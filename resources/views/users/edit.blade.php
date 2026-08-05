<x-layouts::app :title="__('Editar usuario')">
    <div class="p-6">
        <div class="mx-auto max-w-5xl">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Editar usuario
                    </h1>

                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Modifica la información de {{ $user->name }}.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('users.update', $user) }}"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @method('PUT')

                    @include('users._form', [
                        'user' => $user,
                        'buttonText' => 'Actualizar usuario',
                    ])
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>