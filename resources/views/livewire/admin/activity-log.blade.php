<div class="space-y-5">

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por descripción…"
                   class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>
        <select wire:model.live="filterLogName"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los registros</option>
            @foreach($this->logNames as $name)
                <option value="{{ $name }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5 whitespace-nowrap">Fecha / Hora</th>
                        <th class="px-5 py-3.5">Registro</th>
                        <th class="px-5 py-3.5">Descripción</th>
                        <th class="px-5 py-3.5">Autor</th>
                        <th class="px-5 py-3.5">Sujeto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->activities as $activity)
                        @php
                            $causer = $activity->causer;
                            $causerLabel = $causer instanceof \App\Models\User
                                ? $causer->name . "\n" . $causer->email
                                : null;

                            $subjectLabel = $activity->subject_type
                                ? class_basename($activity->subject_type) . ' #' . $activity->subject_id
                                : null;
                        @endphp
                        <tr class="group hover:bg-white/[0.02] transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="text-xs tabular-nums text-gray-400">
                                    {{ $activity->created_at?->format('d/m/Y') }}
                                </span>
                                <p class="text-xs tabular-nums text-gray-600">
                                    {{ $activity->created_at?->format('H:i') }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                @if($activity->log_name)
                                    @php
                                        $logName = $activity->log_name;
                                        $badgeClass = match(true) {
                                            str_contains($logName, 'auth')       => 'bg-cyan-950/60 text-cyan-300 ring-cyan-500/20',
                                            str_contains($logName, 'billing')    => 'bg-emerald-950/60 text-emerald-300 ring-emerald-500/20',
                                            str_contains($logName, 'tattoo')     => 'bg-violet-950/60 text-violet-300 ring-violet-500/20',
                                            str_contains($logName, 'user')       => 'bg-amber-950/60 text-amber-300 ring-amber-500/20',
                                            str_contains($logName, 'security')   => 'bg-red-950/60 text-red-300 ring-red-500/20',
                                            default                              => 'bg-white/5 text-gray-400 ring-white/10',
                                        };
                                    @endphp
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $badgeClass }}">
                                        {{ $logName }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-300 max-w-xs">
                                {{ $activity->description ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($causerLabel)
                                    @php [$name, $email] = explode("\n", $causerLabel, 2); @endphp
                                    <p class="text-xs font-medium text-white">{{ $name }}</p>
                                    <p class="text-xs text-gray-500">{{ $email }}</p>
                                @elseif($causer)
                                    <span class="text-xs text-gray-400">{{ class_basename($activity->causer_type) }} #{{ $activity->causer_id }}</span>
                                @else
                                    <span class="text-xs text-gray-600">sistema</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($subjectLabel)
                                    <span class="font-mono text-xs text-gray-400">{{ $subjectLabel }}</span>
                                @else
                                    <span class="text-xs text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-600">
                                No hay actividad registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->activities->links() }}
        </div>
    </div>

</div>
