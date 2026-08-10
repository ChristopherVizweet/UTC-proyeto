<x-layouts::app :title="__('Detalle de asignación docente')">
    <div class="p-6">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold dark:text-white">{{ $teachingAssignment->teacher->name }} {{ $teachingAssignment->teacher->apellidoPaterno }}</h1>
                    <p class="text-sm text-zinc-500">Detalle de asignación docente</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('teaching-assignments.index') }}" wire:navigate class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-white">Regresar</a>
                    <a href="{{ route('teaching-assignments.edit', $teachingAssignment) }}" wire:navigate class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white">Editar</a>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-4">
                <div class="rounded-xl border p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Periodo</p><p class="font-semibold dark:text-white">{{ $teachingAssignment->schoolGroup->academicPeriod?->nombre_periodo }}</p></div>
                <div class="rounded-xl border p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Grado y grupo</p><p class="font-semibold dark:text-white">{{ $teachingAssignment->schoolGroup->schoolGrade?->nombre_grado }} — {{ $teachingAssignment->schoolGroup->nombre_grupo }}</p></div>
                <div class="rounded-xl border p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Materia</p><p class="font-semibold dark:text-white">{{ $teachingAssignment->subject->nombre_materia }}</p></div>
                <div class="rounded-xl border p-5 dark:border-zinc-700"><p class="text-sm text-zinc-500">Estado</p><p class="font-semibold dark:text-white">{{ $teachingAssignment->is_active ? 'Activa' : 'Inactiva' }}</p></div>
            </div>
        </div>
    </div>
</x-layouts::app>
