<x-layouts::app :title="$attempt->activity->titulo">
    <script>
        window.wordSearch = (configuration) => ({
            configuration,
            selections: [],
            start: null,
            current: null,
            pointerActive: false,
            dragged: false,
            startPointer(row, column) {
                if (this.start && !this.pointerActive) {
                    this.current = {
                        row,
                        column
                    };
                    this.commit();
                    return;
                }
                this.start = {
                    row,
                    column
                };
                this.current = {
                    row,
                    column
                };
                this.pointerActive = true;
                this.dragged = false;
            },
            movePointer(event) {
                if (!this.pointerActive) return;
                const cell = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-word-search-cell]');
                if (!cell) return;
                this.extendPointer(Number(cell.dataset.row), Number(cell.dataset.column));
            },
            extendPointer(row, column) {
                if (!this.pointerActive || !this.validLine(this.start, {
                        row,
                        column
                    })) return;
                if (row !== this.start.row || column !== this.start.column) this.dragged = true;
                this.current = {
                    row,
                    column
                };
            },
            finishPointer() {
                if (!this.pointerActive) return;
                this.pointerActive = false;
                if (this.dragged) this.commit();
            },
            keyboardSelect(row, column) {
                if (!this.start) {
                    this.start = {
                        row,
                        column
                    };
                    this.current = {
                        row,
                        column
                    };
                    return;
                }
                this.current = {
                    row,
                    column
                };
                this.commit();
            },
            commit() {
                if (!this.start || !this.current || !this.validLine(this.start, this.current) || !this.hasLength(this.start, this.current)) {
                    this.start = null;
                    this.current = null;
                    return;
                }
                const item = {
                    start_row: this.start.row,
                    start_column: this.start.column,
                    end_row: this.current.row,
                    end_column: this.current.column
                };
                if (!this.selections.some(value => JSON.stringify(value) === JSON.stringify(item))) this.selections.push(item);
                this.start = null;
                this.current = null;
            },
            hasLength(start, end) {
                return start.row !== end.row || start.column !== end.column;
            },
            validLine(start, end) {
                if (!start || !end) return false;
                const row = Math.abs(end.row - start.row);
                const column = Math.abs(end.column - start.column);
                return row === 0 || column === 0 || row === column;
            },
            coordinates(selection) {
                const rowStep = Math.sign(selection.end_row - selection.start_row);
                const columnStep = Math.sign(selection.end_column - selection.start_column);
                const length = Math.max(Math.abs(selection.end_row - selection.start_row), Math.abs(selection.end_column - selection.start_column));
                return Array.from({
                    length: length + 1
                }, (_, index) => `${selection.start_row + rowStep * index}:${selection.start_column + columnStep * index}`);
            },
            normalize(value) {
                return value.toUpperCase().replace(/[Á]/g, 'A').replace(/[É]/g, 'E').replace(/[Í]/g, 'I').replace(/[Ó]/g, 'O').replace(/[ÚÜ]/g, 'U').replace(/[^A-ZÑ0-9]/g, '');
            },
            selectionText(selection) {
                return this.coordinates(selection).map(coordinate => {
                    const [row, column] = coordinate.split(':').map(Number);
                    return configuration.grid[row][column];
                }).join('');
            },
            isFound(word) {
                const normalized = this.normalize(word.original);
                return this.selections.some(selection => {
                    const selected = this.selectionText(selection);
                    return selected === normalized || [...selected].reverse().join('') === normalized;
                });
            },
            isHighlighted(row, column) {
                const key = `${row}:${column}`;
                const permanent = this.selections.some(selection => this.coordinates(selection).includes(key));
                const preview = this.start && this.current && this.validLine(this.start, this.current) && this.coordinates({
                    start_row: this.start.row,
                    start_column: this.start.column,
                    end_row: this.current.row,
                    end_column: this.current.column
                }).includes(key);
                return permanent || preview;
            },
            remove(index) {
                this.selections.splice(index, 1);
            },
            label(selection) {
                return `${this.selectionText(selection)} · (${selection.start_row + 1}, ${selection.start_column + 1}) → (${selection.end_row + 1}, ${selection.end_column + 1})`;
            },
        });
    </script>
    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-6xl space-y-5">
            <div>
                <p class="text-sm font-semibold text-violet-600">Intento {{ $attempt->attempt_number }}</p>
                <h1 class="text-2xl font-bold dark:text-white">{{ $attempt->activity->titulo }}</h1>
                <p class="text-sm text-zinc-500">Selecciona la primera y la última letra de cada palabra. También puedes arrastrar con mouse o pantalla táctil.</p>
            </div>
            @if ($errors->any())<div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800">
                <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>@endif
            <form method="POST" action="{{ route('activities.word-search.attempts.submit', $attempt) }}" x-data="wordSearch(@js($publicConfiguration))" x-on:pointermove.window="movePointer($event)" x-on:pointerup.window="finishPointer" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                @csrf
                <div class="overflow-x-auto rounded-xl border border-violet-200 bg-white p-3 dark:border-violet-900 dark:bg-zinc-900">
                    <div class="mx-auto grid w-max select-none gap-1 touch-none" style="grid-template-columns: repeat({{ $publicConfiguration['columns'] }}, minmax(2rem, 2.5rem));" role="grid" aria-label="Cuadrícula de sopa de letras">
                        @foreach ($publicConfiguration['grid'] as $row => $letters)
                        @foreach ($letters as $column => $letter)
                        <button type="button" role="gridcell" data-word-search-cell data-row="{{ $row }}" data-column="{{ $column }}" aria-label="Fila {{ $row + 1 }}, columna {{ $column + 1 }}, letra {{ $letter }}" x-on:pointerdown.prevent="startPointer({{ $row }}, {{ $column }})" x-on:pointerenter="extendPointer({{ $row }}, {{ $column }})" x-on:keydown.enter.prevent="keyboardSelect({{ $row }}, {{ $column }})" x-on:keydown.space.prevent="keyboardSelect({{ $row }}, {{ $column }})" x-bind:class="isHighlighted({{ $row }}, {{ $column }}) ? 'bg-violet-600 text-white' : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white'" class="flex h-9 w-9 items-center justify-center rounded-md font-bold transition sm:h-10 sm:w-10">{{ $letter }}</button>
                        @endforeach
                        @endforeach
                    </div>
                </div>
                <aside class="space-y-4">
                    <div class="rounded-xl border p-4 dark:border-zinc-700">
                        <h2 class="mb-3 font-bold dark:text-white">Instrucciones</h2>
                        <p class="whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $attempt->activity->instrucciones ?: 'Selecciona la primera y la última letra o número de cada elemento encontrado. Puedes hacerlo con dos clics o arrastrando sobre la cuadrícula.' }}</p>
                    </div>
                    <div class="rounded-xl border p-4 dark:border-zinc-700">
                        <h2 class="font-bold dark:text-white">Selecciones</h2>
                        <p class="mb-3 text-xs text-zinc-500" x-text="`${selections.length} seleccionada(s)`"></p><template x-for="(selection, index) in selections" x-bind:key="index">
                            <div><input type="hidden" x-bind:name="`selections[${index}][start_row]`" x-bind:value="selection.start_row"><input type="hidden" x-bind:name="`selections[${index}][start_column]`" x-bind:value="selection.start_column"><input type="hidden" x-bind:name="`selections[${index}][end_row]`" x-bind:value="selection.end_row"><input type="hidden" x-bind:name="`selections[${index}][end_column]`" x-bind:value="selection.end_column"><button type="button" x-on:click="remove(index)" class="mb-2 w-full rounded-lg bg-zinc-100 px-3 py-2 text-left text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"><span x-text="label(selection)"></span> · Quitar</button></div>
                        </template>
                    </div>
                    <button type="submit" x-bind:disabled="selections.length === 0" class="w-full rounded-lg bg-violet-600 px-5 py-3 font-bold text-white disabled:opacity-50">Enviar intento</button>
                </aside>
            </form>
        </div>
    </div>
</x-layouts::app>
