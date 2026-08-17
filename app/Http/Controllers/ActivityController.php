<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\SchoolGroup;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Services\CrosswordGenerator;
use App\Services\WordSearchGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ActivityController extends Controller
{
    public function __construct(
        private readonly WordSearchGenerator $wordSearchGenerator,
        private readonly CrosswordGenerator $crosswordGenerator
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Activity::class);

        $user = $request->user();
        $query = Activity::query()
            ->with([
                'creator',
                'teachingAssignment.teacher',
                'teachingAssignment.subject',
                'teachingAssignment.schoolGroup.academicPeriod',
                'teachingAssignment.schoolGroup.schoolGrade',
            ]);

        if ($user->hasRole('docente')) {
            $query->whereHas(
                'teachingAssignment',
                fn (Builder $query) => $query->where('teacher_id', $user->id)
            );
        }

        if ($user->hasRole('estudiante')) {
            $query
                ->where('estado', 'publicada')
                ->where(fn (Builder $query) => $query
                    ->whereNull('fecha_publicacion')
                    ->orWhere('fecha_publicacion', '<=', now()))
                ->whereHas(
                    'teachingAssignment.schoolGroup.enrollments',
                    fn (Builder $query) => $query
                        ->where('student_id', $user->id)
                        ->where('estado_inscripcion', 'activo')
                )
                ->with(['submissions' => fn ($query) => $query->where('student_id', $user->id)]);
        }

        $activities = $query
            ->when($request->filled('search'), fn (Builder $query) => $query
                ->where('titulo', 'like', '%'.$request->string('search')->trim().'%'))
            ->when($request->filled('school_group_id'), fn (Builder $query) => $query
                ->whereHas('teachingAssignment', fn (Builder $query) => $query
                    ->where('school_group_id', $request->integer('school_group_id'))))
            ->when($request->filled('subject_id'), fn (Builder $query) => $query
                ->whereHas('teachingAssignment', fn (Builder $query) => $query
                    ->where('subject_id', $request->integer('subject_id'))))
            ->when($request->filled('estado'), fn (Builder $query) => $query
                ->where('estado', $request->string('estado')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $schoolGroups = SchoolGroup::query()->orderBy('nombre_grupo')->get();
        $subjects = Subject::query()->orderBy('nombre_materia')->get();

        return view('activities.index', compact('activities', 'schoolGroups', 'subjects'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Activity::class);

        return view('activities.create', [
            'activity' => null,
            'teachingAssignments' => $this->availableAssignments($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Activity::class);
        $this->normalizeBooleans($request);
        $validated = $request->validate($this->rules($request));
        $memoryImagePaths = [];

        if ($validated['tipo'] === 'memorama') {
            foreach (array_keys($validated['memory_items']) as $index) {
                if (! $request->hasFile("memory_items.{$index}.image")) {
                    throw ValidationException::withMessages(["memory_items.{$index}.image" => 'Cada tarjeta necesita una imagen.']);
                }
            }

            foreach ($validated['memory_items'] as $index => &$item) {
                $item['id'] = (string) Str::uuid();
                $item['image_path'] = $request->file("memory_items.{$index}.image")->store('memory-cards', 'public');
                $memoryImagePaths[] = $item['image_path'];
            }
            unset($item);
        }

        $contentConfiguration = $this->contentConfiguration($validated);

        $validated['created_by'] = $request->user()->id;

        if ($validated['estado'] === 'publicada' && empty($validated['fecha_publicacion'])) {
            $validated['fecha_publicacion'] = now();
        }

        $storedPath = $request->file('archivo')?->store('activities', 'public');
        unset(
            $validated['archivo'],
            $validated['pairs'],
            $validated['words'],
            $validated['sequence_values'],
            $validated['sequence_hidden_count'],
            $validated['crossword_items'],
            $validated['hay_ahi_ay_items'],
            $validated['memory_items'],
            $validated['word_search_rows'],
            $validated['word_search_columns'],
            $validated['word_search_directions'],
            $validated['allow_reverse'],
            $validated['shuffle_right_column'],
            $validated['max_attempts'],
            $validated['show_result_immediately']
        );
        $validated['archivo_path'] = $storedPath;

        try {
            $activity = DB::transaction(function () use ($validated, $contentConfiguration): Activity {
                $activity = Activity::create($validated);

                if ($contentConfiguration) {
                    $activity->content()->create(['configuracion' => $contentConfiguration]);
                }

                return $activity;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_filter([$storedPath, ...$memoryImagePaths]));

            throw $exception;
        }

        return redirect()->route('activities.show', $activity)
            ->with('success', 'Actividad creada correctamente.');
    }

    public function show(Request $request, Activity $activity): View
    {
        Gate::authorize('view', $activity);

        $activity->load([
            'creator',
            'teachingAssignment.teacher',
            'teachingAssignment.subject',
            'teachingAssignment.schoolGroup.academicPeriod',
            'teachingAssignment.schoolGroup.schoolGrade',
            'submissions.student',
            'submissions.grader',
            'content',
        ]);

        $submission = $request->user()->hasRole('estudiante')
            ? $activity->submissions->firstWhere('student_id', $request->user()->id)
            : null;

        return view('activities.show', compact('activity', 'submission'));
    }

    public function edit(Request $request, Activity $activity): View
    {
        Gate::authorize('update', $activity);
        $activity->load('content');

        return view('activities.edit', [
            'activity' => $activity,
            'teachingAssignments' => $this->availableAssignments($request, $activity),
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('update', $activity);
        $this->normalizeBooleans($request);
        $validated = $request->validate($this->rules($request));
        $existingConfiguration = $activity->content()->value('configuracion');
        $newMemoryPaths = [];
        $oldMemoryPaths = collect($existingConfiguration['items'] ?? [])->pluck('image_path')->filter()->all();

        if ($validated['tipo'] === 'memorama') {
            $existingItems = collect($existingConfiguration['items'] ?? [])->keyBy('id');

            foreach ($validated['memory_items'] as $index => &$item) {
                $existingItem = $existingItems->get($item['id'] ?? '');
                $item['id'] = $existingItem['id'] ?? (string) Str::uuid();
                $item['image_path'] = $request->hasFile("memory_items.{$index}.image")
                    ? $request->file("memory_items.{$index}.image")->store('memory-cards', 'public')
                    : ($existingItem['image_path'] ?? null);

                if ($request->hasFile("memory_items.{$index}.image")) {
                    $newMemoryPaths[] = $item['image_path'];
                }

                if (! $item['image_path']) {
                    throw ValidationException::withMessages(["memory_items.{$index}.image" => 'Cada tarjeta necesita una imagen.']);
                }
            }
            unset($item);
        }

        $contentConfiguration = $this->contentConfiguration($validated);

        if (
            in_array($activity->tipo, ['relacion_columnas', 'sopa_letras', 'secuencia', 'crucigrama', 'hay_ahi_ay', 'memorama'], true)
            && $activity->attempts()->exists()
            && (
                $validated['tipo'] !== $activity->tipo
                || $this->configurationFingerprint($activity->tipo, $existingConfiguration ?? [])
                    !== $this->configurationFingerprint($validated['tipo'], $contentConfiguration ?? [])
            )
        ) {
            throw ValidationException::withMessages([
                'tipo' => 'No se puede cambiar el tipo ni la configuración interactiva porque ya existen intentos. Sí puedes editar los demás datos.',
            ]);
        }

        if ($activity->attempts()->exists() && $validated['tipo'] === $activity->tipo) {
            $contentConfiguration = $existingConfiguration;
        }

        if ($validated['estado'] === 'publicada' && empty($validated['fecha_publicacion'])) {
            $validated['fecha_publicacion'] = now();
        }

        $oldPath = $activity->archivo_path;
        $newPath = $request->file('archivo')?->store('activities', 'public');
        unset(
            $validated['archivo'],
            $validated['pairs'],
            $validated['words'],
            $validated['sequence_values'],
            $validated['sequence_hidden_count'],
            $validated['crossword_items'],
            $validated['hay_ahi_ay_items'],
            $validated['memory_items'],
            $validated['word_search_rows'],
            $validated['word_search_columns'],
            $validated['word_search_directions'],
            $validated['allow_reverse'],
            $validated['shuffle_right_column'],
            $validated['max_attempts'],
            $validated['show_result_immediately']
        );

        if ($newPath) {
            $validated['archivo_path'] = $newPath;
        }

        try {
            DB::transaction(function () use ($activity, $validated, $contentConfiguration): void {
                $activity->update($validated);

                if ($contentConfiguration) {
                    if ($contentConfiguration !== $activity->content?->configuracion) {
                        $activity->content()->updateOrCreate(
                            [],
                            [
                                'configuracion' => $contentConfiguration,
                                'contenido_version' => ($activity->content?->contenido_version ?? 0) + 1,
                            ]
                        );
                    }
                } else {
                    $activity->content()->delete();
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_filter([$newPath, ...$newMemoryPaths]));

            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        if ($oldMemoryPaths !== []) {
            $keptMemoryPaths = $validated['tipo'] === 'memorama'
                ? collect($contentConfiguration['items'])->pluck('image_path')->all()
                : [];
            Storage::disk('public')->delete(array_values(array_diff($oldMemoryPaths, $keptMemoryPaths)));
        }

        return redirect()->route('activities.show', $activity)
            ->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        Gate::authorize('delete', $activity);
        $activity->load(['submissions', 'content']);
        $paths = $activity->submissions->pluck('archivo_path')->filter()->all();

        if ($activity->tipo === 'memorama') {
            $paths = [...$paths, ...collect($activity->content?->configuracion['items'] ?? [])->pluck('image_path')->filter()->all()];
        }

        if ($activity->archivo_path) {
            $paths[] = $activity->archivo_path;
        }

        DB::transaction(function () use ($activity, $paths): void {
            if ($paths && ! Storage::disk('public')->delete($paths)) {
                throw new RuntimeException('No fue posible eliminar los archivos de la actividad.');
            }

            $activity->delete();
        });

        return redirect()->route('activities.index')
            ->with('success', 'Actividad eliminada correctamente.');
    }

    public function download(Activity $activity): StreamedResponse
    {
        Gate::authorize('view', $activity);
        abort_unless($activity->archivo_path && Storage::disk('public')->exists($activity->archivo_path), 404);

        return Storage::disk('public')->download(
            $activity->archivo_path,
            basename($activity->archivo_path)
        );
    }

    public function previewImage(Activity $activity): StreamedResponse
    {
        Gate::authorize('view', $activity);
        abort_unless($activity->archivo_path && Storage::disk('public')->exists($activity->archivo_path), 404);
        abort_unless(in_array(Str::lower(pathinfo($activity->archivo_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true), 404);

        return Storage::disk('public')->response(
            $activity->archivo_path,
            basename($activity->archivo_path),
            ['Content-Disposition' => 'inline; filename="'.basename($activity->archivo_path).'"']
        );
    }

    private function availableAssignments(Request $request, ?Activity $activity = null)
    {
        return TeachingAssignment::query()
            ->with(['teacher', 'subject', 'schoolGroup.academicPeriod', 'schoolGroup.schoolGrade'])
            ->where(function (Builder $query) use ($activity): void {
                $query->where('is_active', true);

                if ($activity) {
                    $query->orWhere('id', $activity->teaching_assignment_id);
                }
            })
            ->when($request->user()->hasRole('docente'), fn (Builder $query) => $query
                ->where('teacher_id', $request->user()->id))
            ->get();
    }

    private function rules(Request $request): array
    {
        $assignmentRule = Rule::exists('teaching_assignments', 'id')
            ->where(fn ($query) => $query->where('is_active', true));

        if ($request->user()->hasRole('docente')) {
            $assignmentRule->where('teacher_id', $request->user()->id);
        }

        return [
            'teaching_assignment_id' => ['required', 'integer', $assignmentRule],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'instrucciones' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(Activity::TYPES)],
            'pairs' => ['required_if:tipo,relacion_columnas', 'array', 'min:2', 'max:20'],
            'pairs.*.left' => ['required_if:tipo,relacion_columnas', 'string', 'max:255'],
            'pairs.*.right' => ['required_if:tipo,relacion_columnas', 'string', 'max:255'],
            'shuffle_right_column' => ['required_if:tipo,relacion_columnas', 'boolean'],
            'words' => ['required_if:tipo,sopa_letras', 'array', 'min:3', 'max:20'],
            'words.*.text' => ['required_if:tipo,sopa_letras', 'string', 'max:255'],
            'word_search_rows' => ['required_if:tipo,sopa_letras', 'integer', 'between:8,20'],
            'word_search_columns' => ['required_if:tipo,sopa_letras', 'integer', 'between:8,20'],
            'word_search_directions' => ['required_if:tipo,sopa_letras', 'array', 'min:1'],
            'word_search_directions.*' => ['string', 'distinct', Rule::in(['horizontal', 'vertical', 'diagonal_down', 'diagonal_up'])],
            'allow_reverse' => ['required_if:tipo,sopa_letras', 'boolean'],
            'sequence_values' => ['required_if:tipo,secuencia', 'nullable', 'string', 'max:1000'],
            'sequence_hidden_count' => ['required_if:tipo,secuencia', 'nullable', 'integer', 'between:1,29'],
            'crossword_items' => ['required_if:tipo,crucigrama', 'array', 'min:3', 'max:15'],
            'crossword_items.*.answer' => ['required_if:tipo,crucigrama', 'string', 'max:255'],
            'crossword_items.*.clue' => ['required_if:tipo,crucigrama', 'string', 'max:500'],
            'hay_ahi_ay_items' => ['required_if:tipo,hay_ahi_ay', 'array', 'min:3', 'max:30'],
            'hay_ahi_ay_items.*.sentence' => ['required_if:tipo,hay_ahi_ay', 'string', 'max:500', 'regex:/___/'],
            'hay_ahi_ay_items.*.answer' => ['required_if:tipo,hay_ahi_ay', Rule::in(['hay', 'ahí', 'ay'])],
            'memory_items' => ['required_if:tipo,memorama', 'array', 'min:3', 'max:12'],
            'memory_items.*.id' => ['nullable', 'uuid'],
            'memory_items.*.label' => ['required_if:tipo,memorama', 'string', 'max:100'],
            'memory_items.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'max_attempts' => ['required_if:tipo,relacion_columnas,sopa_letras,secuencia,crucigrama,hay_ahi_ay,memorama', 'integer', 'between:1,10'],
            'show_result_immediately' => ['required_if:tipo,relacion_columnas,sopa_letras,secuencia,crucigrama,hay_ahi_ay,memorama', 'boolean'],
            'fecha_publicacion' => ['nullable', 'date'],
            'fecha_limite' => ['nullable', 'date', 'after_or_equal:fecha_publicacion'],
            'puntaje_maximo' => ['required', 'numeric', 'min:0.01'],
            'permite_entrega_tardia' => ['required', 'boolean'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,txt', 'max:10240'],
            'estado' => ['required', Rule::in(Activity::STATUSES)],
        ];
    }

    private function normalizeBooleans(Request $request): void
    {
        $request->merge([
            'permite_entrega_tardia' => $request->boolean('permite_entrega_tardia'),
            'shuffle_right_column' => $request->boolean('shuffle_right_column'),
            'allow_reverse' => $request->boolean('allow_reverse'),
            'show_result_immediately' => $request->boolean('show_result_immediately'),
        ]);
    }

    private function contentConfiguration(array $validated): ?array
    {
        if ($validated['tipo'] === 'memorama') {
            return [
                'items' => collect($validated['memory_items'])->map(fn (array $item): array => Arr::only($item, ['id', 'label', 'image_path']))->all(),
                'max_attempts' => (int) $validated['max_attempts'],
                'show_result_immediately' => (bool) $validated['show_result_immediately'],
            ];
        }

        if ($validated['tipo'] === 'hay_ahi_ay') {
            return [
                'items' => collect($validated['hay_ahi_ay_items'])->map(fn (array $item): array => [
                    'id' => (string) Str::uuid(),
                    'sentence' => Str::squish($item['sentence']),
                    'answer' => $item['answer'],
                ])->all(),
                'max_attempts' => (int) $validated['max_attempts'],
                'show_result_immediately' => (bool) $validated['show_result_immediately'],
            ];
        }

        if ($validated['tipo'] === 'crucigrama') {
            return [
                ...$this->crosswordGenerator->generate($validated['crossword_items']),
                'max_attempts' => (int) $validated['max_attempts'],
                'show_result_immediately' => (bool) $validated['show_result_immediately'],
            ];
        }

        if ($validated['tipo'] === 'secuencia') {
            $values = collect(explode(',', $validated['sequence_values']))
                ->map(fn (string $value): string => trim($value));

            if ($values->contains('') || $values->contains(fn (string $value): bool => ! is_numeric($value))) {
                throw ValidationException::withMessages([
                    'sequence_values' => 'Escribe únicamente números separados por comas.',
                ]);
            }

            if ($values->count() < 4 || $values->count() > 30) {
                throw ValidationException::withMessages([
                    'sequence_values' => 'La secuencia debe contener entre 4 y 30 valores.',
                ]);
            }

            if ((int) $validated['sequence_hidden_count'] >= $values->count() - 1) {
                throw ValidationException::withMessages([
                    'sequence_hidden_count' => 'Además del primero, debe quedar al menos otro valor visible para reconocer el patrón.',
                ]);
            }

            return [
                'values' => $values->values()->all(),
                'hidden_count' => (int) $validated['sequence_hidden_count'],
                'max_attempts' => (int) $validated['max_attempts'],
                'show_result_immediately' => (bool) $validated['show_result_immediately'],
            ];
        }

        if ($validated['tipo'] === 'sopa_letras') {
            return [
                ...$this->wordSearchGenerator->generate(
                    collect($validated['words'])->pluck('text')->all(),
                    (int) $validated['word_search_rows'],
                    (int) $validated['word_search_columns'],
                    $validated['word_search_directions'],
                    (bool) $validated['allow_reverse']
                ),
                'max_attempts' => (int) $validated['max_attempts'],
                'show_result_immediately' => (bool) $validated['show_result_immediately'],
            ];
        }

        if ($validated['tipo'] !== 'relacion_columnas') {
            return null;
        }

        $pairs = collect($validated['pairs'])
            ->map(fn (array $pair) => [
                'id' => (string) Str::uuid(),
                'left' => Str::squish($pair['left']),
                'right' => Str::squish($pair['right']),
            ])
            ->values();

        $duplicates = $pairs
            ->map(fn (array $pair) => Str::lower($pair['left']).'|'.Str::lower($pair['right']))
            ->duplicates();

        if ($duplicates->isNotEmpty()) {
            throw ValidationException::withMessages([
                'pairs' => 'No se permiten relaciones completamente duplicadas.',
            ]);
        }

        return [
            'pairs' => $pairs->all(),
            'shuffle_right_column' => $validated['shuffle_right_column'],
            'max_attempts' => $validated['max_attempts'],
            'show_result_immediately' => $validated['show_result_immediately'],
        ];
    }

    private function configurationFingerprint(string $type, array $configuration): string
    {
        $structural = match ($type) {
            'relacion_columnas' => [
                'pairs' => collect($configuration['pairs'] ?? [])
                    ->map(fn (array $pair): array => Arr::only($pair, ['left', 'right']))
                    ->values()
                    ->all(),
                'shuffle_right_column' => $configuration['shuffle_right_column'] ?? null,
                'max_attempts' => $configuration['max_attempts'] ?? null,
                'show_result_immediately' => $configuration['show_result_immediately'] ?? null,
            ],
            'sopa_letras' => [
                'words' => collect($configuration['words'] ?? [])->pluck('normalized')->all(),
                'rows' => $configuration['rows'] ?? null,
                'columns' => $configuration['columns'] ?? null,
                'directions' => $configuration['directions'] ?? [],
                'allow_reverse' => $configuration['allow_reverse'] ?? null,
                'max_attempts' => $configuration['max_attempts'] ?? null,
                'show_result_immediately' => $configuration['show_result_immediately'] ?? null,
            ],
            'secuencia' => [
                'values' => $configuration['values'] ?? [],
                'hidden_count' => $configuration['hidden_count'] ?? null,
                'max_attempts' => $configuration['max_attempts'] ?? null,
                'show_result_immediately' => $configuration['show_result_immediately'] ?? null,
            ],
            'crucigrama' => [
                'entries' => collect($configuration['entries'] ?? [])->map(fn (array $entry): array => Arr::only($entry, ['answer', 'clue']))->all(),
                'max_attempts' => $configuration['max_attempts'] ?? null,
                'show_result_immediately' => $configuration['show_result_immediately'] ?? null,
            ],
            'hay_ahi_ay' => [
                'items' => collect($configuration['items'] ?? [])->map(fn (array $item): array => Arr::only($item, ['sentence', 'answer']))->all(),
                'max_attempts' => $configuration['max_attempts'] ?? null,
                'show_result_immediately' => $configuration['show_result_immediately'] ?? null,
            ],
            'memorama' => [
                'items' => collect($configuration['items'] ?? [])->map(fn (array $item): array => Arr::only($item, ['id', 'label', 'image_path']))->all(),
                'max_attempts' => $configuration['max_attempts'] ?? null,
                'show_result_immediately' => $configuration['show_result_immediately'] ?? null,
            ],
            default => [],
        };

        return hash('sha256', serialize($structural));
    }
}
