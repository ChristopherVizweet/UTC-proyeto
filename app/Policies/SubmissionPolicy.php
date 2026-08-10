<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrador', 'docente', 'estudiante']);
    }

    public function view(User $user, Submission $submission): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('estudiante') && $submission->student_id === $user->id)
            || ($user->hasRole('docente') && $this->ownsAssignment($user, $submission));
    }

    public function create(User $user): bool
    {
        return $user->hasRole('estudiante');
    }

    public function update(User $user, Submission $submission): bool
    {
        return $user->hasRole('estudiante')
            && $submission->student_id === $user->id
            && $submission->estado !== 'revisada'
            && $submission->activity()->where('tipo', '!=', 'relacion_columnas')->exists();
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $this->update($user, $submission);
    }

    public function submit(User $user, Submission $submission): bool
    {
        return $this->update($user, $submission);
    }

    public function grade(User $user, Submission $submission): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('docente') && $this->ownsAssignment($user, $submission));
    }

    private function ownsAssignment(User $user, Submission $submission): bool
    {
        return $submission->activity()
            ->whereHas(
                'teachingAssignment',
                fn ($query) => $query->where('teacher_id', $user->id)
            )
            ->exists();
    }
}
