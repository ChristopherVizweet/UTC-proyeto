<x-layouts::app :title="__('Detalle de inscripción')">
    <div class="p-6">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold dark:text-white">
                        {{ $enrollment->student->name }}
                        {{ $enrollment->student->apellidoPaterno }}
                    </h1>

                    <p class="text-sm text-zinc-500">
                        Detalle de inscripción
                    </p>
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('enrollments.index') }}"
                        wire:navigate
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-white"
                    >
                        Regresar
                    </a>

                    <a
                        href="{{ route('enrollments.edit', $enrollment) }}"
                        wire:navigate
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Editar
                    </a>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-4">
                <div class="rounded-xl border p-5 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500">Periodo</p>
                    <p class="font-semibold dark:text-white">
                        {{ $enrollment->schoolGroup->academicPeriod->nombre_periodo }}
                    </p>
                </div>

                <div class="rounded-xl border p-5 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500">Grado</p>
                    <p class="font-semibold dark:text-white">
                        {{ $enrollment->schoolGroup->schoolGrade->nombre_grado }}
                    </p>
                </div>

                <div class="rounded-xl border p-5 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500">Grupo</p>
                    <p class="font-semibold dark:text-white">
                        {{ $enrollment->schoolGroup->nombre_grupo }}
                    </p>
                </div>

                <div class="rounded-xl border p-5 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500">Estado</p>
                    <p class="font-semibold capitalize dark:text-white">
                        {{ $enrollment->estado_inscripcion }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>