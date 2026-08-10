<x-layouts::app :title="__('Nueva asignación docente')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="mb-6 text-2xl font-bold dark:text-white">Nueva asignación docente</h1>

            <form method="POST" action="{{ route('teaching-assignments.store') }}">
                @csrf
                @include('teaching-assignments._form', [
                    'teachingAssignment' => null,
                    'buttonText' => 'Crear asignación',
                ])
            </form>
        </div>
    </div>
</x-layouts::app>
