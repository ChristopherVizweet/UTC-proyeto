<div class="space-y-5">
    <div>
        <label for="teacher_id" class="mb-1 block text-sm font-medium dark:text-zinc-300">
            Docente
        </label>

        <select id="teacher_id" name="teacher_id" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            <option value="">Selecciona un docente</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $teachingAssignment?->teacher_id) == $teacher->id)>
                    {{ $teacher->name }} {{ $teacher->apellidoPaterno }} {{ $teacher->apellidoMaterno }} — {{ $teacher->username }}
                </option>
            @endforeach
        </select>

        @error('teacher_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="school_group_id" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                Grupo escolar
            </label>

            <select id="school_group_id" name="school_group_id" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                <option value="">Selecciona un grupo</option>
                @foreach ($schoolGroups as $group)
                    <option value="{{ $group->id }}" @selected(old('school_group_id', $teachingAssignment?->school_group_id) == $group->id)>
                        {{ $group->academicPeriod?->nombre_periodo }} — {{ $group->schoolGrade?->nombre_grado }} {{ $group->nombre_grupo }}{{ $group->is_active ? '' : ' (inactivo)' }}
                    </option>
                @endforeach
            </select>

            @error('school_group_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="subject_id" class="mb-1 block text-sm font-medium dark:text-zinc-300">
                Materia
            </label>

            <select id="subject_id" name="subject_id" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                <option value="">Selecciona una materia</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id', $teachingAssignment?->subject_id) == $subject->id)>
                        {{ $subject->nombre_materia }} — {{ $subject->code }}{{ $subject->is_active ? '' : ' (inactiva)' }}
                    </option>
                @endforeach
            </select>

            @error('subject_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="is_active" class="mb-1 block text-sm font-medium dark:text-zinc-300">
            Estado
        </label>

        <select id="is_active" name="is_active" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            <option value="1" @selected((string) old('is_active', (int) ($teachingAssignment?->is_active ?? true)) === '1')>Activa</option>
            <option value="0" @selected((string) old('is_active', (int) ($teachingAssignment?->is_active ?? true)) === '0')>Inactiva</option>
        </select>

        @error('is_active')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a href="{{ route('teaching-assignments.index') }}" wire:navigate class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold dark:border-zinc-600 dark:text-zinc-300">
            Cancelar
        </a>

        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
            {{ $buttonText }}
        </button>
    </div>
</div>
