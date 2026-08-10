<div class="space-y-5">
    <div>
        <label for="student_id" class="mb-1 block text-sm font-medium dark:text-zinc-300">
            Estudiante
        </label>

        <select
            id="student_id"
            name="student_id"
            required
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >
            <option value="">Selecciona un estudiante</option>

            @foreach ($students as $student)
                <option
                    value="{{ $student->id }}"
                    @selected(
                        old('student_id', $enrollment?->student_id)
                        == $student->id
                    )
                >
                    {{ $student->name }}
                    {{ $student->apellidoPaterno }}
                    {{ $student->apellidoMaterno }}
                    — {{ $student->username }}
                </option>
            @endforeach
        </select>

        @error('student_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="school_group_id" class="mb-1 block text-sm font-medium dark:text-zinc-300">
            Grupo escolar
        </label>

        <select
            id="school_group_id"
            name="school_group_id"
            required
            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >
            <option value="">Selecciona un grupo</option>

            @foreach ($schoolGroups as $group)
                <option
                    value="{{ $group->id }}"
                    @selected(
                        old('school_group_id', $enrollment?->school_group_id)
                        == $group->id
                    )
                >
                    {{ $group->academicPeriod?->nombre_periodo }}
                    —
                    {{ $group->schoolGrade?->nombre_grado }}
                    {{ $group->nombre_grupo }}
                </option>
            @endforeach
        </select>

        @error('school_group_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="fecha_inscripcion" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                Fecha de inscripción
            </label>

            <input
                id="fecha_inscripcion"
                name="fecha_inscripcion"
                type="date"
                value="{{ old(
                    'fecha_inscripcion',
                    $enrollment?->fecha_inscripcion?->format('Y-m-d')
                        ?? now()->toDateString()
                ) }}"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            @error('fecha_inscripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="estado_inscripcion" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                Estado
            </label>

            <select
                id="estado_inscripcion"
                name="estado_inscripcion"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >
                @foreach ([
                    'activo' => 'Activo',
                    'baja' => 'Baja',
                    'completado' => 'Completado',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(
                            old(
                                'estado_inscripcion',
                                $enrollment?->estado_inscripcion ?? 'activo'
                            ) === $value
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @error('estado_inscripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a
            href="{{ route('enrollments.index') }}"
            wire:navigate
            class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold dark:border-zinc-600 dark:text-zinc-300"
        >
            Cancelar
        </a>

        <button
            type="submit"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white"
        >
            {{ $buttonText }}
        </button>
    </div>
</div>