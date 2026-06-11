<div class="space-y-8">

    {{-- ── Solicitudes de homologación (pendientes) ─────────────────────────── --}}
    @if($this->solicitudes->isNotEmpty())
        <div class="space-y-3">
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400">
                Solicitudes pendientes
                <span class="ml-1 rounded-full bg-amber-950/60 px-2 py-0.5 text-xs text-amber-300 ring-1 ring-amber-500/20">{{ $this->solicitudes->count() }}</span>
            </h3>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($this->solicitudes as $sol)
                    <div class="glass rounded-2xl p-4 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-white">{{ $sol->studio_name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $sol->name }} · {{ $sol->city ?? '—' }} · {{ $sol->email }}
                                    @if($sol->phone) · {{ $sol->phone }} @endif
                                </p>
                            </div>
                            <span class="whitespace-nowrap text-xs text-gray-600">{{ $sol->created_at?->diffForHumans() }}</span>
                        </div>
                        @if($sol->message)
                            <p class="text-sm text-gray-300">{{ $sol->message }}</p>
                        @endif
                        <div class="flex items-center gap-3 pt-1 text-xs">
                            <button wire:click="certifySolicitud({{ $sol->id }})"
                                    class="rounded-lg bg-emerald-950/60 px-3 py-1.5 font-semibold text-emerald-300 ring-1 ring-emerald-500/20 transition hover:bg-emerald-900/60">
                                ✓ Certificar y añadir al mapa
                            </button>
                            <button wire:click="rejectSolicitud({{ $sol->id }})"
                                    wire:confirm="¿Rechazar esta solicitud?"
                                    class="rounded-lg px-3 py-1.5 font-medium text-gray-500 ring-1 ring-white/10 transition hover:text-red-400">
                                ✕ Rechazar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Tatuadores certificados ──────────────────────────────────────────── --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400">Tatuadores certificados</h3>
            <button wire:click="openCreate"
                    class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
                + Nuevo tatuador
            </button>
        </div>

        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3">Estudio</th>
                        <th class="px-5 py-3">Artista</th>
                        <th class="px-5 py-3">Ciudad</th>
                        <th class="px-5 py-3">Coordenadas</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->tatuadores as $t)
                        <tr class="border-b border-white/[0.04] transition hover:bg-white/[0.02]">
                            <td class="px-5 py-3 font-medium text-white">{{ $t->studio_name }}</td>
                            <td class="px-5 py-3 text-gray-300">{{ $t->artist_name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-300">{{ $t->city }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $t->lat }}, {{ $t->lng }}</td>
                            <td class="px-5 py-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs font-semibold ring-1',
                                    'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20' => $t->is_active,
                                    'bg-gray-900 text-gray-600 ring-white/10' => !$t->is_active,
                                ])>
                                    {{ $t->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="flex items-center justify-end gap-3 text-xs text-gray-500">
                                    <button wire:click="openEdit({{ $t->id }})" class="hover:text-cyan-300 transition">Editar</button>
                                    <button wire:click="toggleActive({{ $t->id }})" class="hover:text-amber-300 transition">
                                        {{ $t->is_active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                    <button wire:click="deleteTatuador({{ $t->id }})"
                                            wire:confirm="¿Eliminar este tatuador del mapa?"
                                            class="hover:text-red-400 transition">Eliminar</button>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-600">
                                Aún no hay tatuadores certificados. Crea el primero o certifica una solicitud.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modal crear/editar ───────────────────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative glass rounded-2xl w-full max-w-lg">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4">
                    <h2 class="text-sm font-semibold text-white">
                        {{ $editingId ? 'Editar tatuador' : 'Certificar tatuador' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Estudio *</label>
                            <input wire:model="studioName" type="text" class="admin-input w-full" placeholder="Black Needle Studio">
                            @error('studioName') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Artista</label>
                            <input wire:model="artistName" type="text" class="admin-input w-full" placeholder="Marcos Ruiz">
                            @error('artistName') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Ciudad *</label>
                            <input wire:model="city" type="text" class="admin-input w-full" placeholder="Madrid">
                            @error('city') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Teléfono</label>
                            <input wire:model="phone" type="text" class="admin-input w-full" placeholder="+34 911 234 567">
                            @error('phone') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Dirección</label>
                            <input wire:model="address" type="text" class="admin-input w-full" placeholder="Gran Vía 45, Madrid">
                            @error('address') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Email</label>
                            <input wire:model="email" type="email" class="admin-input w-full" placeholder="estudio@ejemplo.es">
                            @error('email') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Latitud *</label>
                            <input wire:model="lat" type="text" class="admin-input w-full" placeholder="40.4200">
                            @error('lat') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Longitud *</label>
                            <input wire:model="lng" type="text" class="admin-input w-full" placeholder="-3.7025">
                            @error('lng') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2 space-y-1">
                            <label class="text-xs font-medium text-gray-400">Enlace Google Maps</label>
                            <input wire:model="mapsUrl" type="text" class="admin-input w-full" placeholder="https://maps.google.com/?q=...">
                            @error('mapsUrl') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-400">Orden</label>
                            <input wire:model="sortOrder" type="number" min="0" class="admin-input w-full">
                            @error('sortOrder') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end pb-2">
                            <div class="flex items-center gap-2">
                                <input wire:model="isActive" type="checkbox" id="tatActive" class="rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500/40">
                                <label for="tatActive" class="text-xs text-gray-400">Activo en el mapa</label>
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
