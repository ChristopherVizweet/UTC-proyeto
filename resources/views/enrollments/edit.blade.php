<x-layouts::app :title="__('Editar inscripción')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="mb-6 text-2xl font-bold dark:text-white">
                Editar inscripción
            </h1>

            <form
                method="POST"
                action="{{ route('enrollments.update', $enrollment) }}"
            >
                @csrf
                @method('PUT')

                @include('enrollments._form', [
                    'enrollment' => $enrollment,
                    'buttonText' => 'Actualizar inscripción',
                ])
            </form>
        </div>
    </div>
</x-layouts::app>