<x-layouts::app :title="__('Resultado del intento')">
    <div class="p-6"><div class="mx-auto max-w-4xl space-y-6">
        @if (session('success'))<div class="rounded-lg border border-green-300 bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif
        <div><p class="text-sm font-semibold text-blue-600">Intento {{ $attempt->attempt_number }}</p><h1 class="text-2xl font-bold dark:text-white">{{ $attempt->activity->titulo }}</h1><p class="text-zinc-500">Intento completado en {{ $attempt->time_seconds }} segundos.</p></div>

        @if ($showResult)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-blue-600 p-6 text-white"><p class="text-sm text-blue-100">Puntuación</p><p class="text-3xl font-bold">{{ $attempt->score }} / {{ $attempt->max_score }}</p></div>
                <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700"><p class="text-sm text-zinc-500">Aciertos</p><p class="text-3xl font-bold dark:text-white">{{ $correctCount }} / {{ $totalPairs }}</p></div>
                <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700"><p class="text-sm text-zinc-500">Mejor puntuación</p><p class="text-3xl font-bold dark:text-white">{{ $bestScore }}</p></div>
            </div>
        @else
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 text-blue-900 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-100"><h2 class="font-bold">Tu intento fue recibido</h2><p>El resultado inmediato está desactivado para esta actividad.</p></div>
        @endif

        <div class="flex flex-wrap gap-3"><a href="{{ route('activities.play', $attempt->activity) }}" wire:navigate class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white">Ver mis intentos</a><a href="{{ route('activities.show', $attempt->activity) }}" wire:navigate class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold dark:border-zinc-700 dark:text-white">Volver a la actividad</a></div>
    </div></div>
</x-layouts::app>
