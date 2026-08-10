<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Services\AutomaticSubmissionGrader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WordSearchController extends Controller
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

        return view('word-search.play', compact(
            'activity', 'attempts', 'maxAttempts', 'remainingAttempts', 'bestScore', 'showResults'
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
                'answers' => [
                    'selections' => [],
                    'correct_word_ids' => [],
                    'settings' => [
                        'show_result_immediately' => (bool) $configuration['show_result_immediately'],
                    ],
                ],
                'max_score' => $lockedActivity->puntaje_maximo,
                'started_at' => now(),
                'status' => 'iniciado',
            ]);
        });

        return redirect()->route('activities.word-search.attempts.result', $attempt);
    }

    public function submitAttempt(Request $request, ActivityAttempt $attempt): RedirectResponse
    {
        Gate::authorize('submit', $attempt);

        DB::transaction(function () use ($request, $attempt): void {
            $lockedAttempt = ActivityAttempt::query()->whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('submit', $lockedAttempt);
            $activity = $lockedAttempt->activity()->with('content')->firstOrFail();
            $configuration = $this->authorizePlayable($request, $activity);
            $maxSelections = count($configuration['words']);

            $validated = $request->validate([
                'selections' => ['required', 'array', 'min:1', 'max:'.$maxSelections],
                'selections.*.start_row' => ['required', 'integer', 'between:0,'.((int) $configuration['rows'] - 1)],
                'selections.*.start_column' => ['required', 'integer', 'between:0,'.((int) $configuration['columns'] - 1)],
                'selections.*.end_row' => ['required', 'integer', 'between:0,'.((int) $configuration['rows'] - 1)],
                'selections.*.end_column' => ['required', 'integer', 'between:0,'.((int) $configuration['columns'] - 1)],
            ]);

            $selections = collect($validated['selections'])->map(function (array $selection): array {
                $rowDistance = abs($selection['end_row'] - $selection['start_row']);
                $columnDistance = abs($selection['end_column'] - $selection['start_column']);

                if (! ($rowDistance === 0 || $columnDistance === 0 || $rowDistance === $columnDistance)) {
                    throw ValidationException::withMessages([
                        'selections' => 'Cada selección debe formar una línea horizontal, vertical o diagonal.',
                    ]);
                }

                return array_map('intval', $selection);
            });

            if ($selections->map(fn (array $selection) => implode(':', $selection))->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages(['selections' => 'No envíes la misma selección más de una vez.']);
            }

            $correctWordIds = $selections
                ->map(fn (array $selection) => $this->matchingWordId($selection, $configuration))
                ->filter()
                ->unique()
                ->values();
            $correctCount = $correctWordIds->count();
            $total = count($configuration['words']);
            $score = round(($correctCount / $total) * (float) $lockedAttempt->max_score, 2);
            $completedAt = now();

            $lockedAttempt->update([
                'answers' => [
                    ...$lockedAttempt->answers,
                    'selections' => $selections->all(),
                    'correct_word_ids' => $correctWordIds->all(),
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
                "Calificación automática: {$correctCount} de {$total} palabras encontradas."
            );
        });

        return redirect()->route('activities.word-search.attempts.result', $attempt)
            ->with('success', 'Intento enviado correctamente.');
    }

    public function result(Request $request, ActivityAttempt $attempt): View
    {
        Gate::authorize('view', $attempt);
        $attempt->load(['activity.content', 'activity.teachingAssignment.subject', 'student']);
        abort_unless($attempt->activity->tipo === 'sopa_letras', 404);

        if ($attempt->status === 'iniciado') {
            $publicConfiguration = $this->publicConfiguration($attempt->activity->content->configuracion);

            return view('word-search.attempt', compact('attempt', 'publicConfiguration'));
        }

        $showResult = (bool) ($attempt->answers['settings']['show_result_immediately'] ?? false)
            || $request->user()->hasAnyRole(['docente', 'administrador']);
        $bestScore = $attempt->activity->attempts()
            ->where('student_id', $attempt->student_id)
            ->where('status', 'completado')
            ->max('score');
        $correctCount = $showResult ? ($attempt->answers['correct_count'] ?? 0) : null;
        $totalWords = $showResult ? count($attempt->activity->content->configuracion['words']) : null;

        return view('word-search.result', compact(
            'attempt', 'showResult', 'bestScore', 'correctCount', 'totalWords'
        ));
    }

    public function attempts(Activity $activity): View
    {
        Gate::authorize('update', $activity);
        abort_unless($activity->tipo === 'sopa_letras', 404);
        $activity->load(['teachingAssignment.subject', 'attempts.student']);
        $results = $activity->attempts
            ->groupBy('student_id')
            ->map(function ($attempts): array {
                $completed = $attempts->where('status', 'completado');
                $latest = $attempts->sortByDesc('attempt_number')->first();

                return [
                    'student' => $latest->student,
                    'attempts_count' => $attempts->count(),
                    'best_score' => $completed->max('score'),
                    'latest' => $latest,
                ];
            })->values();

        return view('word-search.results', compact('activity', 'results'));
    }

    private function authorizePlayable(Request $request, Activity $activity): array
    {
        Gate::authorize('submit', $activity);
        abort_unless($activity->tipo === 'sopa_letras', 404);
        $activity->loadMissing('content');
        abort_unless($activity->content, 404);

        if ($activity->fecha_limite?->isPast() && ! $activity->permite_entrega_tardia) {
            throw ValidationException::withMessages(['attempt' => 'La actividad terminó y no permite intentos tardíos.']);
        }

        return $activity->content->configuracion;
    }

    private function publicConfiguration(array $configuration): array
    {
        return [
            'grid' => $configuration['grid'],
            'words' => collect($configuration['words'])
                ->map(fn (array $word): array => Arr::only($word, ['id', 'original']))
                ->values()
                ->all(),
            'rows' => (int) $configuration['rows'],
            'columns' => (int) $configuration['columns'],
            'allow_reverse' => (bool) $configuration['allow_reverse'],
        ];
    }

    private function matchingWordId(array $selection, array $configuration): ?string
    {
        foreach ($configuration['placements'] as $placement) {
            $direct = $selection['start_row'] === $placement['start_row']
                && $selection['start_column'] === $placement['start_column']
                && $selection['end_row'] === $placement['end_row']
                && $selection['end_column'] === $placement['end_column'];
            $reverse = $configuration['allow_reverse']
                && $selection['start_row'] === $placement['end_row']
                && $selection['start_column'] === $placement['end_column']
                && $selection['end_row'] === $placement['start_row']
                && $selection['end_column'] === $placement['start_column'];

            if ($direct || $reverse) {
                return $placement['word_id'];
            }
        }

        return null;
    }
}
