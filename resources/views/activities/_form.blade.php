@php
    $savedConfiguration = $activity?->content?->configuracion ?? [];
    $initialPairs = old('pairs', $savedConfiguration['pairs'] ?? [
        ['left' => '', 'right' => ''],
        ['left' => '', 'right' => ''],
    ]);
    $initialWords = old('words', collect($savedConfiguration['words'] ?? [])->map(fn ($word) => ['text' => $word['original']])->all());
    $initialSequence = old('sequence_values', implode(',', $savedConfiguration['values'] ?? []));
    $initialCrosswordItems = old('crossword_items', collect($savedConfiguration['entries'] ?? [])->map(fn ($entry) => ['answer' => $entry['original'], 'clue' => $entry['clue']])->all());
    while (count($initialCrosswordItems) < 3) {
        $initialCrosswordItems[] = ['answer' => '', 'clue' => ''];
    }
    $savedHayAhiAyItems = $activity?->tipo === 'hay_ahi_ay' ? ($savedConfiguration['items'] ?? []) : [];
    $initialHayAhiAyItems = old('hay_ahi_ay_items', collect($savedHayAhiAyItems)->map(fn ($item) => ['sentence' => $item['sentence'], 'answer' => $item['answer']])->all());
    while (count($initialHayAhiAyItems) < 3) {
        $initialHayAhiAyItems[] = ['sentence' => '', 'answer' => 'hay'];
    }
    $savedMemoryItems = $activity?->tipo === 'memorama' ? ($savedConfiguration['items'] ?? []) : [];
    $initialMemoryItems = old('memory_items', collect($savedMemoryItems)->map(fn ($item) => ['id' => $item['id'], 'label' => $item['label'], 'image_path' => $item['image_path']])->all());
    while (count($initialMemoryItems) < 3) {
        $initialMemoryItems[] = ['id' => null, 'label' => '', 'image_path' => null];
    }
    while (count($initialWords) < 3) {
        $initialWords[] = ['text' => ''];
    }
@endphp

<div
    class="space-y-5"
    x-data="{
        activityType: @js(old('tipo', $activity?->tipo ?? 'tarea')),
        pairs: @js($initialPairs),
        words: @js($initialWords),
        crosswordItems: @js($initialCrosswordItems),
        hayAhiAyItems: @js($initialHayAhiAyItems),
        memoryItems: @js($initialMemoryItems),
        addPair() {
            if (this.pairs.length < 20) this.pairs.push({ left: '', right: '' });
        },
        removePair(index) {
            if (this.pairs.length > 2) this.pairs.splice(index, 1);
        },
        addWord() {
            if (this.words.length < 20) this.words.push({ text: '' });
        },
        removeWord(index) {
            if (this.words.length > 3) this.words.splice(index, 1);
        },
        addCrosswordItem() {
            if (this.crosswordItems.length < 15) this.crosswordItems.push({ answer: '', clue: '' });
        },
        removeCrosswordItem(index) {
            if (this.crosswordItems.length > 3) this.crosswordItems.splice(index, 1);
        },
        addHayAhiAyItem() {
            if (this.hayAhiAyItems.length < 30) this.hayAhiAyItems.push({ sentence: '', answer: 'hay' });
        },
        removeHayAhiAyItem(index) {
            if (this.hayAhiAyItems.length > 3) this.hayAhiAyItems.splice(index, 1);
        },
        addMemoryItem() {
            if (this.memoryItems.length < 12) this.memoryItems.push({ id: null, label: '', image_path: null });
        },
        removeMemoryItem(index) {
            if (this.memoryItems.length > 3) this.memoryItems.splice(index, 1);
        },
    }"
>
    <div>
        <label for="teaching_assignment_id" class="mb-1 block text-sm font-medium dark:text-zinc-300">Asignación docente</label>
        <select id="teaching_assignment_id" name="teaching_assignment_id" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            <option value="">Selecciona una asignación</option>
            @foreach ($teachingAssignments as $assignment)
                <option value="{{ $assignment->id }}" @selected(old('teaching_assignment_id', $activity?->teaching_assignment_id) == $assignment->id)>
                    {{ $assignment->subject?->nombre_materia }} — {{ $assignment->schoolGroup?->schoolGrade?->nombre_grado }} {{ $assignment->schoolGroup?->nombre_grupo }} — {{ $assignment->teacher?->name }}
                </option>
            @endforeach
        </select>
        @error('teaching_assignment_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="titulo" class="mb-1 block text-sm font-medium dark:text-zinc-300">Título</label>
        <input id="titulo" name="titulo" value="{{ old('titulo', $activity?->titulo) }}" required maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
        @error('titulo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="descripcion" class="mb-1 block text-sm font-medium dark:text-zinc-300">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="4" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">{{ old('descripcion', $activity?->descripcion) }}</textarea>
            @error('descripcion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="instrucciones" class="mb-1 block text-sm font-medium dark:text-zinc-300">Instrucciones</label>
            <textarea id="instrucciones" name="instrucciones" rows="4" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">{{ old('instrucciones', $activity?->instrucciones) }}</textarea>
            @error('instrucciones')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div>
            <label for="tipo" class="mb-1 block text-sm font-medium dark:text-zinc-300">Tipo</label>
            <select id="tipo" name="tipo" x-model="activityType" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                @foreach (['archivo' => 'Archivo', 'secuencia' => 'Secuencia numérica', 'sopa_letras' => 'Sopa de letras', 'crucigrama' => 'Crucigrama', 'hay_ahi_ay' => 'Hay, ahí o ay', 'memorama' => 'Memorama', 'relacion_columnas' => 'Relación de columnas'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('tipo', $activity?->tipo ?? 'tarea') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('tipo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="puntaje_maximo" class="mb-1 block text-sm font-medium dark:text-zinc-300">Puntaje máximo</label>
            <input id="puntaje_maximo" name="puntaje_maximo" type="number" min="0.01" step="0.01" value="{{ old('puntaje_maximo', $activity?->puntaje_maximo ?? 10) }}" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            @error('puntaje_maximo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="estado" class="mb-1 block text-sm font-medium dark:text-zinc-300">Estado</label>
            <select id="estado" name="estado" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                @foreach (['borrador' => 'Borrador', 'publicada' => 'Publicada', 'cerrada' => 'Cerrada'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('estado', $activity?->estado ?? 'borrador') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('estado')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <section x-show="activityType === 'relacion_columnas'" x-cloak class="space-y-4 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-950/30">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-blue-900 dark:text-blue-100">Relaciones de conceptos</h2>
                <p class="text-sm text-blue-700 dark:text-blue-300">Agrega entre 2 y 20 parejas. Los identificadores se generan en el servidor.</p>
            </div>
            <button type="button" x-on:click="addPair" x-bind:disabled="pairs.length >= 20" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Agregar relación</button>
        </div>

        <div class="space-y-3">
            <template x-for="(pair, index) in pairs" x-bind:key="index">
                <div class="grid gap-3 rounded-lg border border-blue-200 bg-white p-3 md:grid-cols-[1fr_1fr_auto] dark:border-blue-900 dark:bg-zinc-900">
                    <div>
                        <label x-bind:for="`pair-left-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Concepto</label>
                        <input x-bind:id="`pair-left-${index}`" x-bind:name="`pairs[${index}][left]`" x-model="pair.left" maxlength="255" x-bind:required="activityType === 'relacion_columnas'" x-bind:disabled="activityType !== 'relacion_columnas'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label x-bind:for="`pair-right-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Respuesta</label>
                        <input x-bind:id="`pair-right-${index}`" x-bind:name="`pairs[${index}][right]`" x-model="pair.right" maxlength="255" x-bind:required="activityType === 'relacion_columnas'" x-bind:disabled="activityType !== 'relacion_columnas'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <button type="button" x-on:click="removePair(index)" x-bind:disabled="pairs.length <= 2" class="self-end rounded-lg bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 disabled:opacity-40">Eliminar</button>
                </div>
            </template>
        </div>
        @error('pairs')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('pairs.*.left')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('pairs.*.right')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="max_attempts" class="mb-1 block text-sm font-medium dark:text-zinc-300">Intentos máximos</label>
                <input id="max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $savedConfiguration['max_attempts'] ?? 2) }}" x-bind:required="activityType === 'relacion_columnas'" x-bind:disabled="activityType !== 'relacion_columnas'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                @error('max_attempts')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="shuffle_right_column" value="0" x-bind:disabled="activityType !== 'relacion_columnas'"><input type="checkbox" name="shuffle_right_column" value="1" x-bind:disabled="activityType !== 'relacion_columnas'" @checked(old('shuffle_right_column', $savedConfiguration['shuffle_right_column'] ?? true)) class="rounded border-zinc-300">Mezclar columna derecha</label>
            <label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="show_result_immediately" value="0" x-bind:disabled="activityType !== 'relacion_columnas'"><input type="checkbox" name="show_result_immediately" value="1" x-bind:disabled="activityType !== 'relacion_columnas'" @checked(old('show_result_immediately', $savedConfiguration['show_result_immediately'] ?? true)) class="rounded border-zinc-300">Mostrar resultado inmediatamente</label>
        </div>
    </section>

    <section x-show="activityType === 'sopa_letras'" x-cloak class="space-y-5 rounded-xl border border-violet-200 bg-violet-50 p-5 dark:border-violet-900 dark:bg-violet-950/30">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="font-bold text-violet-900 dark:text-violet-100">Palabras de la sopa</h2><p class="text-sm text-violet-700 dark:text-violet-300">Agrega entre 3 y 20 palabras. La cuadrícula se genera automáticamente al guardar.</p></div>
            <button type="button" x-on:click="addWord" x-bind:disabled="words.length >= 20" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Agregar palabra</button>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <template x-for="(word, index) in words" x-bind:key="index">
                <div class="flex gap-2 rounded-lg border border-violet-200 bg-white p-3 dark:border-violet-900 dark:bg-zinc-900">
                    <div class="min-w-0 flex-1">
                        <label x-bind:for="`word-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300" x-text="`Palabra ${index + 1}`"></label>
                        <input x-bind:id="`word-${index}`" x-bind:name="`words[${index}][text]`" x-model="word.text" maxlength="255" x-bind:required="activityType === 'sopa_letras'" x-bind:disabled="activityType !== 'sopa_letras'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <button type="button" x-on:click="removeWord(index)" x-bind:disabled="words.length <= 3" class="self-end rounded-lg bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 disabled:opacity-40">Eliminar</button>
                </div>
            </template>
        </div>
        @error('words')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('words.*.text')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><label for="word_search_rows" class="mb-1 block text-sm font-medium dark:text-zinc-300">Filas</label><input id="word_search_rows" name="word_search_rows" type="number" min="8" max="20" value="{{ old('word_search_rows', $savedConfiguration['rows'] ?? 12) }}" x-bind:required="activityType === 'sopa_letras'" x-bind:disabled="activityType !== 'sopa_letras'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">@error('word_search_rows')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label for="word_search_columns" class="mb-1 block text-sm font-medium dark:text-zinc-300">Columnas</label><input id="word_search_columns" name="word_search_columns" type="number" min="8" max="20" value="{{ old('word_search_columns', $savedConfiguration['columns'] ?? 12) }}" x-bind:required="activityType === 'sopa_letras'" x-bind:disabled="activityType !== 'sopa_letras'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">@error('word_search_columns')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label for="word_search_max_attempts" class="mb-1 block text-sm font-medium dark:text-zinc-300">Intentos máximos</label><input id="word_search_max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $savedConfiguration['max_attempts'] ?? 2) }}" x-bind:required="activityType === 'sopa_letras'" x-bind:disabled="activityType !== 'sopa_letras'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">@error('max_attempts')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="allow_reverse" value="0" x-bind:disabled="activityType !== 'sopa_letras'"><input type="checkbox" name="allow_reverse" value="1" x-bind:disabled="activityType !== 'sopa_letras'" @checked(old('allow_reverse', $savedConfiguration['allow_reverse'] ?? false)) class="rounded border-zinc-300">Permitir palabras invertidas</label>
        </div>

        @php $selectedDirections = old('word_search_directions', $savedConfiguration['directions'] ?? ['horizontal', 'vertical', 'diagonal_down', 'diagonal_up']); @endphp
        <fieldset><legend class="mb-2 text-sm font-medium dark:text-zinc-300">Direcciones permitidas</legend><div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (['horizontal' => 'Horizontal', 'vertical' => 'Vertical', 'diagonal_down' => 'Diagonal descendente', 'diagonal_up' => 'Diagonal ascendente'] as $direction => $label)
                <label class="flex items-center gap-2 text-sm dark:text-zinc-300"><input type="checkbox" name="word_search_directions[]" value="{{ $direction }}" x-bind:disabled="activityType !== 'sopa_letras'" @checked(in_array($direction, $selectedDirections, true)) class="rounded border-zinc-300">{{ $label }}</label>
            @endforeach
        </div></fieldset>
        @error('word_search_directions')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="show_result_immediately" value="0" x-bind:disabled="activityType !== 'sopa_letras'"><input type="checkbox" name="show_result_immediately" value="1" x-bind:disabled="activityType !== 'sopa_letras'" @checked(old('show_result_immediately', $savedConfiguration['show_result_immediately'] ?? true)) class="rounded border-zinc-300">Mostrar puntuación inmediatamente al estudiante</label>
    </section>

    <section x-show="activityType === 'secuencia'" x-cloak class="space-y-5 rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/30">
        <div>
            <h2 class="font-bold text-emerald-900 dark:text-emerald-100">Secuencia numérica</h2>
            <p class="text-sm text-emerald-700 dark:text-emerald-300">Escribe entre 4 y 30 valores separados por comas. El primer valor siempre será visible.</p>
        </div>
        <div>
            <label for="sequence_values" class="mb-1 block text-sm font-medium dark:text-zinc-300">Valores de la secuencia</label>
            <input id="sequence_values" name="sequence_values" value="{{ $initialSequence }}" placeholder="1,2,3,4,5,6" x-bind:required="activityType === 'secuencia'" x-bind:disabled="activityType !== 'secuencia'" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            <p class="mt-1 text-xs text-zinc-500">Se permiten enteros, negativos y decimales; por ejemplo: 2, 4, 8, 16, 32.</p>
            @error('sequence_values')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div><label for="sequence_hidden_count" class="mb-1 block text-sm font-medium dark:text-zinc-300">Valores por ocultar</label><input id="sequence_hidden_count" name="sequence_hidden_count" type="number" min="1" max="29" value="{{ old('sequence_hidden_count', $savedConfiguration['hidden_count'] ?? 2) }}" x-bind:required="activityType === 'secuencia'" x-bind:disabled="activityType !== 'secuencia'" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">@error('sequence_hidden_count')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label for="sequence_max_attempts" class="mb-1 block text-sm font-medium dark:text-zinc-300">Intentos máximos</label><input id="sequence_max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $savedConfiguration['max_attempts'] ?? 2) }}" x-bind:required="activityType === 'secuencia'" x-bind:disabled="activityType !== 'secuencia'" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">@error('max_attempts')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="show_result_immediately" value="0" x-bind:disabled="activityType !== 'secuencia'"><input type="checkbox" name="show_result_immediately" value="1" x-bind:disabled="activityType !== 'secuencia'" @checked(old('show_result_immediately', $savedConfiguration['show_result_immediately'] ?? true)) class="rounded border-zinc-300">Mostrar resultado inmediatamente</label>
        </div>
    </section>

    <section x-show="activityType === 'crucigrama'" x-cloak class="space-y-5 rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/30">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="font-bold text-amber-900 dark:text-amber-100">Palabras y pistas del crucigrama</h2><p class="text-sm text-amber-700 dark:text-amber-300">Agrega entre 3 y 15 respuestas que compartan letras. La cuadrícula se acomoda automáticamente.</p></div>
            <button type="button" x-on:click="addCrosswordItem" x-bind:disabled="crosswordItems.length >= 15" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Agregar palabra</button>
        </div>
        <div class="space-y-3">
            <template x-for="(item, index) in crosswordItems" x-bind:key="index">
                <div class="grid gap-3 rounded-lg border border-amber-200 bg-white p-3 md:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)_auto] dark:border-amber-900 dark:bg-zinc-900">
                    <div><label x-bind:for="`crossword-answer-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Respuesta</label><input x-bind:id="`crossword-answer-${index}`" x-bind:name="`crossword_items[${index}][answer]`" x-model="item.answer" maxlength="255" x-bind:required="activityType === 'crucigrama'" x-bind:disabled="activityType !== 'crucigrama'" placeholder="PEQUEÑO" class="w-full rounded-lg border border-zinc-300 px-3 py-2 uppercase dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div>
                    <div><label x-bind:for="`crossword-clue-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Pista</label><input x-bind:id="`crossword-clue-${index}`" x-bind:name="`crossword_items[${index}][clue]`" x-model="item.clue" maxlength="500" x-bind:required="activityType === 'crucigrama'" x-bind:disabled="activityType !== 'crucigrama'" placeholder="Antónimo de grande" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div>
                    <button type="button" x-on:click="removeCrosswordItem(index)" x-bind:disabled="crosswordItems.length <= 3" class="self-end rounded-lg bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 disabled:opacity-40">Eliminar</button>
                </div>
            </template>
        </div>
        @error('crossword_items')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('crossword_items.*.answer')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('crossword_items.*.clue')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="crossword_max_attempts" class="mb-1 block text-sm font-medium dark:text-zinc-300">Intentos máximos</label><input id="crossword_max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $savedConfiguration['max_attempts'] ?? 2) }}" x-bind:required="activityType === 'crucigrama'" x-bind:disabled="activityType !== 'crucigrama'" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div>
            <label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="show_result_immediately" value="0" x-bind:disabled="activityType !== 'crucigrama'"><input type="checkbox" name="show_result_immediately" value="1" x-bind:disabled="activityType !== 'crucigrama'" @checked(old('show_result_immediately', $savedConfiguration['show_result_immediately'] ?? true)) class="rounded border-zinc-300">Mostrar resultado inmediatamente</label>
        </div>
    </section>

    <section x-show="activityType === 'hay_ahi_ay'" x-cloak class="space-y-5 rounded-xl border border-cyan-200 bg-cyan-50 p-5 dark:border-cyan-900 dark:bg-cyan-950/30">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="font-bold text-cyan-900 dark:text-cyan-100">¿Hay, ahí o ay?</h2><p class="text-sm text-cyan-700 dark:text-cyan-300">Escribe `___` donde debe aparecer la respuesta. Agrega entre 3 y 30 oraciones.</p></div>
            <button type="button" x-on:click="addHayAhiAyItem" x-bind:disabled="hayAhiAyItems.length >= 30" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Agregar oración</button>
        </div>
        <div class="space-y-3">
            <template x-for="(item, index) in hayAhiAyItems" x-bind:key="index">
                <div class="grid gap-3 rounded-lg border border-cyan-200 bg-white p-3 md:grid-cols-[minmax(0,1fr)_9rem_auto] dark:border-cyan-900 dark:bg-zinc-900">
                    <div><label x-bind:for="`hay-sentence-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Oración</label><input x-bind:id="`hay-sentence-${index}`" x-bind:name="`hay_ahi_ay_items[${index}][sentence]`" x-model="item.sentence" maxlength="500" x-bind:required="activityType === 'hay_ahi_ay'" x-bind:disabled="activityType !== 'hay_ahi_ay'" placeholder="___ suficiente comida para todos." class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div>
                    <div><label x-bind:for="`hay-answer-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Respuesta</label><select x-bind:id="`hay-answer-${index}`" x-bind:name="`hay_ahi_ay_items[${index}][answer]`" x-model="item.answer" x-bind:required="activityType === 'hay_ahi_ay'" x-bind:disabled="activityType !== 'hay_ahi_ay'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"><option value="hay">HAY</option><option value="ahí">AHÍ</option><option value="ay">AY</option></select></div>
                    <button type="button" x-on:click="removeHayAhiAyItem(index)" x-bind:disabled="hayAhiAyItems.length <= 3" class="self-end rounded-lg bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 disabled:opacity-40">Eliminar</button>
                </div>
            </template>
        </div>
        @error('hay_ahi_ay_items')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('hay_ahi_ay_items.*.sentence')<p class="text-sm text-red-600">Cada oración debe incluir ___.</p>@enderror
        @error('hay_ahi_ay_items.*.answer')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="hay_ahi_ay_max_attempts" class="mb-1 block text-sm font-medium dark:text-zinc-300">Intentos máximos</label><input id="hay_ahi_ay_max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $savedConfiguration['max_attempts'] ?? 2) }}" x-bind:required="activityType === 'hay_ahi_ay'" x-bind:disabled="activityType !== 'hay_ahi_ay'" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div>
            <label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="show_result_immediately" value="0" x-bind:disabled="activityType !== 'hay_ahi_ay'"><input type="checkbox" name="show_result_immediately" value="1" x-bind:disabled="activityType !== 'hay_ahi_ay'" @checked(old('show_result_immediately', $savedConfiguration['show_result_immediately'] ?? true)) class="rounded border-zinc-300">Mostrar resultado inmediatamente</label>
        </div>
    </section>

    <section x-show="activityType === 'memorama'" x-cloak class="space-y-5 rounded-xl border border-fuchsia-200 bg-fuchsia-50 p-5 dark:border-fuchsia-900 dark:bg-fuchsia-950/30">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-bold text-fuchsia-900 dark:text-fuchsia-100">Parejas del memorama</h2><p class="text-sm text-fuchsia-700 dark:text-fuchsia-300">Sube entre 3 y 12 imágenes. Cada una se duplicará para crear una pareja.</p></div><button type="button" x-on:click="addMemoryItem" x-bind:disabled="memoryItems.length >= 12" class="rounded-lg bg-fuchsia-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Agregar tarjeta</button></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"><template x-for="(item, index) in memoryItems" x-bind:key="item.id ?? `new-${index}`"><div class="space-y-3 rounded-lg border border-fuchsia-200 bg-white p-3 dark:border-fuchsia-900 dark:bg-zinc-900"><input type="hidden" x-bind:name="`memory_items[${index}][id]`" x-bind:value="item.id"><div><label x-bind:for="`memory-label-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Nombre de la pareja</label><input x-bind:id="`memory-label-${index}`" x-bind:name="`memory_items[${index}][label]`" x-model="item.label" maxlength="100" x-bind:required="activityType === 'memorama'" x-bind:disabled="activityType !== 'memorama'" placeholder="Mi familia" class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div><template x-if="item.image_path"><p class="text-xs font-medium text-green-600">Imagen guardada; sube otra sólo para reemplazarla.</p></template><div><label x-bind:for="`memory-image-${index}`" class="mb-1 block text-sm font-medium dark:text-zinc-300">Imagen</label><input x-bind:id="`memory-image-${index}`" x-bind:name="`memory_items[${index}][image]`" type="file" accept=".jpg,.jpeg,.png,.webp" x-bind:required="activityType === 'memorama' && !item.image_path" x-bind:disabled="activityType !== 'memorama'" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div><button type="button" x-on:click="removeMemoryItem(index)" x-bind:disabled="memoryItems.length <= 3" class="w-full rounded-lg bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 disabled:opacity-40">Eliminar</button></div></template></div>
        @error('memory_items')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('memory_items.*.label')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('memory_items.*.image')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="grid gap-4 sm:grid-cols-2"><div><label for="memory_max_attempts" class="mb-1 block text-sm font-medium dark:text-zinc-300">Intentos máximos</label><input id="memory_max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $savedConfiguration['max_attempts'] ?? 2) }}" x-bind:required="activityType === 'memorama'" x-bind:disabled="activityType !== 'memorama'" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></div><label class="flex items-center gap-3 text-sm dark:text-zinc-300"><input type="hidden" name="show_result_immediately" value="0" x-bind:disabled="activityType !== 'memorama'"><input type="checkbox" name="show_result_immediately" value="1" x-bind:disabled="activityType !== 'memorama'" @checked(old('show_result_immediately', $savedConfiguration['show_result_immediately'] ?? true)) class="rounded border-zinc-300">Mostrar resultado inmediatamente</label></div>
    </section>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="fecha_publicacion" class="mb-1 block text-sm font-medium dark:text-zinc-300">Fecha de publicación</label>
            <input id="fecha_publicacion" name="fecha_publicacion" type="datetime-local" value="{{ old('fecha_publicacion', $activity?->fecha_publicacion?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            @error('fecha_publicacion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="fecha_limite" class="mb-1 block text-sm font-medium dark:text-zinc-300">Fecha límite</label>
            <input id="fecha_limite" name="fecha_limite" type="datetime-local" value="{{ old('fecha_limite', $activity?->fecha_limite?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            @error('fecha_limite')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="archivo" class="mb-1 block text-sm font-medium dark:text-zinc-300">Archivo adjunto</label>
        <input id="archivo" name="archivo" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.txt" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
        @if ($activity?->archivo_path)<p class="mt-1 text-xs text-zinc-500">Actual: {{ basename($activity->archivo_path) }}</p>@endif
        @error('archivo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-3 text-sm dark:text-zinc-300">
        <input type="hidden" name="permite_entrega_tardia" value="0">
        <input type="checkbox" name="permite_entrega_tardia" value="1" @checked(old('permite_entrega_tardia', $activity?->permite_entrega_tardia ?? false)) class="rounded border-zinc-300">
        Permitir entregas después de la fecha límite
    </label>

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a href="{{ route('activities.index') }}" wire:navigate class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold dark:border-zinc-600 dark:text-zinc-300">Cancelar</a>
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ $buttonText }}</button>
    </div>
</div>
