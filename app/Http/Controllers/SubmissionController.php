<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Submission::class);
        $user = $request->user();

        $submissions = Submission::query()
            ->with([
                'student',
                'grader',
                'activity.content',
                'activity.teachingAssignment.subject',
                'activity.teachingAssignment.schoolGroup.schoolGrade',
            ])
            ->when($user->hasRole('estudiante'), fn (Builder $query) => $query
                ->where('student_id', $user->id))
            ->when($user->hasRole('docente'), fn (Builder $query) => $query
                ->whereHas('activity.teachingAssignment', fn (Builder $query) => $query
                    ->where('teacher_id', $user->id)))
            ->when($request->filled('estado'), fn (Builder $query) => $query
                ->where('estado', $request->string('estado')))
            ->latest()
            ->paginate(12);

        return view('submissions.index', compact('submissions'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Submission::class);
        $activity = Activity::findOrFail($request->integer('activity_id'));
        abort_if(in_array($activity->tipo, ['relacion_columnas', 'sopa_letras', 'secuencia'], true), 404);
        Gate::authorize('submit', $activity);
        $this->ensureAcceptingSubmissions($activity, false);

        abort_if(
            Submission::where('activity_id', $activity->id)->where('student_id', $request->user()->id)->exists(),
            409,
            'Ya existe una entrega para esta actividad.'
        );

        return view('submissions.create', ['submission' => null, 'activity' => $activity]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Submission::class);
        $validated = $request->validate($this->submissionRules());
        $activity = Activity::findOrFail($validated['activity_id']);
        abort_if(in_array($activity->tipo, ['relacion_columnas', 'sopa_letras', 'secuencia'], true), 404);
        Gate::authorize('submit', $activity);
        $isSubmit = $validated['action'] === 'submit';
        $this->ensureAcceptingSubmissions($activity, $isSubmit);

        $path = $request->file('archivo')?->store('submissions', 'public');
        $data = [
            'activity_id' => $activity->id,
            'student_id' => $request->user()->id,
            'respuesta' => $validated['respuesta'] ?? null,
            'archivo_path' => $path,
            ...$this->submissionState($activity, $isSubmit),
        ];

        try {
            $submission = DB::transaction(fn () => Submission::create($data));
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        return redirect()->route('submissions.show', $submission)
            ->with('success', $isSubmit ? 'Actividad entregada correctamente.' : 'Borrador guardado correctamente.');
    }

    public function show(Submission $submission): View
    {
        Gate::authorize('view', $submission);
        $submission->load([
            'student',
            'grader',
            'activity.content',
            'activity.creator',
            'activity.teachingAssignment.subject',
            'activity.teachingAssignment.schoolGroup.schoolGrade',
        ]);

        return view('submissions.show', compact('submission'));
    }

    public function edit(Submission $submission): View
    {
        Gate::authorize('update', $submission);
        $this->ensureAcceptingSubmissions($submission->activity, false);

        return view('submissions.edit', ['submission' => $submission, 'activity' => $submission->activity]);
    }

    public function update(Request $request, Submission $submission): RedirectResponse
    {
        Gate::authorize('update', $submission);
        $validated = $request->validate($this->submissionRules(false));
        $activity = $submission->activity;
        $isSubmit = $validated['action'] === 'submit';
        $this->ensureAcceptingSubmissions($activity, $isSubmit);
        $oldPath = $submission->archivo_path;
        $newPath = $request->file('archivo')?->store('submissions', 'public');
        $data = [
            'respuesta' => $validated['respuesta'] ?? null,
            ...$this->submissionState($activity, $isSubmit),
        ];

        if ($newPath) {
            $data['archivo_path'] = $newPath;
        }

        try {
            DB::transaction(fn () => $submission->update($data));
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('submissions.show', $submission)
            ->with('success', $isSubmit ? 'Actividad entregada correctamente.' : 'Borrador actualizado correctamente.');
    }

    public function destroy(Submission $submission): RedirectResponse
    {
        Gate::authorize('delete', $submission);
        $path = $submission->archivo_path;
        DB::transaction(function () use ($submission, $path): void {
            if ($path && ! Storage::disk('public')->delete($path)) {
                throw new RuntimeException('No fue posible eliminar el archivo de la entrega.');
            }

            $submission->delete();
        });

        return redirect()->route('submissions.index')->with('success', 'Entrega eliminada correctamente.');
    }

    public function grade(Submission $submission): View
    {
        Gate::authorize('grade', $submission);
        $submission->load(['student', 'activity.teachingAssignment.subject']);

        return view('submissions.grade', compact('submission'));
    }

    public function updateGrade(Request $request, Submission $submission): RedirectResponse
    {
        Gate::authorize('grade', $submission);
        $maximum = (float) $submission->activity()->value('puntaje_maximo');
        $validated = $request->validate([
            'calificacion' => ['required', 'numeric', 'min:0', 'max:'.$maximum],
            'retroalimentacion' => ['nullable', 'string'],
        ]);

        DB::transaction(fn () => $submission->update([
            ...$validated,
            'graded_by' => $request->user()->id,
            'graded_at' => now(),
            'estado' => 'revisada',
        ]));

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Entrega calificada correctamente.');
    }

    public function download(Submission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);
        abort_unless($submission->archivo_path && Storage::disk('public')->exists($submission->archivo_path), 404);

        return Storage::disk('public')->download(
            $submission->archivo_path,
            basename($submission->archivo_path)
        );
    }

    private function submissionRules(bool $creating = true): array
    {
        return [
            'activity_id' => [Rule::requiredIf($creating), 'integer', 'exists:activities,id', Rule::unique('submissions')->where('student_id', auth()->id())],
            'respuesta' => ['nullable', 'string'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,txt', 'max:10240'],
            'action' => ['required', Rule::in(['draft', 'submit'])],
        ];
    }

    private function ensureAcceptingSubmissions(Activity $activity, bool $submitting): void
    {
        if ($activity->estado !== 'publicada') {
            throw ValidationException::withMessages(['activity_id' => 'Esta actividad no acepta entregas.']);
        }

        if ($submitting && $activity->fecha_limite?->isPast() && ! $activity->permite_entrega_tardia) {
            throw ValidationException::withMessages(['action' => 'La fecha límite terminó y no se permiten entregas tardías.']);
        }
    }

    private function submissionState(Activity $activity, bool $isSubmit): array
    {
        if (! $isSubmit) {
            return ['estado' => 'borrador', 'fecha_entrega' => null];
        }

        return [
            'estado' => $activity->fecha_limite?->isPast() ? 'entregada_tarde' : 'entregada',
            'fecha_entrega' => now(),
        ];
    }
}
