<x-layouts::app :title="__('Editar asignación docente')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="mb-6 text-2xl font-bold dark:text-white">Editar asignación docente</h1>

            <form method="POST" action="{{ route('teaching-assignments.update', $teachingAssignment) }}">
                @csrf
                @method('PUT')
                @include('teaching-assignments._form', [
                    'buttonText' => 'Actualizar asignación',
                ])
            </form>
        </div>
    </div>
</x-layouts::app>
