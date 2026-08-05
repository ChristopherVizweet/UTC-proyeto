<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()
            ->orderBy('nombre_materia')
            ->paginate(10);

        return view('subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('subjects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_materia' => [
                'required',
                'string',
                'max:100',
                'unique:subjects,nombre_materia',
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                'unique:subjects,code',
            ],
            'descripcion_materia' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (! empty($validated['code'])) {
            $validated['code'] =
                mb_strtoupper(trim($validated['code']));
        }

        $validated['is_active'] =
            $request->boolean('is_active');

        Subject::create($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Materia creada correctamente.');
    }

    public function show(Subject $subject): View
    {
        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject): View
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(
        Request $request,
        Subject $subject
    ): RedirectResponse {
        $validated = $request->validate([
            'nombre_materia' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'subjects',
                    'nombre_materia'
                )->ignore($subject->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(
                    'subjects',
                    'code'
                )->ignore($subject->id),
            ],
            'descripcion_materia' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (! empty($validated['code'])) {
            $validated['code'] =
                mb_strtoupper(trim($validated['code']));
        }

        $validated['is_active'] =
            $request->boolean('is_active');

        $subject->update($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Materia eliminada correctamente.');
    }
}