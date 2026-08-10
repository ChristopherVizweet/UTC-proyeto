<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrador', 'docente', 'estudiante']);
    }

    public function view(User $user, Activity $activity): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        if ($user->hasRole('docente')) {
            return $activity->teachingAssignment()->where('teacher_id', $user->id)->exists();
        }

        return $user->hasRole('estudiante')
            && $activity->estado === 'publicada'
            && ($activity->fecha_publicacion === null || $activity->fecha_publicacion->isPast())
            && $user->enrollments()
                ->where('estado_inscripcion', 'activo')
                ->where('school_group_id', $activity->teachingAssignment()->value('school_group_id'))
                ->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrador', 'docente']);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $this->manage($user, $activity);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $this->manage($user, $activity);
    }

    public function submit(User $user, Activity $activity): bool
    {
        return $user->hasRole('estudiante')
            && $activity->estado === 'publicada'
            && $this->view($user, $activity);
    }

    private function manage(User $user, Activity $activity): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('docente')
                && $activity->teachingAssignment()->where('teacher_id', $user->id)->exists());
    }
}
