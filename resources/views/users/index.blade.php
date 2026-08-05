<x-layouts::app :title="__('Usuarios')">
    <div class="p-6">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Usuarios
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Administra estudiantes, docentes y administradores.
                    </p>
                </div>

                <a
                    href="{{ route('users.create') }}"
                    wire:navigate
                    class="rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Nuevo usuario
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

            <form
                method="GET"
                action="{{ route('users.index') }}"
                class="grid gap-4 rounded-xl border border-zinc-200 bg-white p-4 md:grid-cols-[1fr_220px_auto] dark:border-zinc-700 dark:bg-zinc-900"
            >
                <input
                    name="search"
                    type="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por nombre, usuario o correo"
                    class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                <select
                    name="role"
                    class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">Todos los roles</option>

                    <option
                        value="administrador"
                        @selected(request('role') === 'administrador')
                    >
                        Administradores
                    </option>

                    <option
                        value="docente"
                        @selected(request('role') === 'docente')
                    >
                        Docentes
                    </option>

                    <option
                        value="estudiante"
                        @selected(request('role') === 'estudiante')
                    >
                        Estudiantes
                    </option>
                </select>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Buscar
                    </button>

                    <a
                        href="{{ route('users.index') }}"
                        wire:navigate
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-300"
                    >
                        Limpiar
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            <tr>
                                <th class="px-4 py-3">Usuario</th>
                                <th class="px-4 py-3">Acceso</th>
                                <th class="px-4 py-3">Rol</th>
                                <th class="px-4 py-3">Edad</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($users as $user)
                                <tr class="text-zinc-700 dark:text-zinc-300">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            @if ($user->photo_path)
                                                <img
                                                    src="{{ asset('storage/' . $user->photo_path) }}"
                                                    alt="Fotografía de {{ $user->name }}"
                                                    class="h-10 w-10 rounded-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-700">
                                                    {{ $user->initials() }}
                                                </div>
                                            @endif

                                            <div>
                                                <p class="font-semibold text-zinc-900 dark:text-white">
                                                    {{ $user->name }}
                                                    {{ $user->apellidoPaterno }}
                                                    {{ $user->apellidoMaterno }}
                                                </p>

                                                <p class="text-xs text-zinc-500">
                                                    ID: {{ $user->id }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{ $user->username ?? 'Sin usuario' }}
                                        </p>

                                        <p class="text-xs text-zinc-500">
                                            {{ $user->email ?? 'Sin correo' }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-3">
                                        @forelse ($user->roles as $role)
                                            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold capitalize text-purple-700">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-red-600">
                                                Sin rol
                                            </span>
                                        @endforelse
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $user->birth_date?->age ?? 'No registrada' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route('users.show', $user) }}"
                                                wire:navigate
                                                class="rounded-md bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700"
                                            >
                                                Ver
                                            </a>

                                            <a
                                                href="{{ route('users.edit', $user) }}"
                                                wire:navigate
                                                class="rounded-md bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700"
                                            >
                                                Editar
                                            </a>

                                            @if (! $user->is(auth()->user()))
                                                <form
                                                    method="POST"
                                                    action="{{ route('users.destroy', $user) }}"
                                                    onsubmit="return confirm('¿Deseas eliminar este usuario?')"
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
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="px-4 py-8 text-center text-zinc-500"
                                    >
                                        No se encontraron usuarios.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-layouts::app>