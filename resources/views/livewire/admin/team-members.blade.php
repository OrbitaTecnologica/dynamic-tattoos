<div class="space-y-5">

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por nombre, email del miembro o email del propietario…"
                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>
        <select wire:model.live="filterStatus"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los estados</option>
            <option value="active">Activo</option>
            <option value="pending">Pendiente</option>
        </select>
        <select wire:model.live="filterRole"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los roles</option>
            <option value="owner">Owner</option>
            <option value="editor">Editor</option>
            <option value="viewer">Viewer</option>
        </select>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Propietario</th>
                        <th class="px-5 py-3.5">Miembro</th>
                        <th class="px-5 py-3.5">Rol</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5">Invitado</th>
                        <th class="px-5 py-3.5">Última actividad</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->members as $member)
                        <tr class="hover:bg-white/[0.02] transition group">

                            {{-- Propietario --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-violet-500/30 text-xs font-bold text-white">
                                        {{ strtoupper(substr($member->owner?->name ?? $member->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white text-sm">{{ $member->owner?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->owner?->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Miembro --}}
                            <td class="px-5 py-4">
                                <div>
                                    <p class="font-medium text-white text-sm">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                    @if($member->member)
                                        <p class="text-xs text-gray-600 mt-0.5">{{ $member->member->email }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Rol --}}
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                                    'bg-cyan-950/60 text-cyan-300 ring-cyan-500/20'       => $member->role === 'owner',
                                    'bg-violet-950/60 text-violet-300 ring-violet-500/20' => $member->role === 'editor',
                                    'bg-white/5 text-gray-400 ring-white/10'              => $member->role === 'viewer',
                                ])>
                                    {{ ucfirst($member->role) }}
                                </span>
                            </td>

                            {{-- Estado --}}
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                                    'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20' => $member->status === 'active',
                                    'bg-amber-950/60 text-amber-300 ring-amber-500/20'       => $member->status === 'pending',
                                ])>
                                    {{ $member->status === 'active' ? 'Activo' : 'Pendiente' }}
                                </span>
                            </td>

                            {{-- Invitado --}}
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $member->invited_at?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Última actividad --}}
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $member->last_active_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4 text-right">
                                <button wire:click="revoke({{ $member->id }})"
                                        wire:confirm="¿Revocar el acceso de este miembro? Esta acción no se puede deshacer."
                                        wire:loading.attr="disabled"
                                        wire:target="revoke({{ $member->id }})"
                                        class="rounded-lg bg-red-950/40 px-3 py-1.5 text-xs font-medium text-red-400 ring-1 ring-red-500/20 transition hover:ring-red-500/40 hover:text-red-300 disabled:opacity-50">
                                    Revocar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron miembros de equipo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->members->links() }}
        </div>
    </div>

</div>
