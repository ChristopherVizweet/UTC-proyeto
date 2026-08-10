<x-layouts::app :title="$activity->titulo">
    <div class="p-6">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-blue-600">Relación de columnas</p>
                    <h1 class="text-2xl font-bold dark:text-white">{{ $activity->titulo }}</h1>
                    <p class="text-zinc-500">Relaciona cada concepto con su respuesta correcta.</p>
                </div>
                <a href="{{ route('activities.show', $activity) }}" wire:navigate class="rounded-lg border border-zinc-300 px-4 py-2 dark:border-zinc-700 dark:text-white">Volver a la actividad</a>
            </div>

            @error('attempt')<div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800">{{ $message }}</div>@enderror

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Intentos realizados</p><p class="text-2xl font-bold dark:text-white">{{ $attempts->count() }} / {{ $maxAttempts }}</p></div>
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Intentos disponibles</p><p class="text-2xl font-bold dark:text-white">{{ $remainingAttempts }}</p></div>
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Mejor puntuación</p><p class="text-2xl font-bold dark:text-white">{{ $showResults && $bestScore !== null ? $bestScore.' / '.$activity->puntaje_maximo : '—' }}</p></div>
            </div>

            @if ($remainingAttempts > 0 || $attempts->contains('status', 'iniciado'))
                <form method="POST" action="{{ route('activities.attempts.start', $activity) }}">
                    @csrf
                    <button class="w-full rounded-xl bg-blue-600 px-6 py-4 text-lg font-bold text-white sm:w-auto">
                        {{ $attempts->contains('status', 'iniciado') ? 'Continuar intento' : 'Iniciar nuevo intento' }}
                    </button>
                </form>
            @else
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-5 text-amber-800">Ya utilizaste todos los intentos disponibles.</div>
            @endif

            <section class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="border-b border-zinc-200 p-5 text-lg font-bold dark:border-zinc-700 dark:text-white">Mis intentos</h2>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($attempts as $attempt)
                        <div class="flex flex-wrap items-center justify-between gap-3 p-5 dark:text-zinc-300">
                            <div><p class="font-semibold">Intento {{ $attempt->attempt_number }}</p><p class="text-sm text-zinc-500">{{ ucfirst($attempt->status) }} · {{ $attempt->completed_at?->format('d/m/Y H:i') ?? 'En progreso' }}</p></div>
                            <div class="flex items-center gap-4"><span class="font-bold">{{ $showResults && $attempt->score !== null ? $attempt->score.' / '.$attempt->max_score : '—' }}</span><a href="{{ route('activities.attempts.result', $attempt) }}" wire:navigate class="text-blue-600">{{ $attempt->status === 'iniciado' ? 'Continuar' : 'Ver resultado' }}</a></div>
                        </div>
                    @empty
                        <p class="p-5 text-zinc-500">Todavía no has realizado intentos.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
