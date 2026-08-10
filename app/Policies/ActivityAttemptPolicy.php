<?php

namespace App\Policies;

use App\Models\ActivityAttempt;
use App\Models\User;

class ActivityAttemptPolicy
{
    public function view(User $user, ActivityAttempt $attempt): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('estudiante') && $attempt->student_id === $user->id)
            || ($user->hasRole('docente') && $this->ownsActivity($user, $attempt));
    }

    public function submit(User $user, ActivityAttempt $attempt): bool
    {
        return $user->hasRole('estudiante')
            && $attempt->student_id === $user->id
            && $attempt->status === 'iniciado';
    }

    private function ownsActivity(User $user, ActivityAttempt $attempt): bool
    {
        return $attempt->activity()
            ->whereHas(
                'teachingAssignment',
                fn ($query) => $query->where('teacher_id', $user->id)
            )
            ->exists();
    }
}
