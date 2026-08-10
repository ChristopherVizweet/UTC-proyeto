<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Submission;

class AutomaticSubmissionGrader
{
    public function updateBest(
        Activity $activity,
        int $studentId,
        float $score,
        string $feedback
    ): void {
        $submission = Submission::query()
            ->where('activity_id', $activity->id)
            ->where('student_id', $studentId)
            ->lockForUpdate()
            ->first();

        if ($submission?->graded_by !== null) {
            return;
        }

        if ($submission && $submission->calificacion !== null && (float) $submission->calificacion >= $score) {
            return;
        }

        $data = [
            'fecha_entrega' => now(),
            'estado' => $activity->fecha_limite?->isPast() ? 'entregada_tarde' : 'entregada',
            'calificacion' => $score,
            'retroalimentacion' => $feedback,
            'graded_by' => null,
            'graded_at' => now(),
        ];

        if ($submission) {
            $submission->update($data);

            return;
        }

        Submission::create([
            'activity_id' => $activity->id,
            'student_id' => $studentId,
            ...$data,
        ]);
    }
}
