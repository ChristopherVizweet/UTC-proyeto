<x-layouts::app :title="__('Periodos académicos')">
    <div class="p-6">
        <div class="mx-auto max-w-7xl space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Periodos académicos
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Administra los ciclos escolares de la plataforma.
                    </p>
                </div>

                <a
                    href="{{ route('academic-periods.create') }}"
                    wire:navigate
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Nuevo periodo
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
                                <th class="px-4 py-3">Fecha de inicio</th>
                                <th class="px-4 py-3">Fecha de término</th>
                                <th class="px-4 py-3">Grupos</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($academicPeriods as $period)
                                <tr class="text-zinc-700 dark:text-zinc-300">
                                    <td class="px-4 py-3 font-medium">
                                        {{ $period->nombre_periodo ?? 'Sin nombre' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $period->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $period->fecha_fin?->format('d/m/Y') ?? 'Sin fecha' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $period->school_groups_count }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($period->estado_periodo)
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
                                                href="{{ route('academic-periods.show', $period) }}"
                                                wire:navigate
                                                class="rounded-md bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-200"
                                            >
                                                Ver
                                            </a>

                                            <a
                                                href="{{ route('academic-periods.edit', $period) }}"
                                                wire:navigate
                                                class="rounded-md bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('academic-periods.destroy', $period) }}"
                                                onsubmit="return confirm('¿Deseas eliminar este periodo académico?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-md bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-200"
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
                                        No hay periodos académicos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $academicPeriods->links() }}
        </div>
    </div>
</x-layouts::app>