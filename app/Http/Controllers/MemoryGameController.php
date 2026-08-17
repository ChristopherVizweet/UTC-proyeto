<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Services\AutomaticSubmissionGrader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemoryGameController extends Controller
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

        return view('memory-games.play', compact('activity', 'attempts', 'maxAttempts', 'remainingAttempts', 'bestScore', 'showResults'));
    }

    public function startAttempt(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizePlayable($request, $activity);
        $attempt = DB::transaction(function () use ($request, $activity): ActivityAttempt {
            $lockedActivity = Activity::query()->whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $configuration = $this->authorizePlayable($request, $lockedActivity);
            $attempts = ActivityAttempt::query()->where('activity_id', $lockedActivity->id)->where('student_id', $request->user()->id)->lockForUpdate()->get();

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

        return redirect()->route('activities.memory.attempts.result', $attempt);
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
            $pairIds = collect($attemptData['pair_ids'])->sort()->values()->all();
            $validated = $request->validate([
                'matched_pair_ids' => ['required', 'array', 'size:'.count($pairIds)],
                'matched_pair_ids.*' => ['required', 'uuid', 'distinct', Rule::in($pairIds)],
                'moves' => ['required', 'integer', 'min:'.count($pairIds), 'max:10000'],
            ]);

            if (collect($validated['matched_pair_ids'])->sort()->values()->all() !== $pairIds) {
                throw ValidationException::withMessages(['matched_pair_ids' => 'Debes encontrar todas las parejas.']);
            }

            $pairs = count($pairIds);
            $moves = (int) $validated['moves'];
            $score = round(($pairs / $moves) * (float) $lockedAttempt->max_score, 2);
            $completedAt = now();
            $lockedAttempt->update([
                'answers' => [...$attemptData, 'matched_pair_ids' => $pairIds, 'moves' => $moves, 'correct_count' => $pairs],
                'score' => $score,
                'completed_at' => $completedAt,
                'time_seconds' => (int) $lockedAttempt->started_at->diffInSeconds($completedAt),
                'status' => 'completado',
            ]);
            $this->submissionGrader->updateBest($activity, $request->user()->id, $score, "Memorama completado en {$moves} movimientos.");
        });

        return redirect()->route('activities.memory.attempts.result', $attempt)->with('success', 'Memorama completado correctamente.');
    }

    public function result(Request $request, ActivityAttempt $attempt): View
    {
        Gate::authorize('view', $attempt);
        $attempt->load(['activity.content', 'activity.teachingAssignment.subject', 'student']);
        abort_unless($attempt->activity->tipo === 'memorama', 404);

        if ($attempt->status === 'iniciado') {
            return view('memory-games.attempt', compact('attempt'));
        }

        $showResult = (bool) ($attempt->answers['settings']['show_result_immediately'] ?? false) || $request->user()->hasAnyRole(['docente', 'administrador']);
        $bestScore = $attempt->activity->attempts()->where('student_id', $attempt->student_id)->where('status', 'completado')->max('score');
        $moves = $showResult ? ($attempt->answers['moves'] ?? null) : null;

        return view('memory-games.result', compact('attempt', 'showResult', 'bestScore', 'moves'));
    }

    public function attempts(Activity $activity): View
    {
        Gate::authorize('update', $activity);
        abort_unless($activity->tipo === 'memorama', 404);
        $activity->load(['teachingAssignment.subject', 'attempts.student']);
        $results = $activity->attempts->groupBy('student_id')->map(function ($attempts): array {
            $latest = $attempts->sortByDesc('attempt_number')->first();

            return ['student' => $latest->student, 'attempts_count' => $attempts->count(), 'best_score' => $attempts->where('status', 'completado')->max('score'), 'latest' => $latest];
        })->values();

        return view('memory-games.results', compact('activity', 'results'));
    }

    public function image(Request $request, Activity $activity, string $item): StreamedResponse
    {
        Gate::authorize('view', $activity);
        abort_unless($activity->tipo === 'memorama', 404);
        $activity->loadMissing('content');
        $card = collect($activity->content?->configuracion['items'] ?? [])->firstWhere('id', $item);
        abort_unless($card && Storage::disk('public')->exists($card['image_path']), 404);

        return Storage::disk('public')->response($card['image_path'], basename($card['image_path']));
    }

    private function authorizePlayable(Request $request, Activity $activity): array
    {
        Gate::authorize('submit', $activity);
        abort_unless($activity->tipo === 'memorama', 404);
        $activity->loadMissing('content');
        abort_unless($activity->content, 404);

        if ($activity->fecha_limite?->isPast() && ! $activity->permite_entrega_tardia) {
            throw ValidationException::withMessages(['attempt' => 'La actividad terminó y no permite intentos tardíos.']);
        }

        return $activity->content->configuracion;
    }

    private function attemptData(array $configuration): array
    {
        $cards = collect($configuration['items'])->flatMap(fn (array $item): array => [
            ['card_id' => (string) Str::uuid(), 'pair_id' => $item['id'], 'item_id' => $item['id'], 'label' => $item['label']],
            ['card_id' => (string) Str::uuid(), 'pair_id' => $item['id'], 'item_id' => $item['id'], 'label' => $item['label']],
        ])->shuffle()->values()->all();

        return [
            'cards' => $cards,
            'pair_ids' => collect($configuration['items'])->pluck('id')->all(),
            'matched_pair_ids' => [],
            'moves' => 0,
            'settings' => ['show_result_immediately' => (bool) $configuration['show_result_immediately']],
        ];
    }
}
