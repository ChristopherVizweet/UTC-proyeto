<x-layouts::app :title="$user->name">
    <div class="p-6">
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    @if ($user->photo_path)
                        <img
                            src="{{ asset('storage/' . $user->photo_path) }}"
                            alt="Fotografía de {{ $user->name }}"
                            class="h-20 w-20 rounded-full object-cover"
                        >
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 text-xl font-bold text-blue-700">
                            {{ $user->initials() }}
                        </div>
                    @endif

                    <div>
                        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ $user->name }}
                            {{ $user->apellidoPaterno }}
                            {{ $user->apellidoMaterno }}
                        </h1>

                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold capitalize text-purple-700">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('users.index') }}"
                        wire:navigate
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-300"
                    >
                        Regresar
                    </a>

                    <a
                        href="{{ route('users.edit', $user) }}"
                        wire:navigate
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600"
                    >
                        Editar
                    </a>
                </div>
            </div>

            {{-- Información general --}}
            <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                    Información general
                </h2>

                <dl class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-sm text-zinc-500">Nombre de usuario</dt>
                        <dd class="font-semibold text-zinc-900 dark:text-white">
                            {{ $user->username ?? 'No registrado' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">Correo electrónico</dt>
                        <dd class="font-semibold text-zinc-900 dark:text-white">
                            {{ $user->email ?? 'No registrado' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">Fecha de nacimiento</dt>
                        <dd class="font-semibold text-zinc-900 dark:text-white">
                            {{ $user->birth_date?->format('d/m/Y') ?? 'No registrada' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">Edad</dt>
                        <dd class="font-semibold text-zinc-900 dark:text-white">
                            {{ $user->birth_date?->age ?? 'No registrada' }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Domicilio --}}
            @if ($user->address)
                <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                        Domicilio
                    </h2>

                    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-sm text-zinc-500">Calle y número</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $user->address->calle_usuario }}
                                {{ $user->address->exterior_usuario }}

                                @if ($user->address->interior_usuario)
                                    Int. {{ $user->address->interior_usuario }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Colonia</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $user->address->colonia_usuario }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Municipio o alcaldía</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $user->address->municipio_usuario }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Estado y C.P.</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $user->address->estado_usuario }},
                                {{ $user->address->cp_usuario }}
                            </p>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Información del estudiante --}}
            @if ($user->hasRole('estudiante') && $user->studentProfile)
                <section class="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-950/30">
                    <h2 class="mb-4 text-lg font-semibold text-blue-900 dark:text-blue-200">
                        Información del tutor
                    </h2>

                    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-sm text-zinc-500">Nombre</p>
                            <p class="font-semibold dark:text-white">
                                {{ $user->studentProfile->nombre_tutor }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Parentesco</p>
                            <p class="font-semibold dark:text-white">
                                {{ $user->studentProfile->parentesco_tutor }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Teléfono principal</p>
                            <p class="font-semibold dark:text-white">
                                {{ $user->studentProfile->telefono_tutor }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Teléfono secundario</p>
                            <p class="font-semibold dark:text-white">
                                {{ $user->studentProfile->telefonoSecundario_tutor ?? 'No registrado' }}
                            </p>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Información del docente --}}
            @if ($user->hasRole('docente') && $user->teacherProfile)
                <section class="rounded-xl border border-green-200 bg-green-50 p-6 dark:border-green-900 dark:bg-green-950/30">
                    <h2 class="mb-4 text-lg font-semibold text-green-900 dark:text-green-200">
                        Información profesional
                    </h2>

                    <div class="grid gap-5 md:grid-cols-3">
                        <div>
                            <p class="text-sm text-zinc-500">Teléfono</p>
                            <p class="font-semibold dark:text-white">
                                {{ $user->teacherProfile->telefono_profesor }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Nivel de estudios</p>
                            <p class="font-semibold dark:text-white">
                                {{ $user->teacherProfile->nivelEducativo_profesor }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">Cédula profesional</p>
                            <p class="font-semibold dark:text-white">
                                {{ $user->teacherProfile->cedula_profesor }}
                            </p>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-layouts::app>