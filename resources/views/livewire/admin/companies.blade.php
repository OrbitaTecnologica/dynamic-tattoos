<div class="space-y-5">

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por empresa, NIF, IVA o email de usuario…"
                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>
        <select wire:model.live="filterProfessional"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los perfiles</option>
            <option value="1">Profesional</option>
            <option value="0">No profesional</option>
        </select>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-5 py-3.5">Empresa</th>
                        <th class="px-5 py-3.5">Categoría</th>
                        <th class="px-5 py-3.5">NIF / IVA</th>
                        <th class="px-5 py-3.5 text-center">Profesional</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->companies as $company)
                        <tr class="hover:bg-white/[0.02] transition group">
                            {{-- Usuario --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-violet-500/30 text-xs font-bold text-white">
                                        {{ strtoupper(substr($company->user?->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white text-sm">{{ $company->user?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-500">{{ $company->user?->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            {{-- Empresa --}}
                            <td class="px-5 py-4">
                                <p class="text-sm text-white">{{ $company->name ?? '—' }}</p>
                                @if($company->email)
                                    <p class="text-xs text-gray-500">{{ $company->email }}</p>
                                @endif
                            </td>
                            {{-- Categoría --}}
                            <td class="px-5 py-4 text-xs text-gray-400">
                                {{ $company->category ?? '—' }}
                            </td>
                            {{-- NIF / IVA --}}
                            <td class="px-5 py-4">
                                <div class="space-y-0.5">
                                    <p class="text-xs text-gray-300 tabular-nums font-mono">
                                        {{ $company->tax_id ?? '—' }}
                                    </p>
                                    @if($company->vat && $company->vat !== $company->tax_id)
                                        <p class="text-xs text-gray-500 tabular-nums font-mono">
                                            {{ $company->vat }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            {{-- Profesional --}}
                            <td class="px-5 py-4 text-center">
                                @if($company->is_professional)
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-cyan-950/60 text-cyan-300 ring-cyan-500/20">
                                        Sí
                                    </span>
                                @else
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-white/5 text-gray-400 ring-white/10">
                                        No
                                    </span>
                                @endif
                            </td>
                            {{-- Acciones --}}
                            <td class="px-5 py-4 text-right">
                                <button wire:click="openEdit({{ $company->id }})"
                                        class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-cyan-500/40 hover:text-cyan-300">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron empresas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->companies->links() }}
        </div>
    </div>

    {{-- ── Edit Modal ───────────────────────────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative glass rounded-2xl w-full max-w-lg">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4">
                    <h2 class="text-sm font-semibold text-white">Editar empresa</h2>
                    <button wire:click="$set('showModal', false)" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">

                    {{-- Nombre + Categoría --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">Nombre de empresa</label>
                            <input wire:model="name" type="text"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('name') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">Categoría</label>
                            <input wire:model="category" type="text"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('category') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Email + Web --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">Email</label>
                            <input wire:model="email" type="email"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('email') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">Sitio web</label>
                            <input wire:model="website" type="url" placeholder="https://…"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('website') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Dirección --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-gray-400">Dirección</label>
                        <input wire:model="address" type="text"
                               class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                        @error('address') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- NIF + IVA --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">NIF (tax_id)</label>
                            <input wire:model="taxId" type="text"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('taxId') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">IVA / VAT</label>
                            <input wire:model="vat" type="text"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('vat') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Perfil profesional --}}
                    <div class="flex items-center gap-3 rounded-xl bg-white/[0.03] border border-white/[0.06] px-4 py-3">
                        <input wire:model="isProfessional" type="checkbox" id="isProfessional"
                               class="h-4 w-4 rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-0">
                        <label for="isProfessional" class="text-sm text-gray-300 cursor-pointer select-none">
                            Perfil profesional
                            <span class="ml-1.5 text-xs text-gray-600">(activa NIF/IVA para Stripe Tax)</span>
                        </label>
                    </div>

                </div>
                <div class="border-t border-white/[0.06] px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="$set('showModal', false)"
                            class="rounded-xl px-4 py-2 text-sm text-gray-500 ring-1 ring-white/10 transition hover:text-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="saveCompany"
                            wire:loading.attr="disabled"
                            wire:target="saveCompany"
                            class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveCompany">Guardar</span>
                        <span wire:loading wire:target="saveCompany">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
