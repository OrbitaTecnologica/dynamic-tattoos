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
        <select wire:model.live="filterRole"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los roles</option>
            <option value="admin">Admin</option>
            <option value="artist">Artista</option>
            <option value="user">Usuario</option>
        </select>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-5 py-3.5">Rol</th>
                        <th class="px-5 py-3.5">Plan</th>
                        <th class="px-5 py-3.5 text-center">Tatuajes</th>
                        <th class="px-5 py-3.5">Registro</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->users as $user)
                        <tr class="hover:bg-white/[0.02] transition group">
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
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                                    'bg-cyan-950/60 text-cyan-300 ring-cyan-500/20'     => $user->role === 'admin',
                                    'bg-violet-950/60 text-violet-300 ring-violet-500/20' => $user->role === 'artist',
                                    'bg-white/5 text-gray-400 ring-white/10'             => $user->role === 'user',
                                ])>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-400">
                                {{ $user->plan?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-center text-xs tabular-nums text-gray-400">
                                {{ $user->tattoos_count }}
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $user->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($user->two_factor_confirmed_at)
                                        <span title="2FA activo" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-950/60 text-emerald-300 ring-1 ring-emerald-500/20">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </span>
                                        <button wire:click="resetTwoFactor({{ $user->id }})"
                                                wire:confirm="¿Restablecer el 2FA de este usuario? Tendrá que volver a configurarlo."
                                                class="rounded-lg bg-white/5 px-2.5 py-1.5 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-amber-500/40 hover:text-amber-300">
                                            Reset 2FA
                                        </button>
                                    @endif
                                    <button wire:click="openEdit({{ $user->id }})"
                                            class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-cyan-500/40 hover:text-cyan-300">
                                        Editar
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button wire:click="deleteUser({{ $user->id }})"
                                                wire:confirm="¿Eliminar a {{ $user->name }}? Se borrarán sus tatuajes, QRs y datos asociados."
                                                class="rounded-lg bg-white/5 px-2.5 py-1.5 text-xs font-medium text-gray-400 ring-1 ring-white/10 transition hover:ring-red-500/40 hover:text-red-400">
                                            Eliminar
                                        </button>
                                    @endif
                                </div>
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
            {{ $this->users->links() }}
        </div>
    </div>

    {{-- ── Edit Modal ───────────────────────────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative glass rounded-2xl w-full max-w-md">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4">
                    <h2 class="text-sm font-semibold text-white">Editar usuario</h2>
                    <button wire:click="$set('showModal', false)" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-gray-400">Nombre</label>
                        <input wire:model="userName" type="text"
                               class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                        @error('userName') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-gray-400">Email</label>
                        <input wire:model="userEmail" type="email"
                               class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                        @error('userEmail') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">Rol</label>
                            <select wire:model="userRole"
                                    class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-3 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
                                <option value="user">Usuario</option>
                                <option value="artist">Artista</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">Plan</label>
                            <select wire:model="userPlanId"
                                    class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-3 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
                                <option value="">Sin plan</option>
                                @foreach($this->plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">Ciudad</label>
                            <input wire:model="userCity" type="text"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('userCity') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-400">País</label>
                            <input wire:model="userCountry" type="text"
                                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                            @error('userCountry') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="userIsPremium" type="checkbox" id="userIsPremium" class="rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500/40">
                        <label for="userIsPremium" class="text-xs text-gray-400">Marcar como premium</label>
                    </div>
                </div>
                <div class="border-t border-white/[0.06] px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="$set('showModal', false)"
                            class="rounded-xl px-4 py-2 text-sm text-gray-500 ring-1 ring-white/10 transition hover:text-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="saveUser"
                            wire:loading.attr="disabled"
                            wire:target="saveUser"
                            class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveUser">Guardar</span>
                        <span wire:loading wire:target="saveUser">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
