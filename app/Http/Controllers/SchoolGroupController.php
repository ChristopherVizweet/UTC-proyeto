<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\SchoolGrade;
use App\Models\SchoolGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolGroupController extends Controller
{
    public function index(): View
    {
        $schoolGroups = SchoolGroup::query()
            ->with([
                'academicPeriod',
                'schoolGrade',
            ])
            ->orderBy('academic_period_id')
            ->orderBy('school_grade_id')
            ->orderBy('nombre_grupo')
            ->paginate(10);

        return view('school-groups.index', compact('schoolGroups'));
    }

    public function create(): View
    {
        $academicPeriods = AcademicPeriod::query()
            ->orderByDesc('fecha_inicio')
            ->get();

        $schoolGrades = SchoolGrade::query()
            ->orderBy('nivel_grado')
            ->get();

        return view('school-groups.create', compact(
            'academicPeriods',
            'schoolGrades'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_period_id' => [
                'required',
                'integer',
                'exists:academic_periods,id',
            ],
            'school_grade_id' => [
                'required',
                'integer',
                'exists:school_grades,id',
            ],
            'nombre_grupo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('school_groups', 'nombre_grupo')
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'academic_period_id',
                                $request->academic_period_id
                            )
                            ->where(
                                'school_grade_id',
                                $request->school_grade_id
                            )
                    ),
            ],
            'capacidad_grupo' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'descripcion_grupo' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['nombre_grupo'] =
            mb_strtoupper(trim($validated['nombre_grupo']));

        $validated['is_active'] =
            $request->boolean('is_active');

        SchoolGroup::create($validated);

        return redirect()
            ->route('school-groups.index')
            ->with('success', 'Grupo escolar creado correctamente.');
    }

    public function show(SchoolGroup $schoolGroup): View
    {
        $schoolGroup->load([
            'academicPeriod',
            'schoolGrade',
        ]);

        return view('school-groups.show', compact('schoolGroup'));
    }

    public function edit(SchoolGroup $schoolGroup): View
    {
        $academicPeriods = AcademicPeriod::query()
            ->orderByDesc('fecha_inicio')
            ->get();

        $schoolGrades = SchoolGrade::query()
            ->orderBy('nivel_grado')
            ->get();

        return view('school-groups.edit', compact(
            'schoolGroup',
            'academicPeriods',
            'schoolGrades'
        ));
    }

    public function update(
        Request $request,
        SchoolGroup $schoolGroup
    ): RedirectResponse {
        $validated = $request->validate([
            'academic_period_id' => [
                'required',
                'integer',
                'exists:academic_periods,id',
            ],
            'school_grade_id' => [
                'required',
                'integer',
                'exists:school_grades,id',
            ],
            'nombre_grupo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('school_groups', 'nombre_grupo')
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'academic_period_id',
                                $request->academic_period_id
                            )
                            ->where(
                                'school_grade_id',
                                $request->school_grade_id
                            )
                    )
                    ->ignore($schoolGroup->id),
            ],
            'capacidad_grupo' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'descripcion_grupo' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['nombre_grupo'] =
            mb_strtoupper(trim($validated['nombre_grupo']));

        $validated['is_active'] =
            $request->boolean('is_active');

        $schoolGroup->update($validated);

        return redirect()
            ->route('school-groups.index')
            ->with('success', 'Grupo escolar actualizado correctamente.');
    }

    public function destroy(
        SchoolGroup $schoolGroup
    ): RedirectResponse {
        $schoolGroup->delete();

        return redirect()
            ->route('school-groups.index')
            ->with('success', 'Grupo escolar eliminado correctamente.');
    }
}