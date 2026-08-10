<?php

namespace App\Http\Controllers;

use App\Models\SchoolGroup;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeachingAssignmentController extends Controller
{
    public function index(): View
    {
        $teachingAssignments = TeachingAssignment::query()
            ->with([
                'teacher',
                'schoolGroup.academicPeriod',
                'schoolGroup.schoolGrade',
                'subject',
            ])
            ->latest()
            ->paginate(10);

        return view('teaching-assignments.index', compact('teachingAssignments'));
    }

    public function create(): View
    {
        return view('teaching-assignments.create', $this->formOptions(true));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));

        $this->validateTeacher((int) $validated['teacher_id']);

        DB::transaction(fn () => TeachingAssignment::create($validated));

        return redirect()
            ->route('teaching-assignments.index')
            ->with('success', 'Asignación docente creada correctamente.');
    }

    public function show(TeachingAssignment $teachingAssignment): View
    {
        $teachingAssignment->load([
            'teacher.teacherProfile',
            'schoolGroup.academicPeriod',
            'schoolGroup.schoolGrade',
            'subject',
        ]);

        return view('teaching-assignments.show', compact('teachingAssignment'));
    }

    public function edit(TeachingAssignment $teachingAssignment): View
    {
        return view('teaching-assignments.edit', [
            'teachingAssignment' => $teachingAssignment,
            ...$this->formOptions(false),
        ]);
    }

    public function update(
        Request $request,
        TeachingAssignment $teachingAssignment
    ): RedirectResponse {
        $validated = $request->validate(
            $this->rules($request, $teachingAssignment)
        );

        $this->validateTeacher((int) $validated['teacher_id']);

        DB::transaction(
            fn () => $teachingAssignment->update($validated)
        );

        return redirect()
            ->route('teaching-assignments.index')
            ->with('success', 'Asignación docente actualizada correctamente.');
    }

    public function destroy(
        TeachingAssignment $teachingAssignment
    ): RedirectResponse {
        if ($teachingAssignment->activities()->exists()) {
            return redirect()
                ->route('teaching-assignments.index')
                ->with('error', 'No se puede eliminar la asignación porque tiene actividades registradas.');
        }

        DB::transaction(fn () => $teachingAssignment->delete());

        return redirect()
            ->route('teaching-assignments.index')
            ->with('success', 'Asignación docente eliminada correctamente.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(
        Request $request,
        ?TeachingAssignment $teachingAssignment = null
    ): array {
        return [
            'teacher_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'school_group_id' => [
                'required',
                'integer',
                Rule::exists('school_groups', 'id')->when(
                    $teachingAssignment === null,
                    fn (Exists $rule) => $rule->where('is_active', true)
                ),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->when(
                    $teachingAssignment === null,
                    fn (Exists $rule) => $rule->where('is_active', true)
                ),
                Rule::unique('teaching_assignments', 'subject_id')
                    ->where(
                        fn ($query) => $query->where(
                            'school_group_id',
                            $request->integer('school_group_id')
                        )
                    )
                    ->ignore($teachingAssignment?->id),
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    private function validateTeacher(int $teacherId): void
    {
        if (! User::role('docente')->whereKey($teacherId)->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => 'El usuario seleccionado no tiene el rol de docente.',
            ]);
        }
    }

    /**
     * @return array{teachers: Collection, schoolGroups: Collection, subjects: Collection}
     */
    private function formOptions(bool $onlyActive): array
    {
        $teachers = User::role('docente')
            ->orderBy('name')
            ->get();

        $schoolGroups = SchoolGroup::query()
            ->with(['academicPeriod', 'schoolGrade'])
            ->when($onlyActive, fn ($query) => $query->where('is_active', true))
            ->orderBy('academic_period_id')
            ->orderBy('school_grade_id')
            ->orderBy('nombre_grupo')
            ->get();

        $subjects = Subject::query()
            ->when($onlyActive, fn ($query) => $query->where('is_active', true))
            ->orderBy('nombre_materia')
            ->get();

        return compact('teachers', 'schoolGroups', 'subjects');
    }
}
