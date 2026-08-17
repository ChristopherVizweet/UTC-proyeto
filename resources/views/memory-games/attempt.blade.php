<x-layouts::app :title="$attempt->activity->titulo">
    <script>
        window.memoryGame = (cards, totalPairs) => ({
            cards, totalPairs, opened: [], matched: [], moves: 0, locked: false,
            visible(card) { return this.opened.includes(card.card_id) || this.matched.includes(card.pair_id); },
            flip(card) {
                if (this.locked || this.visible(card) || this.opened.length >= 2) return;
                this.opened.push(card.card_id);
                if (this.opened.length !== 2) return;
                this.moves++;
                const selected = this.cards.filter(item => this.opened.includes(item.card_id));
                if (selected[0].pair_id === selected[1].pair_id) {
                    this.matched.push(selected[0].pair_id);
                    setTimeout(() => this.opened = [], 450);
                    return;
                }
                this.locked = true;
                setTimeout(() => { this.opened = []; this.locked = false; }, 900);
            },
        });
    </script>
    <div class="p-4 sm:p-6"><div class="mx-auto max-w-6xl space-y-6" x-data="memoryGame(@js($attempt->answers['cards']), {{ count($attempt->answers['pair_ids']) }})">
        <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold text-fuchsia-600">Intento {{ $attempt->attempt_number }}</p><h1 class="text-2xl font-bold dark:text-white">{{ $attempt->activity->titulo }}</h1><p class="text-zinc-500">Voltea dos tarjetas y encuentra sus parejas.</p></div><div class="flex gap-3"><span class="rounded-lg bg-fuchsia-100 px-4 py-2 font-bold text-fuchsia-800">Movimientos: <span x-text="moves"></span></span><span class="rounded-lg bg-green-100 px-4 py-2 font-bold text-green-800">Parejas: <span x-text="matched.length"></span> / {{ count($attempt->answers['pair_ids']) }}</span></div></div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($attempt->answers['cards'] as $card)
                <button type="button" x-on:click="flip(@js($card))" x-bind:disabled="locked || matched.includes(@js($card['pair_id']))" class="group aspect-[4/5] overflow-hidden rounded-xl border-2 border-dashed border-fuchsia-400 bg-fuchsia-600 shadow-sm transition hover:-translate-y-1 disabled:cursor-default disabled:hover:translate-y-0">
                    <div x-show="!visible(@js($card))" class="flex h-full items-center justify-center bg-gradient-to-br from-fuchsia-500 to-violet-700 text-5xl font-black text-white">?</div>
                    <div x-show="visible(@js($card))" x-cloak class="flex h-full flex-col bg-white p-2 dark:bg-zinc-900"><img src="{{ route('activities.memory.image', [$attempt->activity, $card['item_id']]) }}" alt="{{ $card['label'] }}" class="min-h-0 flex-1 rounded-lg object-contain"><span class="pt-2 text-sm font-bold text-zinc-800 dark:text-white">{{ $card['label'] }}</span></div>
                </button>
            @endforeach
        </div>
        <form method="POST" action="{{ route('activities.memory.attempts.submit', $attempt) }}" class="flex justify-end">@csrf<input type="hidden" name="moves" x-bind:value="moves"><template x-for="(pairId, index) in matched" x-bind:key="pairId"><input type="hidden" x-bind:name="`matched_pair_ids[${index}]`" x-bind:value="pairId"></template><button x-bind:disabled="matched.length !== totalPairs" class="rounded-lg bg-green-600 px-6 py-3 text-lg font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">Completar memorama</button></form>
    </div></div>
</x-layouts::app>
