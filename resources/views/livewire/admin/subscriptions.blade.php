<div class="space-y-5">

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por nombre o email…"
                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>
        <select wire:model.live="filterStatus"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los estados</option>
            <option value="active">Activa</option>
            <option value="canceled">Cancelada</option>
        </select>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-5 py-3.5">Plan</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5">Renueva</th>
                        <th class="px-5 py-3.5">Fin</th>
                        <th class="px-5 py-3.5">Creada</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->subscribers as $user)
                        @php
                            $sub        = $user->subscription('default');
                            $isActive   = $sub && $sub->active() && ! $sub->onGracePeriod();
                            $isGrace    = $sub && $sub->onGracePeriod();
                            $isCanceled = $sub && $sub->canceled() && ! $sub->onGracePeriod();

                            // Resolve plan name: prefer user->plan, fallback to planMap via stripe_price
                            $planName = $user->plan?->name
                                ?? ($sub ? ($this->planMap->get($sub->stripe_price) ?? '—') : '—');
                        @endphp
                        <tr class="hover:bg-white/[0.02] transition group">

                            {{-- Usuario --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-violet-500/30 text-xs font-bold text-white">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white text-sm">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Plan --}}
                            <td class="px-5 py-4 text-xs text-gray-400">
                                {{ $planName }}
                            </td>

                            {{-- Estado --}}
                            <td class="px-5 py-4">
                                @if($isActive)
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-emerald-950/60 text-emerald-300 ring-emerald-500/20">
                                        Activa
                                    </span>
                                @elseif($isGrace)
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-amber-950/60 text-amber-300 ring-amber-500/20">
                                        Periodo de gracia
                                    </span>
                                @elseif($isCanceled)
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-white/5 text-gray-400 ring-white/10">
                                        Cancelada
                                    </span>
                                @else
                                    <span class="text-xs text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Renueva --}}
                            <td class="px-5 py-4 text-xs text-gray-500 whitespace-nowrap tabular-nums">
                                {{ $user->renews_at?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Fin (ends_at) --}}
                            <td class="px-5 py-4 text-xs text-gray-500 whitespace-nowrap tabular-nums">
                                {{ $sub?->ends_at?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Creada --}}
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap tabular-nums">
                                {{ $sub?->created_at?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($isActive)
                                        <button wire:click="cancel({{ $user->id }})"
                                                wire:confirm="¿Cancelar la suscripción de {{ $user->name }}? Seguirá activa hasta el final del periodo facturado."
                                                wire:loading.attr="disabled"
                                                wire:target="cancel({{ $user->id }})"
                                                class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-red-400 ring-1 ring-red-500/20 transition hover:ring-red-500/40 hover:text-red-300 disabled:opacity-50">
                                            <span wire:loading.remove wire:target="cancel({{ $user->id }})">Cancelar</span>
                                            <span wire:loading wire:target="cancel({{ $user->id }})">…</span>
                                        </button>
                                    @elseif($isGrace)
                                        <button wire:click="resume({{ $user->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="resume({{ $user->id }})"
                                                class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-emerald-400 ring-1 ring-emerald-500/20 transition hover:ring-emerald-500/40 hover:text-emerald-300 disabled:opacity-50">
                                            <span wire:loading.remove wire:target="resume({{ $user->id }})">Reanudar</span>
                                            <span wire:loading wire:target="resume({{ $user->id }})">…</span>
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-700">—</span>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron suscriptores.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->subscribers->links() }}
        </div>
    </div>

</div>
