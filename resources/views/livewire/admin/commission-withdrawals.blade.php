<div class="space-y-6">
    <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                    <th class="px-5 py-3">Usuario</th>
                    <th class="px-5 py-3">Importe</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3">Solicitado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->withdrawals as $w)
                    <tr class="border-b border-white/[0.04] transition hover:bg-white/[0.02]">
                        <td class="px-5 py-3">
                            <div class="font-medium text-white">{{ $w->user?->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $w->user?->email }}</div>
                        </td>
                        <td class="px-5 py-3 font-mono text-gray-300">{{ number_format($w->amount_cents / 100, 2) }} €</td>
                        <td class="px-5 py-3">
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-semibold ring-1',
                                'bg-amber-950/60 text-amber-300 ring-amber-500/20' => $w->status === 'requested',
                                'bg-cyan-950/60 text-cyan-300 ring-cyan-500/20' => $w->status === 'approved',
                                'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20' => $w->status === 'paid',
                                'bg-gray-900 text-gray-600 ring-white/10' => $w->status === 'rejected',
                            ])>
                                {{ ['requested' => 'Solicitado', 'approved' => 'Aprobado', 'paid' => 'Pagado', 'rejected' => 'Rechazado'][$w->status] ?? $w->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $w->created_at?->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right">
                            <span class="flex items-center justify-end gap-3 text-xs text-gray-500">
                                @if($w->status === 'requested')
                                    <button wire:click="approve({{ $w->id }})" class="hover:text-cyan-300 transition">Aprobar</button>
                                @endif
                                @if(in_array($w->status, ['requested', 'approved'], true))
                                    <button wire:click="markPaid({{ $w->id }})" class="hover:text-emerald-300 transition">Marcar pagado</button>
                                    <button wire:click="reject({{ $w->id }})"
                                            wire:confirm="¿Rechazar este retiro? El importe vuelve al saldo disponible del usuario."
                                            class="hover:text-red-400 transition">Rechazar</button>
                                @else
                                    <span class="text-gray-700">—</span>
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-600">
                            No hay solicitudes de retiro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
