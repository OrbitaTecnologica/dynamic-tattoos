<section id="mockup" class="mx-auto w-full max-w-6xl px-4 pb-20 sm:px-6 lg:px-8" wire:poll.5s="cycleScene">
    <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
        <div>
            <p class="text-xs uppercase tracking-[0.22em] text-cyan-300">Mockup Interactivo</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                No es solo tinta; es una historia viva sobre tu piel.
            </h2>
            <p class="mt-4 text-base text-slate-300 sm:text-lg">
                Simulacion de una vista movil donde el contenido de un mismo tatuaje evoluciona de foto a video sin cambiar el QR.
            </p>

            <div class="mt-8 grid gap-3 sm:grid-cols-2" x-data="{ hovered: null }">
                <article @mouseenter="hovered = 'foto'" @mouseleave="hovered = null" class="group rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur transition hover:border-cyan-300/50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-white">Galeria de viaje</p>
                        <span class="h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                    </div>
                    <p class="mt-2 text-sm text-slate-300">Hover: el QR brilla y previsualiza imagenes.</p>
                    <div x-show="hovered === 'foto'" x-transition class="mt-3 rounded-xl border border-cyan-300/40 bg-cyan-400/10 p-3 text-xs text-cyan-100">
                        Preview: 5 fotos nuevas sincronizadas.
                    </div>
                </article>

                <article @mouseenter="hovered = 'video'" @mouseleave="hovered = null" class="group rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur transition hover:border-fuchsia-300/50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-white">Video manifesto</p>
                        <span class="h-2.5 w-2.5 rounded-full bg-fuchsia-300"></span>
                    </div>
                    <p class="mt-2 text-sm text-slate-300">Hover: el QR reacciona y muestra clip.</p>
                    <div x-show="hovered === 'video'" x-transition class="mt-3 rounded-xl border border-fuchsia-300/40 bg-fuchsia-400/10 p-3 text-xs text-fuchsia-100">
                        Preview: video corto autoplay desactivado.
                    </div>
                </article>
            </div>
        </div>

        <div class="mx-auto w-full max-w-[280px] animate-float rounded-[2.2rem] border border-white/20 bg-slate-900/80 p-4 shadow-[0_0_50px_rgba(139,92,246,0.28)] backdrop-blur-xl">
            <div class="rounded-[1.7rem] border border-white/10 bg-slate-950 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs tracking-wide text-slate-400">/t/aB3xQ9mZ</p>
                    <button type="button" wire:click="cycleScene" class="rounded-full border border-white/15 px-3 py-1 text-[11px] text-slate-200 transition hover:border-cyan-300 hover:text-cyan-200">
                        Cambiar
                    </button>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900 p-3">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(34,211,238,0.2),_transparent_45%)]"></div>
                    <div class="relative grid grid-cols-[1fr_auto] gap-3">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-cyan-300">Contenido activo</p>
                            @if ($scene === 'photo')
                                <div wire:key="scene-photo" wire:transition>
                                    <p class="mt-2 text-sm font-semibold text-white">Foto destacada</p>
                                    <p class="text-xs text-slate-300">Evolucion de 2026</p>
                                    <div class="mt-3 h-24 rounded-xl bg-gradient-to-br from-cyan-500/35 via-violet-500/30 to-fuchsia-500/35"></div>
                                </div>
                            @else
                                <div wire:key="scene-video" wire:transition>
                                    <p class="mt-2 text-sm font-semibold text-white">Video destacado</p>
                                    <p class="text-xs text-slate-300">Nuevo manifiesto visual</p>
                                    <div class="mt-3 flex h-24 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500/35 via-violet-500/30 to-cyan-500/35">
                                        <span class="rounded-full border border-white/30 px-2 py-1 text-xs text-white">Play</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="relative h-24 w-24 rounded-xl border border-white/15 bg-slate-900/70 p-2 transition duration-300" x-data="{ glow: false }" @mouseenter="glow = true" @mouseleave="glow = false" :class="glow ? 'animate-pulse-soft border-cyan-300/60' : ''">
                            <div class="grid h-full w-full grid-cols-6 gap-[2px]">
                                @for ($i = 0; $i < 36; $i++)
                                    <span class="rounded-[2px] bg-white/90"></span>
                                @endfor
                            </div>
                            <div x-show="glow" x-transition class="pointer-events-none absolute -top-2 left-1/2 w-24 -translate-x-1/2 rounded-full border border-cyan-300/30 bg-cyan-400/15 px-2 py-1 text-center text-[10px] text-cyan-100">
                                QR en vivo
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-white/10 bg-white/5 p-3">
                    <p class="text-xs text-slate-300">
                        Transicion suave entre formatos usando estado reactivo con Livewire.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
