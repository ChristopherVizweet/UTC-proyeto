<x-layouts::app :title="$activity->titulo">
    <div class="p-6"><div class="mx-auto max-w-4xl space-y-6">
        <div><a href="{{ route('activities.show', $activity) }}" class="text-sm font-semibold text-blue-600">← Volver a la actividad</a><h1 class="mt-2 text-2xl font-bold dark:text-white">{{ $activity->titulo }}</h1><p class="text-zinc-500">Sopa de letras · {{ $maxAttempts }} intento(s) disponibles</p></div>
        @error('attempt')<div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800">{{ $message }}</div>@enderror
        <div class="grid gap-4 sm:grid-cols-3"><div class="rounded-xl border p-4 dark:border-zinc-700"><p class="text-sm text-zinc-500">Intentos restantes</p><p class="text-2xl font-bold dark:text-white">{{ $remainingAttempts }}</p></div><div class="rounded-xl border p-4 dark:border-zinc-700"><p class="text-sm text-zinc-500">Mejor puntuación</p><p class="text-2xl font-bold dark:text-white">{{ $bestScore ?? '—' }}</p></div><div class="rounded-xl border p-4 dark:border-zinc-700"><p class="text-sm text-zinc-500">Resultado inmediato</p><p class="font-bold dark:text-white">{{ $showResults ? 'Sí' : 'No' }}</p></div></div>
        @if ($openAttempt = $attempts->firstWhere('status', 'iniciado'))
            <a href="{{ route('activities.word-search.attempts.result', $openAttempt) }}" class="inline-flex rounded-lg bg-violet-600 px-5 py-3 font-bold text-white">Continuar intento {{ $openAttempt->attempt_number }}</a>
        @elseif ($remainingAttempts > 0)
            <form method="POST" action="{{ route('activities.word-search.attempts.start', $activity) }}">@csrf<button class="rounded-lg bg-violet-600 px-5 py-3 font-bold text-white">Iniciar nuevo intento</button></form>
        @else
            <p class="rounded-lg bg-zinc-100 p-4 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">Ya utilizaste todos tus intentos.</p>
        @endif
        <div class="overflow-x-auto rounded-xl border dark:border-zinc-700"><table class="w-full text-left text-sm"><thead class="bg-zinc-100 dark:bg-zinc-800 dark:text-white"><tr><th class="p-3">Intento</th><th class="p-3">Estado</th><th class="p-3">Puntuación</th><th class="p-3"></th></tr></thead><tbody class="divide-y dark:divide-zinc-700">@foreach ($attempts as $item)<tr class="dark:text-zinc-300"><td class="p-3">#{{ $item->attempt_number }}</td><td class="p-3">{{ ucfirst($item->status) }}</td><td class="p-3">{{ $item->score ?? '—' }}</td><td class="p-3 text-right"><a href="{{ route('activities.word-search.attempts.result', $item) }}" class="text-blue-600">{{ $item->status === 'iniciado' ? 'Continuar' : 'Ver' }}</a></td></tr>@endforeach</tbody></table></div>
    </div></div>
</x-layouts::app>
