<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    public function index(): View
    {
        $academicPeriods = AcademicPeriod::query()
            ->withCount('schoolGroups')
            ->orderByDesc('fecha_inicio')
            ->paginate(10);

        return view('academic-periods.index', compact('academicPeriods'));
    }

    public function create(): View
    {
        return view('academic-periods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_periodo' => [
                'required',
                'string',
                'max:30',
                'unique:academic_periods,nombre_periodo',
            ],
            'fecha_inicio' => [
                'required',
                'date',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio',
            ],
            'estado_periodo' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['estado_periodo'] =
            $request->boolean('estado_periodo');

        DB::transaction(function () use ($validated) {
            if ($validated['estado_periodo']) {
                AcademicPeriod::query()->update([
                    'estado_periodo' => false,
                ]);
            }

            AcademicPeriod::create($validated);
        });

        return redirect()
            ->route('academic-periods.index')
            ->with('success', 'Periodo académico creado correctamente.');
    }

    public function show(AcademicPeriod $academicPeriod): View
    {
        $academicPeriod->load([
            'schoolGroups.schoolGrade',
        ]);

        return view(
            'academic-periods.show',
            compact('academicPeriod')
        );
    }

    public function edit(AcademicPeriod $academicPeriod): View
    {
        return view(
            'academic-periods.edit',
            compact('academicPeriod')
        );
    }

    public function update(
        Request $request,
        AcademicPeriod $academicPeriod
    ): RedirectResponse {
        $validated = $request->validate([
            'nombre_periodo' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'academic_periods',
                    'nombre_periodo'
                )->ignore($academicPeriod->id),
            ],
            'fecha_inicio' => [
                'required',
                'date',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio',
            ],
            'estado_periodo' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['estado_periodo'] =
            $request->boolean('estado_periodo');

        DB::transaction(function () use (
            $validated,
            $academicPeriod
        ) {
            if ($validated['estado_periodo']) {
                AcademicPeriod::query()
                    ->whereKeyNot($academicPeriod->id)
                    ->update([
                        'estado_periodo' => false,
                    ]);
            }

            $academicPeriod->update($validated);
        });

        return redirect()
            ->route('academic-periods.index')
            ->with('success', 'Periodo académico actualizado correctamente.');
    }

    public function destroy(
        AcademicPeriod $academicPeriod
    ): RedirectResponse {
        if ($academicPeriod->schoolGroups()->exists()) {
            return redirect()
                ->route('academic-periods.index')
                ->with(
                    'error',
                    'No se puede eliminar el periodo porque tiene grupos registrados.'
                );
        }

        $academicPeriod->delete();

        return redirect()
            ->route('academic-periods.index')
            ->with('success', 'Periodo académico eliminado correctamente.');
    }
}