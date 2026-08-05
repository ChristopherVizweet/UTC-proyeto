<div class="space-y-5">
    <div>
        <label
            for="nombre_grado"
            class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
        >
            Nombre del grado
        </label>

        <input
            id="nombre_grado"
            name="nombre_grado"
            type="text"
            value="{{ old('nombre_grado', $schoolGrade?->nombre_grado) }}"
            placeholder="Ejemplo: Primero"
            required
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >

        @error('nombre_grado')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label
            for="nivel_grado"
            class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
        >
            Nivel
        </label>

        <select
            id="nivel_grado"
            name="nivel_grado"
            required
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >
            <option value="">Selecciona un nivel</option>

            @foreach ([
                1 => '1 - Primero',
                2 => '2 - Segundo',
                3 => '3 - Tercero',
                4 => '4 - Cuarto',
                5 => '5 - Quinto',
                6 => '6 - Sexto',
            ] as $level => $label)
                <option
                    value="{{ $level }}"
                    @selected(old('nivel_grado', $schoolGrade?->nivel_grado) == $level)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('nivel_grado')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label
            for="descripcion_grado"
            class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
        >
            Descripción
        </label>

        <textarea
            id="descripcion_grado"
            name="descripcion_grado"
            rows="4"
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            placeholder="Descripción opcional del grado"
        >{{ old('descripcion_grado', $schoolGrade?->descripcion_grado) }}</textarea>

        @error('descripcion_grado')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a
            href="{{ route('school-grades.index') }}"
            wire:navigate
            class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-300"
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