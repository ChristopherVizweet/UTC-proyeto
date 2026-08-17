<x-layouts::app :title="$activity->titulo">
    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div><p class="text-sm font-semibold text-emerald-600">Secuencia numérica</p><h1 class="text-2xl font-bold dark:text-white">{{ $activity->titulo }}</h1><p class="text-zinc-500">Completa los valores faltantes siguiendo el patrón.</p></div>
                <a href="{{ route('activities.show', $activity) }}" class="rounded-lg border border-zinc-300 px-4 py-2 dark:border-zinc-700 dark:text-white">Volver a la actividad</a>
            </div>
            @error('attempt')<div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800">{{ $message }}</div>@enderror
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Intentos realizados</p><p class="text-2xl font-bold dark:text-white">{{ $attempts->count() }} / {{ $maxAttempts }}</p></div>
                <div class="rounded-xl border p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Intentos disponibles</p><p class="text-2xl font-bold dark:text-white">{{ $remainingAttempts }}</p></div>
                <div class="rounded-xl border p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Mejor puntuación</p><p class="text-2xl font-bold dark:text-white">{{ $showResults && $bestScore !== null ? $bestScore.' / '.$activity->puntaje_maximo : '—' }}</p></div>
            </div>
            @if ($remainingAttempts > 0 || $attempts->contains('status', 'iniciado'))
                <form method="POST" action="{{ route('activities.numeric-sequence.attempts.start', $activity) }}">@csrf<button class="rounded-xl bg-emerald-600 px-6 py-4 text-lg font-bold text-white">{{ $attempts->contains('status', 'iniciado') ? 'Continuar intento' : 'Iniciar nuevo intento' }}</button></form>
            @else
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-5 text-amber-800">Ya utilizaste todos los intentos disponibles.</div>
            @endif
            <section class="rounded-xl border bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="border-b p-5 text-lg font-bold dark:border-zinc-700 dark:text-white">Mis intentos</h2>
                <div class="divide-y dark:divide-zinc-700">@forelse ($attempts as $item)<div class="flex flex-wrap items-center justify-between gap-3 p-5 dark:text-zinc-300"><div><p class="font-semibold">Intento {{ $item->attempt_number }}</p><p class="text-sm text-zinc-500">{{ ucfirst($item->status) }}</p></div><a href="{{ route('activities.numeric-sequence.attempts.result', $item) }}" class="text-emerald-600">{{ $item->status === 'iniciado' ? 'Continuar' : 'Ver resultado' }}</a></div>@empty<p class="p-5 text-zinc-500">Todavía no has realizado intentos.</p>@endforelse</div>
            </section>
        </div>
    </div>
</x-layouts::app>
