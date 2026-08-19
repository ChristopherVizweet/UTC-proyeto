<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Subject;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $pendingActivities = collect();
        $registeredSubjects = collect();

        if ($user->hasRole('estudiante')) {
            $pendingActivities = Activity::query()
                ->with(['teachingAssignment.subject'])
                ->where('estado', 'publicada')
                ->where(fn (Builder $query) => $query
                    ->whereNull('fecha_publicacion')
                    ->orWhere('fecha_publicacion', '<=', now()))
                ->where(fn (Builder $query) => $query
                    ->whereNull('fecha_limite')
                    ->orWhere('fecha_limite', '>=', now())
                    ->orWhere('permite_entrega_tardia', true))
                ->whereHas(
                    'teachingAssignment.schoolGroup.enrollments',
                    fn (Builder $query) => $query
                        ->where('student_id', $user->id)
                        ->where('estado_inscripcion', 'activo')
                )
                ->whereDoesntHave(
                    'submissions',
                    fn (Builder $query) => $query
                        ->where('student_id', $user->id)
                        ->whereIn('estado', array_diff(Submission::STATUSES, ['borrador']))
                )
                ->orderBy('fecha_limite')
                ->limit(3)
                ->get();

            $registeredSubjects = Subject::query()
                ->where('is_active', true)
                ->whereHas(
                    'teachingAssignments',
                    fn (Builder $query) => $query
                        ->where('is_active', true)
                        ->whereHas(
                            'schoolGroup.enrollments',
                            fn (Builder $query) => $query
                                ->where('student_id', $user->id)
                                ->where('estado_inscripcion', 'activo')
                        )
                )
                ->orderBy('nombre_materia')
                ->limit(4)
                ->get();
        }

        if ($user->hasRole('docente')) {
            $registeredSubjects = Subject::query()
                ->where('is_active', true)
                ->whereHas(
                    'teachingAssignments',
                    fn (Builder $query) => $query
                        ->where('teacher_id', $user->id)
                        ->where('is_active', true)
                )
                ->orderBy('nombre_materia')
                ->limit(4)
                ->get();
        }

        return view('dashboard', compact('pendingActivities', 'registeredSubjects'));
    }
}
