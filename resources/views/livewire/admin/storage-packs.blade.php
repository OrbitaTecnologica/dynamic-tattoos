<div class="space-y-6">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400">Packs configurados</h3>
        <button wire:click="openCreate"
                class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
            + Nuevo pack
        </button>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                    <th class="px-5 py-3">Etiqueta</th>
                    <th class="px-5 py-3">Tamaño</th>
                    <th class="px-5 py-3">Precio</th>
                    <th class="px-5 py-3">Stripe Price ID</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3">Orden</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->packs as $pack)
                    <tr class="border-b border-white/[0.04] transition hover:bg-white/[0.02]">
                        <td class="px-5 py-3 font-medium text-white">{{ $pack->label }}</td>
                        <td class="px-5 py-3 text-gray-300">
                            @if($pack->size_mb >= 1024)
                                {{ number_format($pack->size_mb / 1024, 1) }} GB
                            @else
                                {{ $pack->size_mb }} MB
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-300">{{ number_format((float) $pack->price, 2) }} €</td>
                        <td class="px-5 py-3 text-gray-500 font-mono text-xs">
                            {{ $pack->stripe_price_id ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-semibold ring-1',
                                'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20' => $pack->is_active,
                                'bg-gray-900 text-gray-600 ring-white/10' => !$pack->is_active,
                            ])>
                                {{ $pack->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $pack->sort_order }}</td>
                        <td class="px-5 py-3 text-right">
                            <span class="flex items-center justify-end gap-3 text-xs text-gray-500">
                                <button wire:click="openEdit({{ $pack->id }})"
                                        class="hover:text-cyan-300 transition">
                                    Editar
                                </button>
                                <button wire:click="toggleActive({{ $pack->id }})"
                                        class="hover:text-amber-300 transition">
                                    {{ $pack->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button wire:click="deletePack({{ $pack->id }})"
                                        wire:confirm="¿Eliminar este pack? Los usuarios que lo hayan comprado no se verán afectados."
                                        class="hover:text-red-400 transition">
                                    Eliminar
                                </button>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-600">
                            No hay packs configurados. Crea el primero.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Modal ────────────────────────────────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative glass rounded-2xl w-full max-w-md">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4">
                    <h2 class="text-sm font-semibold text-white">
                        {{ $editingId ? 'Editar pack' : 'Nuevo pack' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Etiqueta</label>
                            <input wire:model="label" type="text" class="admin-input w-full" placeholder="Pack 5 GB, Extra Storage…">
                            @error('label') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Tamaño (MB)</label>
                            <input wire:model="sizeMb" type="number" min="1" class="admin-input w-full" placeholder="5120">
                            @error('sizeMb') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Precio (€)</label>
                            <input wire:model="price" type="number" step="0.01" min="0" class="admin-input w-full" placeholder="4.99">
                            @error('price') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Stripe Price ID</label>
                            <input wire:model="stripePriceId" type="text" class="admin-input w-full" placeholder="price_123abc…">
                            @error('stripePriceId') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Orden</label>
                            <input wire:model="sortOrder" type="number" min="0" class="admin-input w-full">
                            @error('sortOrder') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end pb-2">
                            <div class="flex items-center gap-2">
                                <input wire:model="isActive" type="checkbox" id="isActive" class="rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500/40">
                                <label for="isActive" class="text-xs text-gray-400">Pack activo</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/[0.06] px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="$set('showModal', false)"
                            class="rounded-xl px-4 py-2 text-sm font-medium text-gray-500 ring-1 ring-white/10 transition hover:text-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        .admin-input {
            @apply rounded-xl bg-white/5 border border-white/[0.08] px-3 py-2 text-sm text-white placeholder-gray-600
                   focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition;
        }
    </style>
</div>
