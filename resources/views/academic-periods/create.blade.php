<x-layouts::app :title="__('Crear periodo académico')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Crear periodo académico
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Registra un nuevo ciclo escolar.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('academic-periods.store') }}"
                >
                    @csrf

                    @include('academic-periods._form', [
                        'academicPeriod' => null,
                        'buttonText' => 'Guardar periodo',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>