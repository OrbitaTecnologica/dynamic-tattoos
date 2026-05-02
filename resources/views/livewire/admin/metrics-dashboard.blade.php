<div wire:poll.30s class="space-y-8">

    {{-- ── KPI Row ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-5">

        {{-- Active QRs --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-cyan-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-cyan-400">QRs Activos</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalActiveQrs) }}</span>
            <span class="text-xs text-gray-500">Tatuajes en línea</span>
        </div>

        {{-- Mutations 24h --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-violet-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-violet-400">Mutaciones 24h</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->mutationsLast24h) }}</span>
            <span class="text-xs text-gray-500">Contenidos actualizados</span>
        </div>

        {{-- Scans total --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-emerald-400">Escaneos Total</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalScans) }}</span>
            <span class="text-xs text-gray-500">Desde el inicio</span>
        </div>

        {{-- Scans 24h --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-amber-400">Escaneos 24h</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->scansLast24h) }}</span>
            <span class="text-xs text-gray-500">Últimas 24 horas</span>
        </div>

        {{-- Total Users --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-pink-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-pink-400">Usuarios</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalUsers) }}</span>
            <span class="text-xs text-gray-500">Registrados</span>
        </div>
    </div>

    {{-- ── Two-column row ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Device breakdown --}}
        <div class="glass rounded-2xl p-6">
            <h3 class="mb-5 text-sm font-semibold uppercase tracking-widest text-gray-400">Dispositivos</h3>
            @php
                $deviceColors = [
                    'mobile'  => 'from-cyan-500 to-cyan-400',
                    'desktop' => 'from-violet-500 to-violet-400',
                    'tablet'  => 'from-emerald-500 to-emerald-400',
                    'bot'     => 'from-amber-500 to-amber-400',
                    'unknown' => 'from-gray-600 to-gray-500',
                ];
                $maxScans = max(1, max($this->scansByDevice ?: [1]));
            @endphp
            <div class="space-y-3">
                @forelse($this->scansByDevice as $device => $count)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-400 capitalize">{{ $device }}</span>
                            <span class="text-white font-semibold tabular-nums">{{ number_format($count) }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-white/5">
                            <div class="h-1.5 rounded-full bg-gradient-to-r {{ $deviceColors[$device] ?? 'from-gray-600 to-gray-500' }} transition-all duration-700"
                                 style="width: {{ round($count / $maxScans * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">Sin datos de escaneos aún.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent activity feed --}}
        <div class="glass rounded-2xl p-6">
            <h3 class="mb-5 text-sm font-semibold uppercase tracking-widest text-gray-400">Actividad Reciente</h3>
            <ul class="space-y-3">
                @forelse($this->recentActivity as $scan)
                    <li class="flex items-center gap-3 text-sm">
                        <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-white/5 text-xs">
                            @if($scan->device_type === 'mobile') 📱
                            @elseif($scan->device_type === 'tablet') 📲
                            @elseif($scan->device_type === 'desktop') 🖥
                            @else 🤖
                            @endif
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-white truncate">
                                {{ $scan->tattoo?->short_code ?? '—' }}
                                <span class="text-gray-500 font-normal ml-1">{{ $scan->tattoo?->name }}</span>
                            </p>
                            <p class="text-xs text-gray-600">{{ $scan->scanned_at?->diffForHumans() }} · {{ $scan->browser }}</p>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-gray-600">No hay escaneos recientes.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Polling indicator --}}
    <div class="flex items-center justify-end gap-2 text-xs text-gray-700">
        <span class="h-1.5 w-1.5 rounded-full bg-cyan-500 animate-pulse"></span>
        Actualización automática cada 30 s
    </div>
</div>
