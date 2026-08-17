<x-layouts::app :title="__('Actividades')">
    <div class="p-6"><div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold dark:text-white">Actividades</h1><p class="text-sm text-zinc-500">Consulta tareas, archivos y próximas actividades.</p></div>
            @can('create', App\Models\Activity::class)<a href="{{ route('activities.create') }}" wire:navigate class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Nueva actividad</a>@endcan
        </div>

        @if (session('success'))<div class="rounded-lg border border-green-300 bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif

        @unlessrole('estudiante')
            <form method="GET" class="grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 md:grid-cols-5 dark:border-zinc-700 dark:bg-zinc-900">
                <input name="search" value="{{ request('search') }}" placeholder="Buscar por título" class="rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                <select name="school_group_id" class="rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"><option value="">Todos los grupos</option>@foreach ($schoolGroups as $group)<option value="{{ $group->id }}" @selected(request('school_group_id') == $group->id)>{{ $group->nombre_grupo }}</option>@endforeach</select>
                <select name="subject_id" class="rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"><option value="">Todas las materias</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->nombre_materia }}</option>@endforeach</select>
                <select name="estado" class="rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"><option value="">Todos los estados</option>@foreach (['borrador', 'publicada', 'cerrada'] as $status)<option value="{{ $status }}" @selected(request('estado') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                <button class="rounded-lg bg-zinc-800 px-4 py-2 font-semibold text-white">Filtrar</button>
            </form>
        @endunlessrole

        @role('estudiante')
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($activities as $activity)
                    @php
                        $submission = $activity->submissions->first();
                        $visualStatus = $submission?->estado ?? ($activity->fecha_limite?->isPast() ? 'vencida' : 'pendiente');
                        $statusClass = match ($visualStatus) {
                            'revisada' => 'bg-violet-100 text-violet-700',
                            'entregada' => 'bg-green-100 text-green-700',
                            'entregada_tarde' => 'bg-amber-100 text-amber-700',
                            'vencida' => 'bg-red-100 text-red-700',
                            default => 'bg-sky-100 text-sky-700',
                        };
                    @endphp
                    <article class="space-y-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase text-blue-600">{{ $activity->teachingAssignment->subject->nombre_materia }}</p><h2 class="text-lg font-bold dark:text-white">{{ $activity->titulo }}</h2></div><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($visualStatus)) }}</span></div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ Illuminate\Support\Str::limit($activity->instrucciones ?? $activity->descripcion, 120) }}</p>
                        <div class="text-sm text-zinc-500"><p>Grupo {{ $activity->teachingAssignment->schoolGroup->nombre_grupo }}</p><p>Límite: {{ $activity->fecha_limite?->format('d/m/Y H:i') ?? 'Sin límite' }}</p><p>{{ $activity->puntaje_maximo }} puntos</p></div>
                        <div class="flex gap-2"><a href="{{ route('activities.show', $activity) }}" wire:navigate class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white">Ver actividad</a>@if ($activity->tipo === 'relacion_columnas')<a href="{{ route('activities.play', $activity) }}" wire:navigate class="rounded-lg bg-violet-600 px-3 py-2 text-sm font-semibold text-white">Jugar</a>@elseif ($activity->tipo === 'sopa_letras')<a href="{{ route('activities.word-search.play', $activity) }}" wire:navigate class="rounded-lg bg-violet-600 px-3 py-2 text-sm font-semibold text-white">Jugar</a>@elseif ($activity->tipo === 'secuencia')<a href="{{ route('activities.numeric-sequence.play', $activity) }}" wire:navigate class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">Resolver</a>@elseif ($activity->tipo === 'crucigrama')<a href="{{ route('activities.crossword.play', $activity) }}" wire:navigate class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white">Resolver</a>@elseif ($activity->tipo === 'hay_ahi_ay')<a href="{{ route('activities.hay-ahi-ay.play', $activity) }}" wire:navigate class="rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white">Resolver</a>@elseif ($activity->tipo === 'memorama')<a href="{{ route('activities.memory.play', $activity) }}" wire:navigate class="rounded-lg bg-fuchsia-600 px-3 py-2 text-sm font-semibold text-white">Jugar</a>@elseif ($submission && $submission->estado !== 'revisada')<a href="{{ route('submissions.edit', $submission) }}" wire:navigate class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white">Continuar entrega</a>@elseif (! $submission && $visualStatus !== 'vencida')<a href="{{ route('submissions.create', ['activity_id' => $activity]) }}" wire:navigate class="rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white">Entregar</a>@endif</div>
                    </article>
                @empty <p class="text-zinc-500">No tienes actividades disponibles.</p> @endforelse
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"><table class="w-full text-left text-sm"><thead class="bg-zinc-100 dark:bg-zinc-800"><tr><th class="px-4 py-3">Título</th><th class="px-4 py-3">Docente</th><th class="px-4 py-3">Materia y grupo</th><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Límite</th><th class="px-4 py-3">Puntaje</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3 text-right">Acciones</th></tr></thead><tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($activities as $activity)<tr class="dark:text-zinc-300"><td class="px-4 py-3 font-semibold">{{ $activity->titulo }}</td><td class="px-4 py-3">{{ $activity->teachingAssignment->teacher->name }}</td><td class="px-4 py-3">{{ $activity->teachingAssignment->subject->nombre_materia }} — {{ $activity->teachingAssignment->schoolGroup->schoolGrade?->nombre_grado }} {{ $activity->teachingAssignment->schoolGroup->nombre_grupo }}</td><td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $activity->tipo)) }}</td><td class="px-4 py-3">{{ $activity->fecha_limite?->format('d/m/Y H:i') ?? 'Sin límite' }}</td><td class="px-4 py-3">{{ $activity->puntaje_maximo }}</td><td class="px-4 py-3">{{ ucfirst($activity->estado) }}</td><td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('activities.show', $activity) }}" class="text-sky-600">Ver</a>@can('update', $activity)<a href="{{ route('activities.edit', $activity) }}" class="text-amber-600">Editar</a>@endcan @can('delete', $activity)<form method="POST" action="{{ route('activities.destroy', $activity) }}" onsubmit="return confirm('¿Deseas eliminar esta actividad y sus entregas?')">@csrf @method('DELETE')<button class="text-red-600">Eliminar</button></form>@endcan</div></td></tr>
                @empty <tr><td colspan="8" class="px-4 py-8 text-center text-zinc-500">No hay actividades registradas.</td></tr>@endforelse
            </tbody></table></div>
        @endrole
        {{ $activities->links() }}
    </div></div>
</x-layouts::app>
