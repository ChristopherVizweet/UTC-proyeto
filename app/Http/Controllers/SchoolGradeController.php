<?php

namespace App\Http\Controllers;

use App\Models\SchoolGrade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolGradeController extends Controller
{
    public function index(): View
    {
        $schoolGrades = SchoolGrade::query()
            ->withCount('schoolGroups')
            ->orderBy('nivel_grado')
            ->paginate(10);

        return view('school-grades.index', compact('schoolGrades'));
    }

    public function create(): View
    {
        return view('school-grades.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_grado' => [
                'required',
                'string',
                'max:50',
                'unique:school_grades,nombre_grado',
            ],
            'nivel_grado' => [
                'required',
                'integer',
                'between:1,6',
                'unique:school_grades,nivel_grado',
            ],
            'descripcion_grado' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        SchoolGrade::create($validated);

        return redirect()
            ->route('school-grades.index')
            ->with('success', 'Grado escolar creado correctamente.');
    }

    public function show(SchoolGrade $schoolGrade): View
    {
        $schoolGrade->load([
            'schoolGroups.academicPeriod',
        ]);

        return view(
            'school-grades.show',
            compact('schoolGrade')
        );
    }

    public function edit(SchoolGrade $schoolGrade): View
    {
        return view(
            'school-grades.edit',
            compact('schoolGrade')
        );
    }

    public function update(
        Request $request,
        SchoolGrade $schoolGrade
    ): RedirectResponse {
        $validated = $request->validate([
            'nombre_grado' => [
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'school_grades',
                    'nombre_grado'
                )->ignore($schoolGrade->id),
            ],
            'nivel_grado' => [
                'required',
                'integer',
                'between:1,6',
                Rule::unique(
                    'school_grades',
                    'nivel_grado'
                )->ignore($schoolGrade->id),
            ],
            'descripcion_grado' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $schoolGrade->update($validated);

        return redirect()
            ->route('school-grades.index')
            ->with('success', 'Grado escolar actualizado correctamente.');
    }

    public function destroy(
        SchoolGrade $schoolGrade
    ): RedirectResponse {
        if ($schoolGrade->schoolGroups()->exists()) {
            return redirect()
                ->route('school-grades.index')
                ->with(
                    'error',
                    'No se puede eliminar el grado porque tiene grupos registrados.'
                );
        }

        $schoolGrade->delete();

        return redirect()
            ->route('school-grades.index')
            ->with('success', 'Grado escolar eliminado correctamente.');
    }
}