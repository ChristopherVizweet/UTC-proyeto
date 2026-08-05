<div
    x-data="{
        role: @js(old('role', $user?->getRoleNames()->first() ?? 'estudiante'))
    }"
    class="space-y-8"
>
    {{-- Tipo de usuario --}}
    <section class="space-y-4">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
            Tipo de usuario
        </h2>

        <div>
            <label
                for="role"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Rol
            </label>

            <select
                id="role"
                name="role"
                x-model="role"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >
                <option value="estudiante">Estudiante</option>
                <option value="docente">Docente</option>
                <option value="administrador">Administrador</option>
            </select>

            @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </section>

    {{-- Información personal --}}
    <section class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
            Información personal
        </h2>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="name" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Nombre
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $user?->name) }}"
                    required
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="apellidoPaterno" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Apellido paterno
                </label>

                <input
                    id="apellidoPaterno"
                    name="apellidoPaterno"
                    type="text"
                    value="{{ old('apellidoPaterno', $user?->apellidoPaterno) }}"
                    required
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('apellidoPaterno')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="apellidoMaterno" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Apellido materno
                </label>

                <input
                    id="apellidoMaterno"
                    name="apellidoMaterno"
                    type="text"
                    value="{{ old('apellidoMaterno', $user?->apellidoMaterno) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('apellidoMaterno')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="birth_date" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Fecha de nacimiento
                </label>

                <input
                    id="birth_date"
                    name="birth_date"
                    type="date"
                    value="{{ old('birth_date', $user?->birth_date?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('birth_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="photo" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Fotografía
                </label>

                <input
                    id="photo"
                    name="photo"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('photo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if ($user?->photo_path)
                    <img
                        src="{{ asset('storage/' . $user->photo_path) }}"
                        alt="Fotografía actual"
                        class="mt-3 h-20 w-20 rounded-full object-cover"
                    >
                @endif
            </div>
        </div>
    </section>

    {{-- Datos de acceso --}}
    <section class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
            Datos de acceso
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="username" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Nombre de usuario
                </label>

                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username', $user?->username) }}"
                    required
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('username')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Correo electrónico
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $user?->email) }}"
                    :required="role === 'docente' || role === 'administrador'"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                <p
                    x-show="role === 'estudiante'"
                    class="mt-1 text-xs text-zinc-500"
                >
                    El correo es opcional para estudiantes.
                </p>

                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="password" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Contraseña
                </label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    @required(! $user)
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @if ($user)
                    <p class="mt-1 text-xs text-zinc-500">
                        Déjala vacía para conservar la contraseña actual.
                    </p>
                @endif

                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Confirmar contraseña
                </label>

                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    @required(! $user)
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>
        </div>
    </section>

    {{-- Domicilio --}}
    <section
        x-show="role === 'estudiante' || role === 'docente'"
        x-cloak
        class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
            Domicilio
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="calle_usuario" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Calle
                </label>

                <input
                    id="calle_usuario"
                    name="calle_usuario"
                    type="text"
                    value="{{ old('calle_usuario', $user?->address?->calle_usuario) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('calle_usuario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="colonia_usuario" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Colonia
                </label>

                <input
                    id="colonia_usuario"
                    name="colonia_usuario"
                    type="text"
                    value="{{ old('colonia_usuario', $user?->address?->colonia_usuario) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('colonia_usuario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="exterior_usuario" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Número exterior
                </label>

                <input
                    id="exterior_usuario"
                    name="exterior_usuario"
                    type="text"
                    value="{{ old('exterior_usuario', $user?->address?->exterior_usuario) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('exterior_usuario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="interior_usuario" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Número interior
                </label>

                <input
                    id="interior_usuario"
                    name="interior_usuario"
                    type="text"
                    value="{{ old('interior_usuario', $user?->address?->interior_usuario) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="municipio_usuario" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Alcaldía o municipio
                </label>

                <input
                    id="municipio_usuario"
                    name="municipio_usuario"
                    type="text"
                    value="{{ old('municipio_usuario', $user?->address?->municipio_usuario) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('municipio_usuario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="estado_usuario" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Estado
                </label>

                <input
                    id="estado_usuario"
                    name="estado_usuario"
                    type="text"
                    value="{{ old('estado_usuario', $user?->address?->estado_usuario) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('estado_usuario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="cp_usuario" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Código postal
                </label>

                <input
                    id="cp_usuario"
                    name="cp_usuario"
                    type="text"
                    inputmode="numeric"
                    maxlength="5"
                    value="{{ old('cp_usuario', $user?->address?->cp_usuario) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('cp_usuario')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    {{-- Perfil del estudiante --}}
    <section
        x-show="role === 'estudiante'"
        x-cloak
        class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
            Información del tutor
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="nombre_tutor" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Nombre del tutor
                </label>

                <input
                    id="nombre_tutor"
                    name="nombre_tutor"
                    type="text"
                    value="{{ old('nombre_tutor', $user?->studentProfile?->nombre_tutor) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('nombre_tutor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="parentesco_tutor" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Parentesco
                </label>

                <input
                    id="parentesco_tutor"
                    name="parentesco_tutor"
                    type="text"
                    value="{{ old('parentesco_tutor', $user?->studentProfile?->parentesco_tutor) }}"
                    placeholder="Ejemplo: Madre"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('parentesco_tutor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="telefono_tutor" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Teléfono principal
                </label>

                <input
                    id="telefono_tutor"
                    name="telefono_tutor"
                    type="tel"
                    value="{{ old('telefono_tutor', $user?->studentProfile?->telefono_tutor) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('telefono_tutor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="telefonoSecundario_tutor" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Teléfono secundario
                </label>

                <input
                    id="telefonoSecundario_tutor"
                    name="telefonoSecundario_tutor"
                    type="tel"
                    value="{{ old('telefonoSecundario_tutor', $user?->studentProfile?->telefonoSecundario_tutor) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>
        </div>
    </section>

    {{-- Perfil del docente --}}
    <section
        x-show="role === 'docente'"
        x-cloak
        class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
            Información profesional
        </h2>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="telefono_profesor" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Teléfono
                </label>

                <input
                    id="telefono_profesor"
                    name="telefono_profesor"
                    type="tel"
                    value="{{ old('telefono_profesor', $user?->teacherProfile?->telefono_profesor) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('telefono_profesor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nivelEducativo_profesor" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Nivel de estudios
                </label>

                <input
                    id="nivelEducativo_profesor"
                    name="nivelEducativo_profesor"
                    type="text"
                    value="{{ old('nivelEducativo_profesor', $user?->teacherProfile?->nivelEducativo_profesor) }}"
                    placeholder="Ejemplo: Licenciatura"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('nivelEducativo_profesor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="cedula_profesor" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                    Cédula profesional
                </label>

                <input
                    id="cedula_profesor"
                    name="cedula_profesor"
                    type="text"
                    value="{{ old('cedula_profesor', $user?->teacherProfile?->cedula_profesor) }}"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >

                @error('cedula_profesor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-6 dark:border-zinc-700">
        <a
            href="{{ route('users.index') }}"
            wire:navigate
            class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-300"
        >
            Cancelar
        </a>

        <button
            type="submit"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
        >
            {{ $buttonText }}
        </button>
    </div>
</div>