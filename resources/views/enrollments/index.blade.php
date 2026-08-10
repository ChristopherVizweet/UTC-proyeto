<x-layouts::app :title="__('Inscripciones')">
    <div class="p-6">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold dark:text-white">
                        Inscripciones
                    </h1>

                    <p class="text-sm text-zinc-500">
                        Asigna estudiantes a sus grupos escolares.
                    </p>
                </div>

                <a
                    href="{{ route('enrollments.create') }}"
                    wire:navigate
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    Nueva inscripción
                </a>
            </div>

            @if (session('success'))
                <div class="rounded-lg border border-green-300 bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-100 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3">Estudiante</th>
                                <th class="px-4 py-3">Periodo</th>
                                <th class="px-4 py-3">Grado y grupo</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($enrollments as $enrollment)
                                <tr class="text-zinc-700 dark:text-zinc-300">
                                    <td class="px-4 py-3 font-semibold">
                                        {{ $enrollment->student?->name }}
                                        {{ $enrollment->student?->apellidoPaterno }}
                                        {{ $enrollment->student?->apellidoMaterno }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $enrollment->schoolGroup?->academicPeriod?->nombre_periodo }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $enrollment->schoolGroup?->schoolGrade?->nombre_grado }}
                                        —
                                        Grupo {{ $enrollment->schoolGroup?->nombre_grupo }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $enrollment->fecha_inscripcion?->format('d/m/Y') }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @php
                                            $statusClasses = match ($enrollment->estado_inscripcion) {
                                                'activo' => 'bg-green-100 text-green-700',
                                                'completado' => 'bg-blue-100 text-blue-700',
                                                default => 'bg-red-100 text-red-700',
                                            };
                                        @endphp

                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                            {{ ucfirst($enrollment->estado_inscripcion) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route('enrollments.show', $enrollment) }}"
                                                wire:navigate
                                                class="rounded-md bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700"
                                            >
                                                Ver
                                            </a>

                                            <a
                                                href="{{ route('enrollments.edit', $enrollment) }}"
                                                wire:navigate
                                                class="rounded-md bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('enrollments.destroy', $enrollment) }}"
                                                onsubmit="return confirm('¿Deseas eliminar esta inscripción?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-md bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                                        No hay inscripciones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $enrollments->links() }}
        </div>
    </div>
</x-layouts::app>