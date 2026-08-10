<x-layouts::app :title="__('Asignaciones docentes')">
    <div class="p-6">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold dark:text-white">Asignaciones docentes</h1>
                    <p class="text-sm text-zinc-500">Asigna un docente a cada materia de un grupo escolar.</p>
                </div>

                <a href="{{ route('teaching-assignments.create') }}" wire:navigate class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    Nueva asignación
                </a>
            </div>

            @if (session('success'))
                <div class="rounded-lg border border-green-300 bg-green-100 p-4 text-green-800">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-100 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3">Docente</th>
                                <th class="px-4 py-3">Periodo</th>
                                <th class="px-4 py-3">Grado y grupo</th>
                                <th class="px-4 py-3">Materia</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($teachingAssignments as $assignment)
                                <tr class="text-zinc-700 dark:text-zinc-300">
                                    <td class="px-4 py-3 font-semibold">{{ $assignment->teacher?->name }} {{ $assignment->teacher?->apellidoPaterno }} {{ $assignment->teacher?->apellidoMaterno }}</td>
                                    <td class="px-4 py-3">{{ $assignment->schoolGroup?->academicPeriod?->nombre_periodo }}</td>
                                    <td class="px-4 py-3">{{ $assignment->schoolGroup?->schoolGrade?->nombre_grado }} — Grupo {{ $assignment->schoolGroup?->nombre_grupo }}</td>
                                    <td class="px-4 py-3">{{ $assignment->subject?->nombre_materia }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $assignment->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $assignment->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('teaching-assignments.show', $assignment) }}" wire:navigate class="rounded-md bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700">Ver</a>
                                            <a href="{{ route('teaching-assignments.edit', $assignment) }}" wire:navigate class="rounded-md bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700">Editar</a>
                                            <form method="POST" action="{{ route('teaching-assignments.destroy', $assignment) }}" onsubmit="return confirm('¿Deseas eliminar esta asignación docente?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-zinc-500">No hay asignaciones docentes registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $teachingAssignments->links() }}
        </div>
    </div>
</x-layouts::app>
