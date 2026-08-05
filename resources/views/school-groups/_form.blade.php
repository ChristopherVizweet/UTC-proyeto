<div class="space-y-5">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label
                for="academic_period_id"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Periodo académico
            </label>

            <select
                id="academic_period_id"
                name="academic_period_id"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >
                <option value="">Selecciona un periodo</option>

                @foreach ($academicPeriods as $period)
                    <option
                        value="{{ $period->id }}"
                        @selected(
                            old(
                                'academic_period_id',
                                $schoolGroup?->academic_period_id
                            ) == $period->id
                        )
                    >
                        {{ $period->nombre_periodo ?? 'Periodo sin nombre' }}
                        {{ $period->estado_periodo ? '(Activo)' : '' }}
                    </option>
                @endforeach
            </select>

            @error('academic_period_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="school_grade_id"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Grado escolar
            </label>

            <select
                id="school_grade_id"
                name="school_grade_id"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >
                <option value="">Selecciona un grado</option>

                @foreach ($schoolGrades as $grade)
                    <option
                        value="{{ $grade->id }}"
                        @selected(
                            old(
                                'school_grade_id',
                                $schoolGroup?->school_grade_id
                            ) == $grade->id
                        )
                    >
                        {{ $grade->nombre_grado ?? 'Grado sin nombre' }}
                    </option>
                @endforeach
            </select>

            @error('school_grade_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label
                for="nombre_grupo"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Nombre del grupo
            </label>

            <input
                id="nombre_grupo"
                name="nombre_grupo"
                type="text"
                maxlength="10"
                value="{{ old('nombre_grupo', $schoolGroup?->nombre_grupo) }}"
                placeholder="Ejemplo: A"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 uppercase text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            @error('nombre_grupo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="capacidad_grupo"
                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
            >
                Capacidad
            </label>

            <input
                id="capacidad_grupo"
                name="capacidad_grupo"
                type="number"
                min="1"
                max="100"
                value="{{ old('capacidad_grupo', $schoolGroup?->capacidad_grupo) }}"
                placeholder="Ejemplo: 30"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            @error('capacidad_grupo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label
            for="descripcion_grupo"
            class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
        >
            Descripción
        </label>

        <textarea
            id="descripcion_grupo"
            name="descripcion_grupo"
            rows="4"
            placeholder="Descripción opcional del grupo"
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >{{ old('descripcion_grupo', $schoolGroup?->descripcion_grupo) }}</textarea>

        @error('descripcion_grupo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-3">
        <input
            name="is_active"
            type="checkbox"
            value="1"
            @checked(old('is_active', $schoolGroup?->is_active ?? true))
            class="h-4 w-4 rounded border-zinc-300 text-blue-600"
        >

        <span class="text-sm text-zinc-700 dark:text-zinc-300">
            Grupo activo
        </span>
    </label>

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a
            href="{{ route('school-groups.index') }}"
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