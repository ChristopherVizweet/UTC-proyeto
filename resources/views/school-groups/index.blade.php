<x-layouts::app :title="__('Grupos escolares')">
    <div class="p-6">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Grupos escolares
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Administra los grupos de cada grado y periodo.
                    </p>
                </div>

                <a
                    href="{{ route('school-groups.create') }}"
                    wire:navigate
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Nuevo grupo
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
                        <thead class="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            <tr>
                                <th class="px-4 py-3">Periodo</th>
                                <th class="px-4 py-3">Grado</th>
                                <th class="px-4 py-3">Grupo</th>
                                <th class="px-4 py-3">Capacidad</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($schoolGroups as $group)
                                <tr class="text-zinc-700 dark:text-zinc-300">
                                    <td class="px-4 py-3">
                                        {{ $group->academicPeriod?->nombre_periodo ?? 'Sin periodo' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $group->schoolGrade?->nombre_grado ?? 'Sin grado' }}
                                    </td>

                                    <td class="px-4 py-3 font-semibold">
                                        {{ $group->nombre_grupo ?? 'Sin nombre' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $group->capacidad_grupo ?? 'No especificada' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($group->is_active)
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Activo
                                            </span>
                                        @else
                                            <span class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-semibold text-zinc-700">
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route('school-groups.show', $group) }}"
                                                wire:navigate
                                                class="rounded-md bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700"
                                            >
                                                Ver
                                            </a>

                                            <a
                                                href="{{ route('school-groups.edit', $group) }}"
                                                wire:navigate
                                                class="rounded-md bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('school-groups.destroy', $group) }}"
                                                onsubmit="return confirm('¿Deseas eliminar este grupo?')"
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
                                    <td
                                        colspan="6"
                                        class="px-4 py-8 text-center text-zinc-500"
                                    >
                                        No hay grupos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $schoolGroups->links() }}
        </div>
    </div>
</x-layouts::app>