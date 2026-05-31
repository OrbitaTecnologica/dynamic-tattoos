<div class="space-y-6">

    {{-- ── Plans grid ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400">Planes activos</h3>
        <button wire:click="openCreate"
                class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
            + Nuevo plan
        </button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($this->plans as $plan)
            <div class="glass rounded-2xl p-5 flex flex-col gap-4 relative overflow-hidden transition hover:border-cyan-500/20">
                {{-- Glow --}}
                <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full {{ $plan->is_active ? 'bg-cyan-500/10' : 'bg-gray-700/10' }} blur-2xl"></div>

                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-white">{{ $plan->name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ $plan->billing_cycle }}</p>
                    </div>
                    <span @class([
                        'rounded-full px-2 py-0.5 text-xs font-semibold ring-1',
                        'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20' => $plan->is_active,
                        'bg-gray-900 text-gray-600 ring-white/10' => !$plan->is_active,
                    ])>
                        {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                @if($plan->is_featured || $plan->is_referral)
                    <div class="flex flex-wrap gap-1.5">
                        @if($plan->is_featured)
                            <span class="rounded-full bg-violet-950/60 px-2 py-0.5 text-[10px] font-semibold text-violet-300 ring-1 ring-violet-500/20">★ Destacado</span>
                        @endif
                        @if($plan->is_referral)
                            <span class="rounded-full bg-amber-950/60 px-2 py-0.5 text-[10px] font-semibold text-amber-300 ring-1 ring-amber-500/20">
                                Referidos · {{ $plan->referral_reward !== null ? number_format((float) $plan->referral_reward, 2).' €' : 'global' }}
                            </span>
                        @endif
                    </div>
                @endif

                <div>
                    <span class="text-3xl font-bold text-white">${{ number_format((float)$plan->price, 2) }}</span>
                    <span class="text-xs text-gray-500"> / {{ $plan->billing_cycle }}</span>
                </div>

                <ul class="space-y-1.5 flex-1">
                    @foreach($plan->features ?? [] as $feature)
                        <li class="flex items-center gap-2 text-xs text-gray-400">
                            <svg class="h-3 w-3 flex-shrink-0 text-cyan-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                    <li class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="h-3 w-3 flex-shrink-0 text-violet-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Hasta {{ $plan->max_tattoos }} tatuaje(s)
                    </li>
                </ul>

                <div class="flex items-center gap-2 text-xs text-gray-500 border-t border-white/[0.06] pt-3">
                    <span>{{ $plan->users_count ?? 0 }} usuarios</span>
                    <span class="ml-auto flex gap-2">
                        <button wire:click="openEdit({{ $plan->id }})" class="hover:text-cyan-300 transition">Editar</button>
                        <button wire:click="toggleActive({{ $plan->id }})" class="hover:text-amber-300 transition">
                            {{ $plan->is_active ? 'Desactivar' : 'Activar' }}
                        </button>
                        <button wire:click="deletePlan({{ $plan->id }})"
                                wire:confirm="¿Eliminar este plan? Los usuarios asignados perderán su referencia."
                                class="hover:text-red-400 transition">
                            Eliminar
                        </button>
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-sm text-gray-600">
                No hay planes configurados. Crea el primero.
            </div>
        @endforelse
    </div>

    {{-- ── Modal ────────────────────────────────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative glass rounded-2xl w-full max-w-md">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4">
                    <h2 class="text-sm font-semibold text-white">
                        {{ $editingId ? 'Editar plan' : 'Nuevo plan' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Nombre</label>
                            <input wire:model="name" type="text" class="admin-input w-full" placeholder="Pro, Starter…">
                            @error('name') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Precio (USD)</label>
                            <input wire:model="price" type="number" step="0.01" min="0" class="admin-input w-full">
                            @error('price') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Ciclo</label>
                            <select wire:model="billingCycle" class="admin-input w-full">
                                <option value="monthly">Mensual</option>
                                <option value="yearly">Anual</option>
                                <option value="lifetime">De por vida</option>
                            </select>
                        </div>
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Stripe Price ID</label>
                            <input wire:model="stripePriceId" type="text" class="admin-input w-full" placeholder="price_123abc...">
                            @error('stripePriceId') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Máx. tatuajes</label>
                            <input wire:model="maxTattoos" type="number" min="1" class="admin-input w-full">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Orden</label>
                            <input wire:model="sortOrder" type="number" min="0" class="admin-input w-full">
                        </div>
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Características <span class="text-gray-600">(una por línea)</span></label>
                            <textarea wire:model="featuresRaw" rows="4" class="admin-input w-full resize-none" placeholder="Acceso ilimitado&#10;Soporte 24/7"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Almacenamiento (MB)</label>
                            <input wire:model="storageMb" type="number" min="0" class="admin-input w-full">
                            @error('storageMb') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2 border-t border-white/[0.06] pt-3 space-y-3">
                            <div class="flex items-center gap-2">
                                <input wire:model="isFeatured" type="checkbox" id="isFeatured" class="rounded border-white/20 bg-white/5 text-violet-500 focus:ring-violet-500/40">
                                <label for="isFeatured" class="text-xs text-gray-400">Plan destacado (el “más elegido”)</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input wire:model.live="isReferral" type="checkbox" id="isReferral" class="rounded border-white/20 bg-white/5 text-amber-500 focus:ring-amber-500/40">
                                <label for="isReferral" class="text-xs text-gray-400">Plan de referidos / Partner <span class="text-gray-600">(QR de tienda + panel de monitorización + recompensa)</span></label>
                            </div>
                            <div class="space-y-1" x-show="$wire.isReferral" x-cloak>
                                <label class="text-xs font-medium text-gray-400">Recompensa por referido (€)</label>
                                <input wire:model="referralReward" type="number" step="0.01" min="0" class="admin-input w-full" placeholder="Vacío = usar valor global">
                                @error('referralReward') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                <p class="text-xs text-gray-600">Lo que se acredita al Partner por cada referido que paga. Vacío = valor global de configuración.</p>
                            </div>
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input wire:model="isActive" type="checkbox" id="isActive" class="rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500/40">
                            <label for="isActive" class="text-xs text-gray-400">Plan activo (visible para usuarios)</label>
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
        [x-cloak] { display: none !important; }
        .admin-input {
            @apply rounded-xl bg-white/5 border border-white/[0.08] px-3 py-2 text-sm text-white placeholder-gray-600
                   focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition;
        }
    </style>
</div>
