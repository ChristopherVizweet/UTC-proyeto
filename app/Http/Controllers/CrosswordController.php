<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Services\AutomaticSubmissionGrader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CrosswordController extends Controller
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

        return view('crosswords.play', compact('activity', 'attempts', 'maxAttempts', 'remainingAttempts', 'bestScore', 'showResults'));
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

        return redirect()->route('activities.crossword.attempts.result', $attempt);
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
            $cellKeys = array_keys($attemptData['solution']);
            $validated = $request->validate([
                'cells' => ['required', 'array', 'size:'.count($cellKeys)],
                'cells.*' => ['required', 'string', 'size:1', 'regex:/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9]$/u'],
            ]);

            if (collect(array_keys($validated['cells']))->sort()->values()->all() !== collect($cellKeys)->sort()->values()->all()) {
                throw ValidationException::withMessages(['cells' => 'Completa todas las casillas del crucigrama.']);
            }

            $responses = collect($validated['cells'])->map(fn (string $value): string => $this->normalizeLetter($value))->all();
            $correctCount = collect($attemptData['entries'])->filter(function (array $entry) use ($responses): bool {
                foreach ($entry['cells'] as $cell) {
                    if (($responses[$cell] ?? null) !== $entry['answer_by_cell'][$cell]) {
                        return false;
                    }
                }

                return true;
            })->count();
            $total = count($attemptData['entries']);
            $score = round(($correctCount / $total) * (float) $lockedAttempt->max_score, 2);
            $completedAt = now();

            $lockedAttempt->update([
                'answers' => [...$attemptData, 'responses' => $responses, 'correct_count' => $correctCount],
                'score' => $score,
                'completed_at' => $completedAt,
                'time_seconds' => (int) $lockedAttempt->started_at->diffInSeconds($completedAt),
                'status' => 'completado',
            ]);
            $this->submissionGrader->updateBest(
                $activity,
                $request->user()->id,
                $score,
                "Calificación automática: {$correctCount} de {$total} palabras correctas."
            );
        });

        return redirect()->route('activities.crossword.attempts.result', $attempt)->with('success', 'Intento enviado correctamente.');
    }

    public function result(Request $request, ActivityAttempt $attempt): View
    {
        Gate::authorize('view', $attempt);
        $attempt->load(['activity.content', 'activity.teachingAssignment.subject', 'student']);
        abort_unless($attempt->activity->tipo === 'crucigrama', 404);

        if ($attempt->status === 'iniciado') {
            return view('crosswords.attempt', compact('attempt'));
        }

        $showResult = (bool) ($attempt->answers['settings']['show_result_immediately'] ?? false)
            || $request->user()->hasAnyRole(['docente', 'administrador']);
        $bestScore = $attempt->activity->attempts()->where('student_id', $attempt->student_id)
            ->where('status', 'completado')->max('score');
        $correctCount = $showResult ? ($attempt->answers['correct_count'] ?? 0) : null;
        $totalWords = $showResult ? count($attempt->answers['entries']) : null;

        return view('crosswords.result', compact('attempt', 'showResult', 'bestScore', 'correctCount', 'totalWords'));
    }

    public function attempts(Activity $activity): View
    {
        Gate::authorize('update', $activity);
        abort_unless($activity->tipo === 'crucigrama', 404);
        $activity->load(['teachingAssignment.subject', 'attempts.student']);
        $results = $activity->attempts->groupBy('student_id')->map(function ($attempts): array {
            $latest = $attempts->sortByDesc('attempt_number')->first();

            return [
                'student' => $latest->student,
                'attempts_count' => $attempts->count(),
                'best_score' => $attempts->where('status', 'completado')->max('score'),
                'latest' => $latest,
            ];
        })->values();

        return view('crosswords.results', compact('activity', 'results'));
    }

    private function authorizePlayable(Request $request, Activity $activity): array
    {
        Gate::authorize('submit', $activity);
        abort_unless($activity->tipo === 'crucigrama', 404);
        $activity->loadMissing('content');
        abort_unless($activity->content, 404);

        if ($activity->fecha_limite?->isPast() && ! $activity->permite_entrega_tardia) {
            throw ValidationException::withMessages(['attempt' => 'La actividad terminó y no permite intentos tardíos.']);
        }

        return $activity->content->configuracion;
    }

    private function attemptData(array $configuration): array
    {
        $solution = $configuration['grid'];
        $numbers = [];
        $entries = collect($configuration['entries'])->map(function (array $entry) use (&$numbers): array {
            $cells = [];
            $answerByCell = [];

            foreach (mb_str_split($entry['answer']) as $index => $letter) {
                $row = $entry['row'] + ($entry['direction'] === 'vertical' ? $index : 0);
                $column = $entry['column'] + ($entry['direction'] === 'horizontal' ? $index : 0);
                $key = $row.':'.$column;
                $cells[] = $key;
                $answerByCell[$key] = $letter;
            }

            $numbers[$entry['row'].':'.$entry['column']] = $entry['number'];

            return [
                'number' => $entry['number'],
                'clue' => $entry['clue'],
                'direction' => $entry['direction'],
                'cells' => $cells,
                'answer_by_cell' => $answerByCell,
            ];
        })->all();

        return [
            'rows' => $configuration['rows'],
            'columns' => $configuration['columns'],
            'cell_keys' => array_keys($solution),
            'numbers' => $numbers,
            'entries' => $entries,
            'solution' => $solution,
            'responses' => [],
            'settings' => ['show_result_immediately' => (bool) $configuration['show_result_immediately']],
        ];
    }

    private function normalizeLetter(string $letter): string
    {
        return strtr(mb_strtoupper($letter), ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U']);
    }
}
