<x-layouts::app :title="$subject->nombre_materia ?? 'Materia'">
    <div class="p-6">
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600">
                        {{ $subject->code ?? 'Sin código' }}
                    </p>

                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        {{ $subject->nombre_materia }}
                    </h1>
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('subjects.index') }}"
                        wire:navigate
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-white"
                    >
                        Regresar
                    </a>

                    <a
                        href="{{ route('subjects.edit', $subject) }}"
                        wire:navigate
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Editar
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-2 font-semibold text-zinc-900 dark:text-white">
                    Descripción
                </h2>

                <p class="text-zinc-600 dark:text-zinc-400">
                    {{ $subject->descripcion_materia ?? 'Sin descripción.' }}
                </p>
            </div>
        </div>
    </div>
</x-layouts::app>