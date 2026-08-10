<x-layouts::app :title="__('Realizar entrega')">
    <div class="p-6"><div class="mx-auto max-w-4xl space-y-5">
        <div><p class="text-sm font-semibold text-blue-600">{{ $activity->teachingAssignment->subject->nombre_materia }}</p><h1 class="text-2xl font-bold dark:text-white">{{ $activity->titulo }}</h1><p class="text-sm text-zinc-500">Fecha límite: {{ $activity->fecha_limite?->format('d/m/Y H:i') ?? 'Sin límite' }}</p></div>
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900"><form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data">@csrf @include('submissions._form')</form></div>
    </div></div>
</x-layouts::app>
