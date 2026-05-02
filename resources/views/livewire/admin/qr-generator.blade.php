<div class="space-y-8">

    {{-- ── Generator Form ──────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl p-6">
        <h3 class="mb-6 text-sm font-semibold uppercase tracking-widest text-gray-400">
            Configurar generación
        </h3>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Quantity --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-gray-400">Cantidad de QRs</label>
                <input wire:model="quantity"
                       type="number" min="1" max="50"
                       class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                @error('quantity') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-600">Máx. 50 por operación</p>
            </div>

            {{-- Prefix --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-gray-400">Prefijo del nombre <span class="text-gray-600">(opcional)</span></label>
                <input wire:model="prefix"
                       type="text" maxlength="4" placeholder="ej. TAT"
                       class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                @error('prefix') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Assign to user --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-gray-400">Asignar a usuario</label>
                <select wire:model="assignToUserId"
                        class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
                    <option value="">— Mi cuenta —</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Info callout --}}
        <div class="mt-5 flex items-start gap-3 rounded-xl bg-cyan-950/30 px-4 py-3 ring-1 ring-cyan-500/20">
            <svg class="h-4 w-4 flex-shrink-0 mt-0.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-cyan-300">
                Todos los QRs se generan con corrección de errores nivel <strong>H</strong> (30 % de redundancia), optimizado para sobrevivir deformaciones en piel.
            </p>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button wire:click="generate"
                    wire:loading.attr="disabled"
                    wire:target="generate"
                    class="relative overflow-hidden rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40 disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="generate">Generar QRs</span>
                <span wire:loading wire:target="generate" class="flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Generando…
                </span>
            </button>

            @if(count($generatedCodes) > 0)
                <button wire:click="clearResults"
                        class="rounded-xl px-4 py-2.5 text-sm font-medium text-gray-500 ring-1 ring-white/10 transition hover:text-gray-300 hover:ring-white/20">
                    Limpiar
                </button>
            @endif
        </div>
    </div>

    {{-- ── Generated Codes Grid ─────────────────────────────────────────────── --}}
    @if(count($generatedCodes) > 0)
        <div>
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-gray-400">
                {{ count($generatedCodes) }} QR(s) generados
            </h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
                @foreach($generatedCodes as $code)
                    <div class="glass rounded-2xl p-4 flex flex-col items-center gap-3 text-center">
                        {{-- SVG QR code --}}
                        <div class="rounded-xl bg-white p-2 w-full aspect-square flex items-center justify-center">
                            {!! $code['qr_svg'] !!}
                        </div>
                        <span class="font-mono text-xs text-cyan-300 tracking-widest">{{ $code['short_code'] }}</span>
                        <a href="{{ $code['url'] }}" target="_blank"
                           class="text-xs text-gray-600 hover:text-gray-400 transition truncate w-full">
                            {{ $code['url'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
