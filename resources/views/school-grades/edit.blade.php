<x-layouts::app :title="__('Editar grado escolar')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Editar grado escolar
                    </h1>
                </div>

                <form
                    method="POST"
                    action="{{ route('school-grades.update', $schoolGrade) }}"
                >
                    @csrf
                    @method('PUT')

                    @include('school-grades._form', [
                        'schoolGrade' => $schoolGrade,
                        'buttonText' => 'Actualizar grado',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>