<x-layouts::app :title="__('Nueva actividad')">
    <div class="p-6"><div class="mx-auto max-w-4xl rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="mb-6 text-2xl font-bold dark:text-white">Nueva actividad</h1>
        <form method="POST" action="{{ route('activities.store') }}" enctype="multipart/form-data">
            @csrf
            @include('activities._form', ['buttonText' => 'Crear actividad'])
        </form>
    </div></div>
</x-layouts::app>
