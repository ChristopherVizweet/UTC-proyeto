<x-layouts::app :title="$attempt->activity->titulo">
    <script>
        window.crosswordInput = () => ({
            move(event, row, column) {
                const input = event.target;
                input.value = input.value.toUpperCase();
                if (!input.value) return;
                const next = document.querySelector(`[data-crossword-cell="${row}:${column + 1}"]`) ?? document.querySelector(`[data-crossword-cell="${row + 1}:${column}"]`);
                next?.focus();
            },
        });
    </script>
    <div class="p-4 sm:p-6"><div class="mx-auto max-w-7xl space-y-6">
        <div><p class="text-sm font-semibold text-amber-600">Intento {{ $attempt->attempt_number }}</p><h1 class="text-2xl font-bold dark:text-white">{{ $attempt->activity->titulo }}</h1><p class="text-zinc-500">Completa una letra por casilla usando las pistas.</p></div>
        @if ($errors->any())<div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('activities.crossword.attempts.submit', $attempt) }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">@csrf
            <div class="overflow-x-auto rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30" x-data="crosswordInput">
                <div class="mx-auto grid w-max gap-0" style="grid-template-columns: repeat({{ $attempt->answers['columns'] }}, 2.5rem)">
                    @for ($row = 0; $row < $attempt->answers['rows']; $row++)
                        @for ($column = 0; $column < $attempt->answers['columns']; $column++)
                            @php $key = $row.':'.$column; @endphp
                            @if (in_array($key, $attempt->answers['cell_keys'], true))
                                <div class="relative h-10 w-10 border border-zinc-700 bg-white dark:bg-zinc-900">
                                    @if (isset($attempt->answers['numbers'][$key]))<span class="pointer-events-none absolute left-0.5 top-0 text-[9px] font-bold text-zinc-500">{{ $attempt->answers['numbers'][$key] }}</span>@endif
                                    <label for="cell-{{ $row }}-{{ $column }}" class="sr-only">Fila {{ $row + 1 }}, columna {{ $column + 1 }}</label>
                                    <input id="cell-{{ $row }}-{{ $column }}" data-crossword-cell="{{ $key }}" name="cells[{{ $key }}]" value="{{ old('cells.'.$key) }}" maxlength="1" required autocomplete="off" x-on:input="move($event, {{ $row }}, {{ $column }})" class="h-full w-full border-0 bg-transparent p-0 text-center text-xl font-bold uppercase text-zinc-900 focus:ring-2 focus:ring-amber-500 dark:text-white">
                                </div>
                            @else
                                <div class="h-10 w-10 bg-transparent"></div>
                            @endif
                        @endfor
                    @endfor
                </div>
            </div>
            <aside class="space-y-5">
                @foreach (['horizontal' => 'Horizontales', 'vertical' => 'Verticales'] as $direction => $label)
                    <section class="rounded-xl border bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"><h2 class="mb-3 font-bold text-amber-700 dark:text-amber-300">{{ $label }}</h2><ol class="space-y-3">@foreach (collect($attempt->answers['entries'])->where('direction', $direction) as $entry)<li class="text-sm dark:text-zinc-300"><span class="font-bold">{{ $entry['number'] }}.</span> {{ $entry['clue'] }}</li>@endforeach</ol></section>
                @endforeach
                <button class="w-full rounded-lg bg-amber-600 px-6 py-3 text-lg font-bold text-white">Enviar crucigrama</button>
            </aside>
        </form>
    </div></div>
</x-layouts::app>
