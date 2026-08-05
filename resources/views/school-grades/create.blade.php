<x-layouts::app :title="__('Crear grado escolar')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Crear grado escolar
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Registra un nuevo grado de primaria.
                    </p>
                </div>

                <form method="POST" action="{{ route('school-grades.store') }}">
                    @csrf

                    @include('school-grades._form', [
                        'schoolGrade' => null,
                        'buttonText' => 'Guardar grado',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>