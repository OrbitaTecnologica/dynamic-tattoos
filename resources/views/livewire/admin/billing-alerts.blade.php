<div wire:poll.20s class="space-y-8">

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="glass rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-xs font-semibold uppercase tracking-widest text-cyan-400">Jobs Payments</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->pendingPaymentsJobs) }}</span>
            <span class="text-xs text-gray-500">Pendientes en cola</span>
        </div>

        <div class="glass rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-xs font-semibold uppercase tracking-widest text-violet-400">Jobs Notifications</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->pendingNotificationsJobs) }}</span>
            <span class="text-xs text-gray-500">Pendientes en cola</span>
        </div>

        <div class="glass rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-xs font-semibold uppercase tracking-widest text-rose-400">Fallos Ultima Hora</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->failedJobsLastHour) }}</span>
            <span class="text-xs text-gray-500">Total fallos recientes</span>
        </div>

        <div class="glass rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-xs font-semibold uppercase tracking-widest text-emerald-400">Webhooks Ultima Hora</span>
            <span class="text-4xl font-bold text-white tabular-nums">{{ number_format($this->webhooksLastHour) }}</span>
            <span class="text-xs text-gray-500">Eventos procesados</span>
        </div>
    </div>

    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400">Alertas Activas</h3>
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ count($this->activeAlerts) > 0 ? 'bg-amber-500/10 text-amber-300 ring-1 ring-amber-500/20' : 'bg-emerald-500/10 text-emerald-300 ring-1 ring-emerald-500/20' }}">
                {{ count($this->activeAlerts) > 0 ? count($this->activeAlerts) . ' alerta(s)' : 'Sin alertas' }}
            </span>
        </div>

        <div class="space-y-3">
            @forelse($this->activeAlerts as $alert)
                <div class="rounded-xl border px-4 py-3 {{ $alert['severity'] === 'critical' ? 'border-red-500/40 bg-red-500/10' : 'border-amber-500/40 bg-amber-500/10' }}">
                    <p class="text-sm font-semibold {{ $alert['severity'] === 'critical' ? 'text-red-200' : 'text-amber-200' }}">{{ $alert['title'] }}</p>
                    <p class="text-xs text-gray-300 mt-1">{{ $alert['description'] }}</p>
                </div>
            @empty
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3">
                    <p class="text-sm font-semibold text-emerald-200">Sistema estable</p>
                    <p class="text-xs text-emerald-100/90 mt-1">No se detectan alertas de billing en este momento.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400">Failed Jobs Recientes</h3>
                <span class="text-xs text-gray-500">Total acumulado: {{ number_format($this->failedJobsTotal) }}</span>
            </div>

            <div class="space-y-2">
                @forelse($this->recentFailedJobs as $job)
                    <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-white">{{ $job->queue }}</p>
                            <span class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($job->failed_at)->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-300">{{ $job->exception_summary }}</p>
                        <p class="mt-1 text-[11px] text-gray-500 font-mono">{{ $job->uuid }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No hay jobs fallidos registrados.</p>
                @endforelse
            </div>
        </section>

        <section class="glass rounded-2xl p-6">
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">Webhooks Recientes</h3>

            <div class="space-y-2">
                @forelse($this->recentWebhookEvents as $event)
                    <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-white">{{ $event->type }}</p>
                            <span class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($event->processed_at)->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500 font-mono break-all">{{ $event->stripe_event_id }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No hay webhooks procesados aun.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="flex items-center justify-end gap-2 text-xs text-gray-700">
        <span class="h-1.5 w-1.5 rounded-full bg-cyan-500 animate-pulse"></span>
        Actualizacion automatica cada 20 s
    </div>
</div>
