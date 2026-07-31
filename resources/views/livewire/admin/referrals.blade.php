<div class="space-y-5">

    {{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">

        {{-- Total referidos --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-cyan-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-cyan-400">Total Referidos</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalReferrals) }}</span>
            <span class="text-xs text-gray-500">Desde el inicio</span>
        </div>

        {{-- Registrados --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-gray-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Registrados</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalRegistered) }}</span>
            <span class="text-xs text-gray-500">Pendientes de pago</span>
        </div>

        {{-- Pagados --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-emerald-400">Pagados</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalPaid) }}</span>
            <span class="text-xs text-gray-500">Recompensa acreditada</span>
        </div>

        {{-- Recompensa total --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-amber-400">Recompensas</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalRewardEuros, 2) }} €</span>
            <span class="text-xs text-gray-500">Acreditado en total</span>
        </div>

        {{-- Visitas QR --}}
        <div class="glass rounded-2xl p-5 flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-violet-500/10 blur-2xl"></div>
            <span class="text-xs font-semibold uppercase tracking-widest text-violet-400">Visitas QR</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->totalVisits) }}</span>
            <span class="text-xs text-gray-500">Escaneos de tienda</span>
        </div>
    </div>

    {{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2">
        <button wire:click="showTab('conversiones')"
                @class([
                    'rounded-xl px-4 py-2 text-xs font-semibold transition ring-1',
                    'bg-gradient-to-r from-cyan-500 to-violet-500 text-white ring-transparent shadow-lg shadow-cyan-500/20' => $tab === 'conversiones',
                    'bg-white/5 text-gray-400 ring-white/10 hover:text-white' => $tab !== 'conversiones',
                ])>
            Conversiones
        </button>
        <button wire:click="showTab('recomendadores')"
                @class([
                    'rounded-xl px-4 py-2 text-xs font-semibold transition ring-1',
                    'bg-gradient-to-r from-cyan-500 to-violet-500 text-white ring-transparent shadow-lg shadow-cyan-500/20' => $tab === 'recomendadores',
                    'bg-white/5 text-gray-400 ring-white/10 hover:text-white' => $tab !== 'recomendadores',
                ])>
            Recomendadores
        </button>
    </div>

    {{-- ── Toolbar ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="{{ $tab === 'recomendadores' ? 'Buscar por nombre, email o código…' : 'Buscar por nombre o email del Partner o referido…' }}"
                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>
        @if($tab === 'conversiones')
            <select wire:model.live="filterStatus"
                    class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
                <option value="">Todos los estados</option>
                <option value="registered">Registrado</option>
                <option value="paid">Pagado</option>
            </select>
        @endif
    </div>

    @if($tab === 'recomendadores')
    {{-- ── Recomendadores: todos los usuarios con su link ───────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-5 py-3.5">Link de recomendación</th>
                        <th class="px-5 py-3.5 text-center">Visitas</th>
                        <th class="px-5 py-3.5 text-center">Registrados</th>
                        <th class="px-5 py-3.5 text-center">Pagados</th>
                        <th class="px-5 py-3.5 text-right">Comisión</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->recommenders as $rec)
                        <tr wire:key="rec-{{ $rec->id }}" class="hover:bg-white/[0.02] transition">
                            <td class="px-5 py-4">
                                <p class="font-medium text-white text-sm">{{ $rec->name }}</p>
                                <p class="text-xs text-gray-500">{{ $rec->email }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if($rec->referral_code)
                                    @php $link = $this->shareBase().'/register?ref='.$rec->referral_code; @endphp
                                    <div class="flex items-center gap-2">
                                        <code class="rounded bg-white/5 px-2 py-1 text-xs text-cyan-300 ring-1 ring-white/10">{{ $rec->referral_code }}</code>
                                        <button type="button"
                                                onclick="navigator.clipboard.writeText('{{ $link }}'); this.textContent='¡Copiado!'; setTimeout(() => this.textContent='Copiar link', 1500)"
                                                class="rounded-lg bg-white/5 px-2.5 py-1 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-cyan-500/40 hover:text-cyan-300">
                                            Copiar link
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="generateCode({{ $rec->id }})"
                                            class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-400 ring-1 ring-white/10 transition hover:ring-cyan-500/40 hover:text-cyan-300">
                                        Generar código
                                    </button>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center tabular-nums text-gray-300">{{ $rec->referral_visits_count }}</td>
                            <td class="px-5 py-4 text-center tabular-nums text-gray-300">{{ $rec->referrals_made_count }}</td>
                            <td class="px-5 py-4 text-center tabular-nums text-emerald-300">{{ $rec->paid_referrals_count }}</td>
                            <td class="px-5 py-4 text-right tabular-nums text-xs text-gray-300">
                                {{ number_format((int) ($rec->paid_reward_cents ?? 0) / 100, 2) }} €
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->recommenders->links() }}
        </div>
    </div>
    @else
    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Partner / Referente</th>
                        <th class="px-5 py-3.5">Referido</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5 text-right">Recompensa</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Acreditado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->referrals as $referral)
                        <tr class="hover:bg-white/[0.02] transition group">

                            {{-- Partner / Referente --}}
                            <td class="px-5 py-4">
                                @if($referral->referrer)
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-gradient-to-br from-amber-400/30 to-violet-500/30 text-xs font-bold text-white">
                                            {{ strtoupper(substr($referral->referrer->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-white text-sm">{{ $referral->referrer->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $referral->referrer->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Referido --}}
                            <td class="px-5 py-4">
                                @if($referral->referred)
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-emerald-500/30 text-xs font-bold text-white">
                                            {{ strtoupper(substr($referral->referred->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-white text-sm">{{ $referral->referred->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $referral->referred->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                                    'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20' => $referral->status === 'paid',
                                    'bg-white/5 text-gray-400 ring-white/10'                 => $referral->status === 'registered',
                                ])>
                                    {{ $referral->status === 'paid' ? 'Pagado' : 'Registrado' }}
                                </span>
                            </td>

                            {{-- Recompensa --}}
                            <td class="px-5 py-4 text-right tabular-nums text-xs text-gray-300">
                                @if($referral->reward_cents > 0)
                                    {{ number_format($referral->reward_cents / 100, 2) }} €
                                @else
                                    <span class="text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Acreditado --}}
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $referral->credited_at?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4 text-right">
                                @if($referral->status === 'registered')
                                    <button wire:click="markPaid({{ $referral->id }})"
                                            wire:confirm="¿Marcar este referido como pagado y acreditar la recompensa?"
                                            class="rounded-lg bg-emerald-950/60 px-3 py-1.5 text-xs font-medium text-emerald-300 ring-1 ring-emerald-500/20 transition hover:ring-emerald-400/40 hover:text-emerald-200">
                                        Marcar pagado
                                    </button>
                                @else
                                    <button wire:click="markRegistered({{ $referral->id }})"
                                            wire:confirm="¿Revertir este referido a estado 'registrado'? Se eliminará la fecha de acreditación."
                                            class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-400 ring-1 ring-white/10 transition hover:ring-amber-500/30 hover:text-amber-300">
                                        Marcar registrado
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron referidos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->referrals->links() }}
        </div>
    </div>
    @endif

</div>
