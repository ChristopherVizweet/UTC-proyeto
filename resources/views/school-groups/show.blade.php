<x-layouts::app :title="'Grupo ' . ($schoolGroup->nombre_grupo ?? '')">
    <div class="p-6">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Grupo {{ $schoolGroup->nombre_grupo }}
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $schoolGroup->schoolGrade?->nombre_grado }}
                        ·
                        {{ $schoolGroup->academicPeriod?->nombre_periodo }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('school-groups.index') }}"
                        wire:navigate
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-white"
                    >
                        Regresar
                    </a>

                    <a
                        href="{{ route('school-groups.edit', $schoolGroup) }}"
                        wire:navigate
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Editar
                    </a>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-500">Periodo</p>
                    <p class="font-semibold dark:text-white">
                        {{ $schoolGroup->academicPeriod?->nombre_periodo }}
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-500">Grado</p>
                    <p class="font-semibold dark:text-white">
                        {{ $schoolGroup->schoolGrade?->nombre_grado }}
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-500">Capacidad</p>
                    <p class="font-semibold dark:text-white">
                        {{ $schoolGroup->capacidad_grupo ?? 'No especificada' }}
                    </p>
                </div>
            </div>

            @if ($schoolGroup->descripcion_grupo)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-2 font-semibold dark:text-white">
                        Descripción
                    </h2>

                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ $schoolGroup->descripcion_grupo }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>