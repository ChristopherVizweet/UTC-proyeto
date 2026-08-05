<x-layouts::app :title="__('Crear grupo escolar')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Crear grupo escolar
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Asigna el grupo a un grado y periodo académico.
                    </p>
                </div>

                <form method="POST" action="{{ route('school-groups.store') }}">
                    @csrf

                    @include('school-groups._form', [
                        'schoolGroup' => null,
                        'buttonText' => 'Guardar grupo',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>