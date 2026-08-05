<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl bg-gradient-to-br from-sky-100 via-cyan-50 to-amber-50 p-4">
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="relative col-span-3 overflow-hidden rounded-3xl border border-sky-200 bg-gradient-to-r from-sky-400 via-cyan-400 to-emerald-400 p-6 shadow-lg">
                <div class="absolute right-4 top-4 h-20 w-20 rounded-full bg-white/30"></div>
                <div class="absolute bottom-0 right-0 h-24 w-24 rounded-tl-full bg-yellow-300/40"></div>
                <div class="relative z-10">
                    <p class="text-lg font-semibold text-white">¡Hola de nuevo!</p>
                    <h1 class="mt-2 text-3xl font-black text-white">{{ auth()->user()->name }}</h1>
                    <p class="mt-3 text-large text-cyan-50">¡Qué gusto tenerte hoy en tu plataforma favorita!</p>
                    <p class="mt-3 text-large text-cyan-50">¿Qué tal si exploras algunas nuevas actividades hoy?</p>
                </div>
                <button class="absolute bottom-4 right-4 rounded-full bg-white/30 px-4 py-2 text-sm font-semibold text-white hover:bg-white/50">Ver tareas</button>
            </div>

            <div class="relative aspect-video overflow-hidden rounded-3xl border border-amber-200 bg-amber-100 p-4 shadow-md">
                <div class="flex h-full flex-col justify-between">
                    <div>
                        <p class="text-lg font-bold text-amber-700">📚 Actividades</p>
                        <p class="mt-2 text-sm text-amber-800">Aprende y diviértete.</p>
                    </div>
                    <div class="text-4xl font-black text-amber-600">✨</div>
                </div>
            </div>

            <div class="relative aspect-video overflow-hidden rounded-3xl border border-pink-200 bg-pink-100 p-4 shadow-md">
                <div class="flex h-full flex-col justify-between">
                    <div>
                        <p class="text-lg font-bold text-pink-700">✍️ Tareas</p>
                        <p class="mt-2 text-sm text-pink-800">¡Tiempo de jugar y explorar!</p>
                    </div>
                    <div class="text-4xl font-black text-pink-600">🎈</div>
                </div>
            </div>
        </div>

        <div class="relative h-full flex-1 overflow-hidden rounded-3xl border border-emerald-200 bg-white/80 p-6 shadow-md">
            <p class="text-xl font-bold text-emerald-700">🌟 Hoy te proponemos</p>
            <p class="mt-2 text-sm text-emerald-800">Descubre nuevas aventuras, retos y sorpresas.</p>
        </div>
    </div>
</x-layouts::app>
