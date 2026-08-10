<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SchoolGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        $enrollments = Enrollment::query()
            ->with([
                'student',
                'schoolGroup.academicPeriod',
                'schoolGroup.schoolGrade',
            ])
            ->orderByDesc('fecha_inscripcion')
            ->paginate(10);

        return view('enrollments.index', compact('enrollments'));
    }

    public function create(): View
    {
        $students = User::role('estudiante')
            ->orderBy('name')
            ->get();

        $schoolGroups = SchoolGroup::query()
            ->with([
                'academicPeriod',
                'schoolGrade',
            ])
            ->where('is_active', true)
            ->orderBy('academic_period_id')
            ->orderBy('school_grade_id')
            ->orderBy('nombre_grupo')
            ->get();

        return view('enrollments.create', compact(
            'students',
            'schoolGroups'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($request)
        );

        $this->validateStudent(
            (int) $validated['student_id']
        );

        $this->validateActiveEnrollment(
            (int) $validated['student_id'],
            (int) $validated['school_group_id'],
            $validated['estado_inscripcion']
        );

        $this->validateGroupCapacity(
            (int) $validated['school_group_id'],
            $validated['estado_inscripcion']
        );

        Enrollment::create($validated);

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Estudiante inscrito correctamente.');
    }

    public function show(Enrollment $enrollment): View
    {
        $enrollment->load([
            'student.address',
            'student.studentProfile',
            'schoolGroup.academicPeriod',
            'schoolGroup.schoolGrade',
        ]);

        return view('enrollments.show', compact('enrollment'));
    }

    public function edit(Enrollment $enrollment): View
    {
        $students = User::role('estudiante')
            ->orderBy('name')
            ->get();

        $schoolGroups = SchoolGroup::query()
            ->with([
                'academicPeriod',
                'schoolGrade',
            ])
            ->orderBy('academic_period_id')
            ->orderBy('school_grade_id')
            ->orderBy('nombre_grupo')
            ->get();

        return view('enrollments.edit', compact(
            'enrollment',
            'students',
            'schoolGroups'
        ));
    }

    public function update(
        Request $request,
        Enrollment $enrollment
    ): RedirectResponse {
        $validated = $request->validate(
            $this->rules($request, $enrollment)
        );

        $this->validateStudent(
            (int) $validated['student_id']
        );

        $this->validateActiveEnrollment(
            (int) $validated['student_id'],
            (int) $validated['school_group_id'],
            $validated['estado_inscripcion'],
            $enrollment
        );

        $this->validateGroupCapacity(
            (int) $validated['school_group_id'],
            $validated['estado_inscripcion'],
            $enrollment
        );

        $enrollment->update($validated);

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Inscripción actualizada correctamente.');
    }

    public function destroy(
        Enrollment $enrollment
    ): RedirectResponse {
        $enrollment->delete();

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Inscripción eliminada correctamente.');
    }

    private function rules(
        Request $request,
        ?Enrollment $enrollment = null
    ): array {
        return [
            'student_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'school_group_id' => [
                'required',
                'integer',
                'exists:school_groups,id',
                Rule::unique(
                    'enrollments',
                    'school_group_id'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'student_id',
                            $request->student_id
                        )
                    )
                    ->ignore($enrollment?->id),
            ],
            'fecha_inscripcion' => [
                'required',
                'date',
            ],
            'estado_inscripcion' => [
                'required',
                Rule::in([
                    'activo',
                    'baja',
                    'completado',
                ]),
            ],
        ];
    }

    private function validateStudent(int $studentId): void
    {
        $student = User::findOrFail($studentId);

        if (! $student->hasRole('estudiante')) {
            throw ValidationException::withMessages([
                'student_id' =>
                    'El usuario seleccionado no tiene el rol de estudiante.',
            ]);
        }
    }

    private function validateActiveEnrollment(
        int $studentId,
        int $schoolGroupId,
        string $status,
        ?Enrollment $currentEnrollment = null
    ): void {
        if ($status !== 'activo') {
            return;
        }

        $schoolGroup = SchoolGroup::findOrFail(
            $schoolGroupId
        );

        $exists = Enrollment::query()
            ->where('student_id', $studentId)
            ->where('estado_inscripcion', 'activo')
            ->whereHas(
                'schoolGroup',
                fn ($query) => $query->where(
                    'academic_period_id',
                    $schoolGroup->academic_period_id
                )
            )
            ->when(
                $currentEnrollment,
                fn ($query) => $query->whereKeyNot(
                    $currentEnrollment->id
                )
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'school_group_id' =>
                    'El estudiante ya tiene una inscripción activa en este periodo académico.',
            ]);
        }
    }

    private function validateGroupCapacity(
        int $schoolGroupId,
        string $status,
        ?Enrollment $currentEnrollment = null
    ): void {
        if ($status !== 'activo') {
            return;
        }

        $schoolGroup = SchoolGroup::findOrFail(
            $schoolGroupId
        );

        if (! $schoolGroup->capacidad_grupo) {
            return;
        }

        $enrolled = Enrollment::query()
            ->where('school_group_id', $schoolGroupId)
            ->where('estado_inscripcion', 'activo')
            ->when(
                $currentEnrollment,
                fn ($query) => $query->whereKeyNot(
                    $currentEnrollment->id
                )
            )
            ->count();

        if ($enrolled >= $schoolGroup->capacidad_grupo) {
            throw ValidationException::withMessages([
                'school_group_id' =>
                    'El grupo seleccionado ya alcanzó su capacidad máxima.',
            ]);
        }
    }
}