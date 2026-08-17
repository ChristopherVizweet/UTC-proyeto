<x-layouts::app :title="$attempt->activity->titulo">
    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-6xl space-y-6">
            <div><p class="text-sm font-semibold text-emerald-600">Intento {{ $attempt->attempt_number }}</p><h1 class="text-2xl font-bold dark:text-white">{{ $attempt->activity->titulo }}</h1><p class="text-zinc-500">Observa los valores visibles y completa los espacios faltantes.</p></div>
            @if ($errors->any())<div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('activities.numeric-sequence.attempts.submit', $attempt) }}" class="space-y-6">
                @csrf
                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/30">
                    @foreach ($attempt->answers['display_values'] as $index => $value)
                        <div class="flex items-center gap-3">
                            @if (in_array($index, $attempt->answers['hidden_indices'], true))
                                <div><label for="answer-{{ $index }}" class="sr-only">Valor {{ $index + 1 }}</label><input id="answer-{{ $index }}" name="answers[{{ $index }}]" type="number" step="any" required value="{{ old('answers.'.$index) }}" class="h-14 w-24 rounded-lg border-2 border-emerald-400 bg-white px-3 text-center text-lg font-bold dark:bg-zinc-900 dark:text-white" placeholder="?"></div>
                            @else
                                <span class="flex h-14 min-w-20 items-center justify-center rounded-lg bg-white px-4 text-lg font-bold text-emerald-900 shadow-sm dark:bg-zinc-900 dark:text-emerald-100">{{ $value }}</span>
                            @endif
                            @unless ($loop->last)<span class="text-xl text-emerald-500" aria-hidden="true">→</span>@endunless
                        </div>
                    @endforeach
                </div>
                <div class="flex flex-wrap justify-end gap-3"><a href="{{ route('activities.numeric-sequence.play', $attempt->activity) }}" class="rounded-lg border px-5 py-3 font-semibold dark:border-zinc-700 dark:text-white">Salir sin enviar</a><button class="rounded-lg bg-emerald-600 px-6 py-3 text-lg font-bold text-white">Enviar respuestas</button></div>
            </form>
        </div>
    </div>
</x-layouts::app>
