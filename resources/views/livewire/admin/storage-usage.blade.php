<div class="space-y-6">

    {{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">

        {{-- Total de archivos --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-cyan-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-cyan-400">Total Archivos</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalFiles) }}</span>
            <span class="text-xs text-gray-500">Uploads registrados</span>
        </div>

        {{-- Almacenamiento usado --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-violet-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-violet-400">Almacenamiento Usado</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ $this->totalUsedFormatted }}</span>
            <span class="text-xs text-gray-500">En todo el sistema</span>
        </div>

        {{-- Usuarios con uploads --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden col-span-2 lg:col-span-1">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-emerald-400">Usuarios con Archivos</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->usersWithUploads) }}</span>
            <span class="text-xs text-gray-500">Con al menos 1 upload</span>
        </div>
    </div>

    {{-- ── Top consumo por usuario ──────────────────────────────────────────── --}}
    <div class="glass rounded-2xl p-6">
        <h3 class="mb-5 text-sm font-semibold uppercase tracking-widest text-gray-400">Top consumo por usuario</h3>

        @php
            $maxBytes = max(1, $this->topUsers->max('used_bytes_sum') ?? 1);
        @endphp

        @forelse($this->topUsers as $topUser)
            @php
                $usage     = $topUser->storageUsage();
                $usedBytes = (int) $topUser->used_bytes_sum;
                $barPct    = round($usedBytes / $maxBytes * 100);
                $quotaPct  = $usage['percent'];
            @endphp
            <div class="mb-3 last:mb-0">
                <div class="flex items-center justify-between text-xs mb-1.5 gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-violet-500/30 text-[10px] font-bold text-white">
                            {{ strtoupper(substr($topUser->name, 0, 2)) }}
                        </div>
                        <span class="truncate font-medium text-white">{{ $topUser->name }}</span>
                        <span class="truncate text-gray-500 hidden sm:block">{{ $topUser->email }}</span>
                    </div>
                    <div class="flex-shrink-0 flex items-center gap-3 text-right">
                        <span class="text-gray-300 tabular-nums font-medium">{{ $this->formatBytes($usedBytes) }}</span>
                        <span @class([
                            'rounded-full px-2 py-0.5 font-semibold ring-1',
                            'bg-red-950/60 text-red-400 ring-red-500/20'       => $quotaPct >= 90,
                            'bg-amber-950/60 text-amber-400 ring-amber-500/20' => $quotaPct >= 70 && $quotaPct < 90,
                            'bg-white/5 text-gray-500 ring-white/10'           => $quotaPct < 70,
                        ])>{{ $quotaPct }}%</span>
                    </div>
                </div>
                <div class="h-1.5 w-full rounded-full bg-white/5">
                    <div class="h-1.5 rounded-full bg-gradient-to-r from-cyan-500 to-violet-500 transition-all duration-700"
                         style="width: {{ $barPct }}%"></div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-600">No hay usuarios con archivos almacenados.</p>
        @endforelse
    </div>

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por email del usuario…"
                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>
        <select wire:model.live="filterType"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los tipos</option>
            <option value="avatar">Avatar</option>
            <option value="cover">Portada</option>
            <option value="gallery">Galería</option>
            <option value="qr">QR</option>
        </select>
    </div>

    {{-- ── Tabla glass de uploads ───────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-5 py-3.5">Tipo</th>
                        <th class="px-5 py-3.5">Archivo</th>
                        <th class="px-5 py-3.5 text-right">Tamaño</th>
                        <th class="px-5 py-3.5">Disco</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Fecha</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->uploads as $upload)
                        <tr class="hover:bg-white/[0.02] transition group">

                            {{-- Usuario --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-shrink-0 h-7 w-7 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-violet-500/30 text-[10px] font-bold text-white">
                                        {{ strtoupper(substr($upload->user?->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-white text-xs truncate">{{ $upload->user?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $upload->user?->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Tipo (badge) --}}
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                                    'bg-cyan-950/60 text-cyan-300 ring-cyan-500/20'           => $upload->type === 'avatar',
                                    'bg-violet-950/60 text-violet-300 ring-violet-500/20'     => $upload->type === 'cover',
                                    'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20'  => $upload->type === 'gallery',
                                    'bg-amber-950/60 text-amber-300 ring-amber-500/20'        => $upload->type === 'qr',
                                    'bg-white/5 text-gray-400 ring-white/10'                  => ! in_array($upload->type, ['avatar','cover','gallery','qr']),
                                ])>
                                    {{ ucfirst($upload->type ?? '—') }}
                                </span>
                            </td>

                            {{-- Archivo (basename del path) --}}
                            <td class="px-5 py-4 text-xs text-gray-400 max-w-[180px] truncate" title="{{ $upload->path }}">
                                {{ basename($upload->path) }}
                            </td>

                            {{-- Tamaño --}}
                            <td class="px-5 py-4 text-xs text-gray-300 tabular-nums text-right whitespace-nowrap">
                                {{ $this->formatBytes((int) $upload->bytes) }}
                            </td>

                            {{-- Disco --}}
                            <td class="px-5 py-4 text-xs text-gray-500">
                                {{ $upload->disk }}
                            </td>

                            {{-- Fecha --}}
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $upload->created_at?->format('d/m/Y H:i') }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4 text-right">
                                <button wire:click="deleteUpload({{ $upload->id }})"
                                        wire:confirm="¿Eliminar este archivo? Esta acción no se puede deshacer."
                                        wire:loading.attr="disabled"
                                        wire:target="deleteUpload({{ $upload->id }})"
                                        class="rounded-lg bg-red-950/40 px-3 py-1.5 text-xs font-medium text-red-400 ring-1 ring-red-500/20 transition hover:ring-red-500/50 hover:text-red-300 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="deleteUpload({{ $upload->id }})">Eliminar</span>
                                    <span wire:loading wire:target="deleteUpload({{ $upload->id }})">…</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron archivos con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->uploads->links() }}
        </div>
    </div>

</div>
