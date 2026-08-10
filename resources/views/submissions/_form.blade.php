<div class="space-y-5">
    @if (! $submission)
        <input type="hidden" name="activity_id" value="{{ $activity->id }}">
    @endif

    <div>
        <label for="respuesta" class="mb-1 block text-sm font-medium dark:text-zinc-300">Respuesta escrita</label>
        <textarea id="respuesta" name="respuesta" rows="10" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" placeholder="Escribe aquí tu respuesta...">{{ old('respuesta', $submission?->respuesta) }}</textarea>
        @error('respuesta')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="archivo" class="mb-1 block text-sm font-medium dark:text-zinc-300">Adjuntar archivo</label>
        <input id="archivo" name="archivo" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.txt" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
        @if ($submission?->archivo_path)<p class="mt-1 text-xs text-zinc-500">Archivo actual: {{ basename($submission->archivo_path) }}</p>@endif
        @error('archivo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('activity_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('action')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
        <a href="{{ route('activities.show', $activity) }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold dark:border-zinc-600 dark:text-zinc-300">Cancelar</a>
        <button type="submit" name="action" value="draft" class="rounded-lg bg-zinc-700 px-4 py-2 text-sm font-semibold text-white">Guardar borrador</button>
        <button type="submit" name="action" value="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white">Entregar actividad</button>
    </div>
</div>
