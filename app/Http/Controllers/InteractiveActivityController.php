<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Services\AutomaticSubmissionGrader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InteractiveActivityController extends Controller
{
    public function __construct(private readonly AutomaticSubmissionGrader $submissionGrader) {}

    public function play(Request $request, Activity $activity): View
    {
        $configuration = $this->authorizePlayable($request, $activity);
        $attempts = $activity->attempts()
            ->where('student_id', $request->user()->id)
            ->latest('attempt_number')
            ->get();
        $maxAttempts = (int) $configuration['max_attempts'];
        $remainingAttempts = max(0, $maxAttempts - $attempts->count());
        $bestScore = $attempts->where('status', 'completado')->max('score');
        $showResults = (bool) $configuration['show_result_immediately'];

        return view('interactive-activities.play', compact(
            'activity',
            'attempts',
            'maxAttempts',
            'remainingAttempts',
            'bestScore',
            'showResults'
        ));
    }

    public function startAttempt(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizePlayable($request, $activity);

        $attempt = DB::transaction(function () use ($request, $activity): ActivityAttempt {
            $lockedActivity = Activity::query()->whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $configuration = $this->authorizePlayable($request, $lockedActivity);
            $attempts = ActivityAttempt::query()
                ->where('activity_id', $lockedActivity->id)
                ->where('student_id', $request->user()->id)
                ->lockForUpdate()
                ->get();

            $openAttempt = $attempts->firstWhere('status', 'iniciado');

            if ($openAttempt) {
                return $openAttempt;
            }

            if ($attempts->count() >= (int) $configuration['max_attempts']) {
                throw ValidationException::withMessages([
                    'attempt' => 'Ya utilizaste todos los intentos disponibles.',
                ]);
            }

            $attemptNumber = ((int) $attempts->max('attempt_number')) + 1;

            return ActivityAttempt::create([
                'activity_id' => $lockedActivity->id,
                'student_id' => $request->user()->id,
                'attempt_number' => $attemptNumber,
                'answers' => $this->attemptData($configuration),
                'max_score' => $lockedActivity->puntaje_maximo,
                'started_at' => now(),
                'status' => 'iniciado',
            ]);
        });

        return redirect()->route('activities.attempts.result', $attempt);
    }

    public function submitAttempt(Request $request, ActivityAttempt $attempt): RedirectResponse
    {
        Gate::authorize('submit', $attempt);

        DB::transaction(function () use ($request, $attempt): void {
            $lockedAttempt = ActivityAttempt::query()->whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('submit', $lockedAttempt);
            $activity = $lockedAttempt->activity()->with('content')->firstOrFail();
            $configuration = $this->authorizePlayable($request, $activity);
            $attemptData = $lockedAttempt->answers;
            $leftIds = collect($attemptData['display']['left'])->pluck('id')->sort()->values()->all();
            $rightIds = collect($attemptData['display']['right'])->pluck('id')->all();

            $validated = $request->validate([
                'answers' => ['required', 'array', 'size:'.count($leftIds)],
                'answers.*' => ['required', 'string', 'distinct', Rule::in($rightIds)],
            ]);

            $submittedLeftIds = collect(array_keys($validated['answers']))->sort()->values()->all();

            if ($submittedLeftIds !== $leftIds) {
                throw ValidationException::withMessages([
                    'answers' => 'Debes relacionar todos los conceptos de este intento.',
                ]);
            }

            $correctCount = collect($validated['answers'])
                ->filter(fn (string $rightId, string $leftId) => ($attemptData['solution'][$leftId] ?? null) === $rightId)
                ->count();
            $total = count($attemptData['solution']);
            $score = round(($correctCount / $total) * (float) $lockedAttempt->max_score, 2);
            $completedAt = now();

            $lockedAttempt->update([
                'answers' => [
                    ...$attemptData,
                    'responses' => $validated['answers'],
                    'correct_count' => $correctCount,
                ],
                'score' => $score,
                'completed_at' => $completedAt,
                'time_seconds' => (int) $lockedAttempt->started_at->diffInSeconds($completedAt),
                'status' => 'completado',
            ]);

            $this->submissionGrader->updateBest(
                $activity,
                $request->user()->id,
                $score,
                "Calificación automática: {$correctCount} de {$total} relaciones correctas."
            );
        });

        return redirect()->route('activities.attempts.result', $attempt)
            ->with('success', 'Intento enviado correctamente.');
    }

    public function result(Request $request, ActivityAttempt $attempt): View
    {
        Gate::authorize('view', $attempt);
        $attempt->load(['activity.content', 'activity.teachingAssignment.subject', 'student']);

        if ($attempt->status === 'iniciado') {
            $display = $attempt->answers['display'];

            return view('interactive-activities.attempt', compact('attempt', 'display'));
        }

        $showResult = (bool) ($attempt->answers['settings']['show_result_immediately'] ?? false)
            || $request->user()->hasAnyRole(['docente', 'administrador']);
        $bestScore = $attempt->activity->attempts()
            ->where('student_id', $attempt->student_id)
            ->where('status', 'completado')
            ->max('score');
        $correctCount = $showResult ? ($attempt->answers['correct_count'] ?? null) : null;
        $totalPairs = $showResult ? count($attempt->answers['solution']) : null;

        return view('interactive-activities.result', compact(
            'attempt',
            'showResult',
            'bestScore',
            'correctCount',
            'totalPairs'
        ));
    }

    public function attempts(Activity $activity): View
    {
        Gate::authorize('update', $activity);
        abort_unless($activity->tipo === 'relacion_columnas', 404);
        $activity->load(['teachingAssignment.subject', 'attempts.student']);

        $results = $activity->attempts
            ->groupBy('student_id')
            ->map(function ($attempts) {
                $completed = $attempts->where('status', 'completado');
                $latest = $attempts->sortByDesc('attempt_number')->first();

                return [
                    'student' => $latest->student,
                    'attempts_count' => $attempts->count(),
                    'best_score' => $completed->max('score'),
                    'latest' => $latest,
                ];
            })
            ->values();

        return view('interactive-activities.results', compact('activity', 'results'));
    }

    private function authorizePlayable(Request $request, Activity $activity): array
    {
        Gate::authorize('submit', $activity);
        abort_unless($activity->tipo === 'relacion_columnas', 404);
        $activity->loadMissing('content');
        abort_unless($activity->content, 404);

        if ($activity->fecha_limite?->isPast() && ! $activity->permite_entrega_tardia) {
            throw ValidationException::withMessages([
                'attempt' => 'La actividad terminó y no permite intentos tardíos.',
            ]);
        }

        return $activity->content->configuracion;
    }

    private function attemptData(array $configuration): array
    {
        $left = [];
        $right = [];
        $solution = [];

        foreach ($configuration['pairs'] as $pair) {
            $leftId = (string) Str::uuid();
            $rightId = (string) Str::uuid();
            $left[] = ['id' => $leftId, 'label' => $pair['left']];
            $right[] = ['id' => $rightId, 'label' => $pair['right']];
            $solution[$leftId] = $rightId;
        }

        if ($configuration['shuffle_right_column']) {
            shuffle($right);
        }

        return [
            'display' => ['left' => $left, 'right' => $right],
            'solution' => $solution,
            'responses' => [],
            'settings' => [
                'show_result_immediately' => (bool) $configuration['show_result_immediately'],
            ],
        ];
    }
}
