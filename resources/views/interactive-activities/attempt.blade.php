<x-layouts::app :title="__('Relacionar columnas')">
    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-5xl space-y-6">
            <div><p class="text-sm font-semibold text-blue-600">Intento {{ $attempt->attempt_number }}</p><h1 class="text-2xl font-bold dark:text-white">{{ $attempt->activity->titulo }}</h1><p class="text-zinc-500">Selecciona una respuesta distinta para cada concepto. Puedes corregir todo antes de enviar.</p></div>

            @if ($errors->any())<div class="rounded-lg border border-red-300 bg-red-100 p-4 text-red-800"><p class="font-semibold">Revisa tus relaciones:</p><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('activities.attempts.submit', $attempt) }}" class="space-y-5">
                @csrf
                <div class="space-y-4">
                    @foreach ($display['left'] as $leftItem)
                        <div class="grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 md:grid-cols-[1fr_auto_1fr] md:items-center dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="rounded-lg bg-blue-50 p-4 font-bold text-blue-900 dark:bg-blue-950/50 dark:text-blue-100">{{ $leftItem['label'] }}</div>
                            <span class="hidden text-xl text-zinc-400 md:block" aria-hidden="true">→</span>
                            <div>
                                <label for="answer-{{ $leftItem['id'] }}" class="mb-1 block text-sm font-medium dark:text-zinc-300">Selecciona su pareja</label>
                                <select id="answer-{{ $leftItem['id'] }}" name="answers[{{ $leftItem['id'] }}]" required class="min-h-12 w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-base dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <option value="">Elige una respuesta</option>
                                    @foreach ($display['right'] as $rightItem)
                                        <option value="{{ $rightItem['id'] }}" @selected(old('answers.'.$leftItem['id']) === $rightItem['id'])>{{ $rightItem['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ route('activities.play', $attempt->activity) }}" class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold dark:border-zinc-700 dark:text-white">Salir sin enviar</a>
                    <button class="rounded-lg bg-green-600 px-6 py-3 text-lg font-bold text-white">Enviar intento</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
