<section class="mx-auto w-full max-w-6xl px-4 pb-16 pt-8 sm:px-6 sm:pt-12 lg:px-8 lg:pt-16" x-data="{ evolving: false }" x-init="setInterval(() => evolving = !evolving, 2400)">
    <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
        <div class="opacity-0 animate-fade-in-up">
            <p class="inline-flex rounded-full border border-cyan-300/35 bg-cyan-300/10 px-3 py-1 text-[11px] font-medium uppercase tracking-[0.2em] text-cyan-200">
                Evolucion Tatuaje Dinamico
            </p>
            <h1 class="mt-6 text-4xl font-semibold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                Tu tatuaje te acompana en tu
                <span class="bg-gradient-to-r from-cyan-300 via-fuchsia-300 to-violet-300 bg-clip-text text-transparent">evolucion personal</span>
            </h1>
            <p class="mt-5 max-w-xl text-base text-slate-300 sm:text-lg">
                Disena un QR permanente en piel que conecta con contenido digital que puedes reescribir sin borrar lo que ya eres.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="#mockup" class="group relative inline-flex items-center justify-center overflow-hidden rounded-full border border-cyan-300/40 bg-cyan-300/15 px-6 py-3 text-sm font-semibold text-cyan-50 transition hover:border-cyan-200 hover:bg-cyan-300/20">
                    <span class="pointer-events-none absolute inset-y-0 left-0 w-20 -skew-x-12 bg-gradient-to-r from-transparent via-white/40 to-transparent opacity-0 transition group-hover:animate-shimmer group-hover:opacity-100"></span>
                    Descubrir como
                </a>
                <a href="#valor" class="rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-slate-200 transition hover:border-white/40">
                    Ver propuesta de valor
                </a>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-2 text-sm text-slate-400">
                <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1">Tinta Estatica</span>
                <span class="text-cyan-300">-></span>
                <span x-show="!evolving" x-transition.opacity.duration.500ms class="rounded-full border border-fuchsia-300/40 bg-fuchsia-300/10 px-3 py-1 text-fuchsia-100">Tinta Estatica</span>
                <span x-show="evolving" x-transition.opacity.duration.500ms class="rounded-full border border-cyan-300/40 bg-cyan-300/10 px-3 py-1 text-cyan-100">Historia Viva</span>
            </div>
        </div>

        <div class="opacity-0 animate-fade-in-up [animation-delay:180ms]">
            <div class="relative mx-auto w-full max-w-md rounded-3xl border border-white/15 bg-white/5 p-5 backdrop-blur-xl">
                <div class="absolute -inset-px -z-10 rounded-3xl bg-gradient-to-r from-cyan-500/30 via-fuchsia-500/25 to-violet-500/30 blur"></div>
                <div class="rounded-2xl border border-white/10 bg-slate-950/80 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-300">Diseño vivo</p>
                    <h3 class="mt-2 text-xl font-semibold text-white">Historias que se reescriben sin desaparecer</h3>
                    <p class="mt-3 text-sm text-slate-300">Un mismo QR, multiples momentos. Mantienes identidad y evolucionas el mensaje.</p>

                    <div class="mt-5 flex items-center justify-center rounded-2xl border border-white/10 bg-slate-900/70 p-3">
                        <lottie-player
                            src="https://assets2.lottiefiles.com/packages/lf20_tll0j4bb.json"
                            background="transparent"
                            speed="1"
                            style="width: 180px; height: 180px;"
                            loop
                            autoplay
                        ></lottie-player>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
