<div class="space-y-5">

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por nombre de token o IP…"
                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>
        <button wire:click="revokeAllExpired"
                wire:confirm="¿Eliminar todos los tokens expirados? Esta acción no se puede deshacer."
                wire:loading.attr="disabled"
                wire:target="revokeAllExpired"
                class="flex-shrink-0 rounded-xl bg-white/5 px-4 py-2.5 text-sm font-medium text-gray-400 ring-1 ring-white/10 transition hover:ring-red-500/40 hover:text-red-400">
            <span wire:loading.remove wire:target="revokeAllExpired">Limpiar expirados</span>
            <span wire:loading wire:target="revokeAllExpired">Limpiando…</span>
        </button>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-5 py-3.5">Nombre del token</th>
                        <th class="px-5 py-3.5">Habilidades</th>
                        <th class="px-5 py-3.5">Última vez</th>
                        <th class="px-5 py-3.5">IP</th>
                        <th class="px-5 py-3.5">Creado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->tokens as $token)
                        <tr class="hover:bg-white/[0.02] transition group">
                            {{-- Usuario --}}
                            <td class="px-5 py-4">
                                @if($token->tokenable && method_exists($token->tokenable, 'getAuthIdentifier'))
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-7 w-7 flex-shrink-0 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-violet-500/30 text-[10px] font-bold text-white">
                                            {{ strtoupper(substr($token->tokenable->name ?? '?', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-white text-xs leading-tight">{{ $token->tokenable->name ?? '—' }}</p>
                                            <p class="text-[11px] text-gray-500 leading-tight">{{ $token->tokenable->email ?? '' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Nombre del token --}}
                            <td class="px-5 py-4">
                                <span class="text-xs text-gray-300 font-mono">{{ $token->name }}</span>
                            </td>

                            {{-- Habilidades --}}
                            <td class="px-5 py-4">
                                @php
                                    $abilities = $token->abilities ?? ['*'];
                                    $isWildcard = $abilities === ['*'] || in_array('*', $abilities, true);
                                @endphp
                                @if($isWildcard)
                                    <span class="rounded-full bg-violet-950/60 px-2 py-0.5 text-[11px] font-semibold text-violet-300 ring-1 ring-violet-500/20">*</span>
                                @else
                                    <span class="text-xs text-gray-400 truncate max-w-[120px] block" title="{{ implode(', ', $abilities) }}">
                                        {{ Str::limit(implode(', ', $abilities), 30) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Última vez --}}
                            <td class="px-5 py-4 text-xs text-gray-400 whitespace-nowrap">
                                @if($token->last_used_at)
                                    <span title="{{ $token->last_used_at->format('d/m/Y H:i:s') }}">
                                        {{ $token->last_used_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-gray-600">nunca</span>
                                @endif
                            </td>

                            {{-- IP --}}
                            <td class="px-5 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">
                                {{ $token->ip_address ?? '—' }}
                            </td>

                            {{-- Creado --}}
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $token->created_at?->format('d/m/Y') }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4 text-right">
                                <button wire:click="revoke({{ $token->id }})"
                                        wire:confirm="¿Revocar este token? El usuario tendrá que volver a iniciar sesión."
                                        wire:loading.attr="disabled"
                                        wire:target="revoke({{ $token->id }})"
                                        class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-400 ring-1 ring-white/10 transition hover:ring-red-500/40 hover:text-red-400">
                                    <span wire:loading.remove wire:target="revoke({{ $token->id }})">Revocar</span>
                                    <span wire:loading wire:target="revoke({{ $token->id }})">…</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron tokens.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->tokens->links() }}
        </div>
    </div>

</div>
