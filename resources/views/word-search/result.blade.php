<x-layouts::app :title="'Resultado · '.$attempt->activity->titulo">
    <div class="p-6"><div class="mx-auto max-w-3xl space-y-6">
        @if (session('success'))<div class="rounded-lg border border-green-300 bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif
        <div><p class="text-sm font-semibold text-violet-600">Intento {{ $attempt->attempt_number }}</p><h1 class="text-2xl font-bold dark:text-white">{{ $attempt->activity->titulo }}</h1></div>
        <div class="rounded-xl border p-6 text-center dark:border-zinc-700"><p class="text-sm text-zinc-500">Puntuación</p><p class="text-4xl font-bold text-violet-600">{{ $showResult ? $attempt->score : 'Registrada' }}</p>@if ($showResult)<p class="mt-2 dark:text-zinc-300">Encontraste {{ $correctCount }} de {{ $totalWords }} palabras.</p>@else<p class="mt-2 text-zinc-500">El docente configuró esta actividad para no mostrar el resultado inmediatamente.</p>@endif<p class="mt-4 text-sm dark:text-zinc-300">Tu mejor puntuación: {{ $bestScore ?? '—' }}</p></div>
        <div class="flex flex-wrap gap-3"><a href="{{ route('activities.word-search.play', $attempt->activity) }}" class="rounded-lg bg-violet-600 px-4 py-2 font-semibold text-white">Volver a intentos</a><a href="{{ route('activities.show', $attempt->activity) }}" class="rounded-lg border px-4 py-2 dark:text-white">Ver actividad</a></div>
    </div></div>
</x-layouts::app>
