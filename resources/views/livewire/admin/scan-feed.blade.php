<div wire:poll.5s class="space-y-5">

    {{-- ── Filter bar ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-2 items-center">
        <button wire:click="$set('filterDevice', '')"
                @class(['rounded-full px-3 py-1 text-xs font-semibold ring-1 transition',
                        'bg-white/10 ring-white/20 text-white' => $filterDevice === '',
                        'ring-white/10 text-gray-500 hover:text-gray-300' => $filterDevice !== ''])>
            Todos
        </button>
        @foreach(['mobile','desktop','tablet','bot','unknown'] as $d)
            <button wire:click="$set('filterDevice', '{{ $d }}')"
                    @class(['rounded-full px-3 py-1 text-xs font-semibold ring-1 transition capitalize',
                            'bg-cyan-950/60 ring-cyan-500/40 text-cyan-300' => $filterDevice === $d,
                            'ring-white/10 text-gray-500 hover:text-gray-300' => $filterDevice !== $d])>
                {{ $d }}
                @if(isset($this->deviceCounts[$d]))
                    <span class="ml-1 opacity-60">{{ $this->deviceCounts[$d] }}</span>
                @endif
            </button>
        @endforeach

        <span class="ml-auto flex items-center gap-1.5 text-xs text-gray-600">
            <span class="h-1.5 w-1.5 rounded-full bg-cyan-500 animate-pulse"></span>
            Actualización cada 5 s
        </span>
    </div>

    {{-- ── Feed ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Tatuaje</th>
                        <th class="px-5 py-3.5">Dispositivo</th>
                        <th class="px-5 py-3.5">Navegador</th>
                        <th class="px-5 py-3.5">IP</th>
                        <th class="px-5 py-3.5">Cuando</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->scans as $scan)
                        <tr class="group hover:bg-white/[0.02] transition">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-cyan-300 text-xs tracking-wider">
                                    {{ $scan->tattoo?->short_code ?? '—' }}
                                </span>
                                <p class="text-xs text-gray-600">{{ $scan->tattoo?->name }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $deviceIcon = match($scan->device_type) {
                                        'mobile'  => '📱',
                                        'tablet'  => '📲',
                                        'desktop' => '🖥',
                                        'bot'     => '🤖',
                                        default   => '❓',
                                    };
                                @endphp
                                <span class="flex items-center gap-1.5 text-xs text-gray-300 capitalize">
                                    <span>{{ $deviceIcon }}</span>
                                    {{ $scan->device_type }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-400">{{ $scan->browser }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-500">
                                {{ $scan->ip_address ? substr($scan->ip_address, 0, -3) . '***' : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600 whitespace-nowrap">
                                {{ $scan->scanned_at?->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-600">
                                No hay escaneos para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
