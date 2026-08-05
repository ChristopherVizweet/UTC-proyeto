<x-layouts::app :title="$academicPeriod->nombre_periodo">
    <div class="p-6">
        <div class="mx-auto max-w-5xl space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        {{ $academicPeriod->nombre_periodo }}
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Del {{ $academicPeriod->fecha_inicio?->format('d/m/Y') }}
                        al {{ $academicPeriod->fecha_fin?->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('academic-periods.index') }}"
                        wire:navigate
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-white"
                    >
                        Regresar
                    </a>

                    <a
                        href="{{ route('academic-periods.edit', $academicPeriod) }}"
                        wire:navigate
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Editar
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                    Grupos registrados
                </h2>

                <div class="space-y-3">
                    @forelse ($academicPeriod->schoolGroups as $group)
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $group->schoolGrade?->nombre_grado }}
                                — Grupo {{ $group->nombre_grupo }}
                            </p>

                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Capacidad:
                                {{ $group->capacidad_grupo ?? 'No especificada' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-zinc-500">
                            Este periodo todavía no tiene grupos registrados.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>