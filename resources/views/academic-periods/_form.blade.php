<div class="space-y-5">
    <div>
        <label
            for="nombre_periodo"
            class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
        >
            Nombre del periodo
        </label>

        <input
            id="nombre_periodo"
            name="nombre_periodo"
            type="text"
            value="{{ old('nombre_periodo', $academicPeriod?->nombre_periodo) }}"
            placeholder="Ejemplo: 2026-2027"
            required
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >

        @error('nombre_periodo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label
                for="fecha_inicio"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Fecha de inicio
            </label>

            <input
                id="fecha_inicio"
                name="fecha_inicio"
                type="date"
                value="{{ old('fecha_inicio', $academicPeriod?->fecha_inicio?->format('Y-m-d')) }}"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            @error('fecha_inicio')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="fecha_fin"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Fecha de término
            </label>

            <input
                id="fecha_fin"
                name="fecha_fin"
                type="date"
                value="{{ old('fecha_fin', $academicPeriod?->fecha_fin?->format('Y-m-d')) }}"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            @error('fecha_fin')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <label class="flex items-center gap-3">
        <input
            name="estado_periodo"
            type="checkbox"
            value="1"
            @checked(old('estado_periodo', $academicPeriod?->estado_periodo ?? false))
            class="h-4 w-4 rounded border-zinc-300 text-blue-600"
        >

        <span class="text-sm text-zinc-700 dark:text-zinc-300">
            Establecer como periodo activo
        </span>
    </label>

    @error('estado_periodo')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a
            href="{{ route('academic-periods.index') }}"
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