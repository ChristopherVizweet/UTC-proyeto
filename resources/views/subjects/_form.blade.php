<div class="space-y-5">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label
                for="nombre_materia"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Nombre de la materia
            </label>

            <input
                id="nombre_materia"
                name="nombre_materia"
                type="text"
                value="{{ old('nombre_materia', $subject?->nombre_materia) }}"
                placeholder="Ejemplo: Matemáticas"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            @error('nombre_materia')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="code"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Código
            </label>

            <input
                id="code"
                name="code"
                type="text"
                maxlength="20"
                value="{{ old('code', $subject?->code) }}"
                placeholder="Ejemplo: MAT"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 uppercase text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label
            for="descripcion_materia"
            class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
        >
            Descripción
        </label>

        <textarea
            id="descripcion_materia"
            name="descripcion_materia"
            rows="4"
            placeholder="Descripción opcional"
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >{{ old('descripcion_materia', $subject?->descripcion_materia) }}</textarea>

        @error('descripcion_materia')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-3">
        <input
            name="is_active"
            type="checkbox"
            value="1"
            @checked(old('is_active', $subject?->is_active ?? true))
            class="h-4 w-4 rounded border-zinc-300 text-blue-600"
        >

        <span class="text-sm text-zinc-700 dark:text-zinc-300">
            Materia activa
        </span>
    </label>

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a
            href="{{ route('subjects.index') }}"
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