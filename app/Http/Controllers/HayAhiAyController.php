<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Services\AutomaticSubmissionGrader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HayAhiAyController extends Controller
{
    public function __construct(private readonly AutomaticSubmissionGrader $submissionGrader) {}

    public function play(Request $request, Activity $activity): View
    {
        $configuration = $this->authorizePlayable($request, $activity);
        $attempts = $activity->attempts()->where('student_id', $request->user()->id)->latest('attempt_number')->get();
        $maxAttempts = (int) $configuration['max_attempts'];
        $remainingAttempts = max(0, $maxAttempts - $attempts->count());
        $bestScore = $attempts->where('status', 'completado')->max('score');
        $showResults = (bool) $configuration['show_result_immediately'];

        return view('hay-ahi-ay.play', compact('activity', 'attempts', 'maxAttempts', 'remainingAttempts', 'bestScore', 'showResults'));
    }

    public function startAttempt(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizePlayable($request, $activity);
        $attempt = DB::transaction(function () use ($request, $activity): ActivityAttempt {
            $lockedActivity = Activity::query()->whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $configuration = $this->authorizePlayable($request, $lockedActivity);
            $attempts = ActivityAttempt::query()->where('activity_id', $lockedActivity->id)
                ->where('student_id', $request->user()->id)->lockForUpdate()->get();

            if ($openAttempt = $attempts->firstWhere('status', 'iniciado')) {
                return $openAttempt;
            }

            if ($attempts->count() >= (int) $configuration['max_attempts']) {
                throw ValidationException::withMessages(['attempt' => 'Ya utilizaste todos los intentos disponibles.']);
            }

            return ActivityAttempt::create([
                'activity_id' => $lockedActivity->id,
                'student_id' => $request->user()->id,
                'attempt_number' => ((int) $attempts->max('attempt_number')) + 1,
                'answers' => $this->attemptData($configuration),
                'max_score' => $lockedActivity->puntaje_maximo,
                'started_at' => now(),
                'status' => 'iniciado',
            ]);
        });

        return redirect()->route('activities.hay-ahi-ay.attempts.result', $attempt);
    }

    public function submitAttempt(Request $request, ActivityAttempt $attempt): RedirectResponse
    {
        Gate::authorize('submit', $attempt);
        DB::transaction(function () use ($request, $attempt): void {
            $lockedAttempt = ActivityAttempt::query()->whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('submit', $lockedAttempt);
            $activity = $lockedAttempt->activity()->with('content')->firstOrFail();
            $this->authorizePlayable($request, $activity);
            $attemptData = $lockedAttempt->answers;
            $itemIds = collect($attemptData['display'])->pluck('id')->sort()->values()->all();
            $validated = $request->validate([
                'answers' => ['required', 'array', 'size:'.count($itemIds)],
                'answers.*' => ['required', Rule::in(['hay', 'ahí', 'ay'])],
            ]);

            if (collect(array_keys($validated['answers']))->sort()->values()->all() !== $itemIds) {
                throw ValidationException::withMessages(['answers' => 'Responde todas las oraciones.']);
            }

            $correctCount = collect($validated['answers'])
                ->filter(fn (string $answer, string $id): bool => ($attemptData['solution'][$id] ?? null) === $answer)
                ->count();
            $total = count($attemptData['solution']);
            $score = round(($correctCount / $total) * (float) $lockedAttempt->max_score, 2);
            $completedAt = now();
            $lockedAttempt->update([
                'answers' => [...$attemptData, 'responses' => $validated['answers'], 'correct_count' => $correctCount],
                'score' => $score,
                'completed_at' => $completedAt,
                'time_seconds' => (int) $lockedAttempt->started_at->diffInSeconds($completedAt),
                'status' => 'completado',
            ]);
            $this->submissionGrader->updateBest(
                $activity,
                $request->user()->id,
                $score,
                "Calificación automática: {$correctCount} de {$total} oraciones correctas."
            );
        });

        return redirect()->route('activities.hay-ahi-ay.attempts.result', $attempt)->with('success', 'Intento enviado correctamente.');
    }

    public function result(Request $request, ActivityAttempt $attempt): View
    {
        Gate::authorize('view', $attempt);
        $attempt->load(['activity.content', 'activity.teachingAssignment.subject', 'student']);
        abort_unless($attempt->activity->tipo === 'hay_ahi_ay', 404);

        if ($attempt->status === 'iniciado') {
            return view('hay-ahi-ay.attempt', ['attempt' => $attempt, 'display' => $attempt->answers['display']]);
        }

        $showResult = (bool) ($attempt->answers['settings']['show_result_immediately'] ?? false)
            || $request->user()->hasAnyRole(['docente', 'administrador']);
        $bestScore = $attempt->activity->attempts()->where('student_id', $attempt->student_id)
            ->where('status', 'completado')->max('score');
        $correctCount = $showResult ? ($attempt->answers['correct_count'] ?? 0) : null;
        $totalItems = $showResult ? count($attempt->answers['solution']) : null;

        return view('hay-ahi-ay.result', compact('attempt', 'showResult', 'bestScore', 'correctCount', 'totalItems'));
    }

    public function attempts(Activity $activity): View
    {
        Gate::authorize('update', $activity);
        abort_unless($activity->tipo === 'hay_ahi_ay', 404);
        $activity->load(['teachingAssignment.subject', 'attempts.student']);
        $results = $activity->attempts->groupBy('student_id')->map(function ($attempts): array {
            $latest = $attempts->sortByDesc('attempt_number')->first();

            return ['student' => $latest->student, 'attempts_count' => $attempts->count(), 'best_score' => $attempts->where('status', 'completado')->max('score'), 'latest' => $latest];
        })->values();

        return view('hay-ahi-ay.results', compact('activity', 'results'));
    }

    private function authorizePlayable(Request $request, Activity $activity): array
    {
        Gate::authorize('submit', $activity);
        abort_unless($activity->tipo === 'hay_ahi_ay', 404);
        $activity->loadMissing('content');
        abort_unless($activity->content, 404);

        if ($activity->fecha_limite?->isPast() && ! $activity->permite_entrega_tardia) {
            throw ValidationException::withMessages(['attempt' => 'La actividad terminó y no permite intentos tardíos.']);
        }

        return $activity->content->configuracion;
    }

    private function attemptData(array $configuration): array
    {
        $options = ['hay', 'ahí', 'ay'];
        shuffle($options);

        return [
            'display' => collect($configuration['items'])->map(fn (array $item): array => ['id' => $item['id'], 'sentence' => $item['sentence']])->shuffle()->values()->all(),
            'options' => $options,
            'solution' => collect($configuration['items'])->mapWithKeys(fn (array $item): array => [$item['id'] => $item['answer']])->all(),
            'responses' => [],
            'settings' => ['show_result_immediately' => (bool) $configuration['show_result_immediately']],
        ];
    }
}
