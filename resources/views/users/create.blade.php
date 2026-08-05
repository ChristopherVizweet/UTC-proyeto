<x-layouts::app :title="__('Crear usuario')">
    <div class="p-6">
        <div class="mx-auto max-w-5xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Crear usuario
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Registra un estudiante, docente o administrador.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('users.store') }}"
                    enctype="multipart/form-data"
                >
                    @csrf

                    @include('users._form', [
                        'user' => null,
                        'buttonText' => 'Guardar usuario',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>