<!DOCTYPE html>
<html lang="es" class="bg-[#0b0d12]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Studio · dtattoos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { color-scheme: dark; }
        body {
            background:
                radial-gradient(1200px 600px at 80% -10%, rgba(180,0,0,0.28), transparent 60%),
                radial-gradient(900px 500px at -10% 20%, rgba(220,0,0,0.14), transparent 60%),
                #0b0d12;
        }
        .glass {
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
        }
        .shape-tile {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all .18s ease;
        }
        .shape-tile:hover { border-color: rgba(255,255,255,0.25); transform: translateY(-1px); }
        .shape-tile.active {
            border-color: #ff3333;
            background: linear-gradient(180deg, rgba(180,0,0,0.24), rgba(180,0,0,0.08));
            box-shadow: 0 0 0 3px rgba(180,0,0,0.22), 0 8px 24px -12px rgba(180,0,0,0.60);
        }
        .step-num {
            background: linear-gradient(135deg, #b30000, #ff1a1a);
            box-shadow: 0 6px 18px -6px rgba(180,0,0,0.70);
        }
        .btn-primary {
            background: linear-gradient(135deg, #b30000, #ff1a1a);
            box-shadow: 0 10px 30px -10px rgba(180,0,0,0.70);
            transition: all .18s ease;
        }
        .btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 14px 36px -10px rgba(180,0,0,0.85); }
        .btn-secondary {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            transition: all .18s ease;
        }
        .btn-secondary:hover:not(:disabled) { background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.20); }
        .input {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.10);
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .input:focus { border-color: #ff3333; box-shadow: 0 0 0 4px rgba(180,0,0,0.22); outline: none; }
        .qr-canvas svg, .qr-canvas canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }
        .checker {
            background-image:
                linear-gradient(45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.03) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.03) 75%);
            background-size: 16px 16px;
            background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
        }
        .ring-dot { box-shadow: 0 0 0 2px #ff3333, 0 0 0 4px rgba(180,0,0,0.30); }
    </style>
</head>
<body class="min-h-screen text-slate-200 antialiased font-sans">

<div
    x-data="qrBuilder({
        storeUrl: @js(route('qr.store')),
        emailUrl: @js(route('qr.email')),
        baseUrl: @js($baseUrl),
        initialSlug: @js($initialSlug),
        csrf: document.querySelector('meta[name=csrf-token]').content,
    })"
    x-init="init()"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14"
>
    {{-- Botón volver al dashboard --}}
    <div class="fixed top-4 left-4 z-50">
        <a href="{{ route('dashboard') }}"
           class="group inline-flex items-center gap-2 px-4 py-2 rounded-full
                  bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-rose-500/40
                  backdrop-blur-md transition-all duration-200 shadow-lg shadow-black/20">
            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            <span class="hidden sm:flex flex-col leading-tight">
                <span class="text-[13px] font-medium text-white">Dashboard</span>
                <span class="text-[10px] text-slate-400 group-hover:text-rose-300 transition-colors">Volver al panel</span>
            </span>
            <span class="sm:hidden text-[13px] font-medium text-white">Dashboard</span>
        </a>
    </div>

    {{-- Botón acceso a panel de usuario --}}
    <div class="fixed top-4 right-4 z-50">
        <a href="{{ route('profile.index') }}"
           class="group inline-flex items-center gap-2.5 px-4 py-2 rounded-full
                  bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-rose-500/40
                  backdrop-blur-md transition-all duration-200 shadow-lg shadow-black/20">
            <span class="hidden sm:flex flex-col leading-tight">
                <span class="text-[13px] font-medium text-white">Mi cuenta</span>
                <span class="text-[10px] text-slate-400 group-hover:text-rose-300 transition-colors">Panel de usuario</span>
            </span>
            <span class="sm:hidden text-[13px] font-medium text-white">Mi cuenta</span>
            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>
    </div>

    <header class="mb-10 lg:mb-14 text-center">
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight bg-gradient-to-b from-white to-slate-400 bg-clip-text text-transparent">
            Crea tu Código QR personalizado
        </h1>
        <p class="text-slate-400 mt-3 max-w-xl mx-auto">
            Diseña un QR único para tu URL. Personaliza la forma del cuerpo, el marco y el ojo central.
        </p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

        {{-- Panel personalización --}}
        <section class="lg:col-span-3 glass rounded-2xl p-6 sm:p-8 space-y-10">

            {{-- Paso 1 · URL --}}
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="step-num w-7 h-7 rounded-full grid place-items-center text-sm font-semibold text-white">1</span>
                    <h2 class="text-lg font-semibold text-white">Define tu enlace</h2>
                </div>
                <label for="slug" class="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-2">Destino del QR</label>

                <div class="input flex items-stretch w-full rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-rose-500/40">
                    <span class="flex items-center gap-2.5 px-5 py-3 bg-white/[0.04] border-r border-white/[0.06] text-slate-400 text-sm select-none">
                        <svg class="w-4 h-4 opacity-60 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <span class="font-mono text-[13px] tracking-tight whitespace-nowrap">{{ str_replace(['https://','http://'], '', $baseUrl) }}/</span>
                    </span>
                    <input
                        id="slug"
                        type="text"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="mi-tatuaje"
                        x-model.debounce.300ms="form.slug"
                        @input="form.slug = $event.target.value.replace(/\s+/g,'-').replace(/[^A-Za-z0-9._~\-\/]/g,'')"
                        class="flex-1 min-w-0 bg-transparent border-0 focus:outline-none pl-5 pr-4 py-3 text-slate-100 placeholder:text-slate-500 font-mono text-[13px]"
                    >
                    <span class="pr-3 flex items-center pointer-events-none" x-show="form.slug">
                        <svg x-show="urlValid" class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="!urlValid" class="w-4 h-4 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                </div>

                <p class="mt-2 text-[11px] text-slate-500">
                    URL final: <span class="font-mono text-slate-300" x-text="form.url || '{{ $baseUrl }}/…'"></span>
                </p>
                <p x-show="errors.slug || errors.url" x-text="errors.slug || errors.url" class="text-sm text-rose-400 mt-2"></p>

                {{-- Aviso de longitud recomendada --}}
                <div x-show="form.slug.length > 8" x-transition
                     class="mt-4 flex items-start gap-2.5 rounded-lg border border-rose-500/25 bg-rose-500/[0.06] px-3.5 py-2.5">
                    <svg class="w-3.5 h-3.5 mt-0.5 shrink-0 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 3.5h.01"/>
                    </svg>
                    <p class="text-[11.5px] leading-snug text-rose-100/90">
                        Has superado los <strong class="text-white">8 caracteres</strong> recomendados
                        (<span class="font-mono text-rose-300" x-text="form.slug.length"></span>).
                        Solo garantizamos la correcta legibilidad del QR dentro de ese límite.
                    </p>
                </div>

                {{-- Aviso de inmutabilidad --}}
                <div class="mt-8 relative overflow-hidden rounded-xl border border-white/[0.06] bg-gradient-to-br from-white/[0.03] to-white/[0.01]">
                    <span aria-hidden="true" class="absolute inset-y-0 left-0 w-[3px]" style="background:linear-gradient(180deg,#ff4d4d,#b30000);"></span>
                    <div class="p-4 pl-5">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="shrink-0 flex items-center justify-center w-5 h-5 rounded-md border border-rose-500/20"
                                  style="background:radial-gradient(circle at 30% 30%, rgba(255,77,77,0.18), rgba(179,0,0,0.05));">
                                <svg class="w-2.5 h-2.5 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 3.5h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-rose-400 leading-none">Importante</span>
                            <span class="h-px flex-1 bg-gradient-to-r from-rose-500/30 to-transparent"></span>
                        </div>
                        <p class="pl-7 text-[12.5px] leading-relaxed text-slate-300">
                            Una vez generado el QR de forma definitiva, <strong class="text-white font-semibold">la URL no podrá modificarse</strong>. Asegúrate de que el destino es correcto antes de guardar.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Paso 2 · Color --}}
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="step-num w-7 h-7 rounded-full grid place-items-center text-sm font-semibold text-white">2</span>
                    <h2 class="text-lg font-semibold text-white">Elige el color</h2>
                </div>
                <div class="flex items-center gap-4">
                    <button type="button" class="w-12 h-12 rounded-xl bg-black ring-dot relative" title="Negro sólido">
                        <svg class="absolute inset-0 m-auto w-5 h-5 text-white/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                    <div>
                        <p class="text-sm font-medium text-slate-200">Negro sólido</p>
                        <p class="text-xs text-slate-500">Más colores y gradientes próximamente</p>
                    </div>
                </div>
            </div>

            {{-- Paso 3 · Diseño --}}
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <span class="step-num w-7 h-7 rounded-full grid place-items-center text-sm font-semibold text-white">3</span>
                    <h2 class="text-lg font-semibold text-white">Personaliza el diseño</h2>
                </div>

                <div class="space-y-7">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Forma del cuerpo</h3>
                        <div class="grid grid-cols-4 gap-2.5">
                            <template x-for="opt in dotsTypes" :key="opt.value">
                                <button type="button"
                                    @click="form.dots_type = opt.value"
                                    :class="form.dots_type === opt.value ? 'shape-tile active' : 'shape-tile'"
                                    class="rounded-xl px-3 py-2 flex items-center justify-center gap-2"
                                    :title="opt.label">
                                    <span class="w-4 h-4 shrink-0 grid place-items-center text-slate-200" x-html="opt.icon"></span>
                                    <span class="text-[11px] text-slate-300 leading-none truncate" x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Marco del ojo</h3>
                        <div class="grid grid-cols-3 gap-2.5 max-w-md">
                            <template x-for="opt in cornersSquareTypes" :key="opt.value">
                                <button type="button"
                                    @click="form.corners_square_type = opt.value"
                                    :class="form.corners_square_type === opt.value ? 'shape-tile active' : 'shape-tile'"
                                    class="aspect-square rounded-xl p-2 flex flex-col items-center justify-center gap-1.5"
                                    :title="opt.label">
                                    <span class="w-8 h-8 grid place-items-center text-slate-200" x-html="opt.icon"></span>
                                    <span class="text-[10px] text-slate-400 leading-tight text-center" x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Ojo (centro)</h3>
                        <div class="grid grid-cols-3 gap-2.5 max-w-xs">
                            <template x-for="opt in cornersDotTypes" :key="opt.value">
                                <button type="button"
                                    @click="form.corners_dot_type = opt.value"
                                    :class="form.corners_dot_type === opt.value ? 'shape-tile active' : 'shape-tile'"
                                    class="aspect-square rounded-xl p-2 flex flex-col items-center justify-center gap-1.5"
                                    :title="opt.label">
                                    <span class="w-7 h-7 grid place-items-center text-slate-200" x-html="opt.icon"></span>
                                    <span class="text-[10px] text-slate-400 leading-tight text-center" x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Preview --}}
        <aside class="lg:col-span-2">
            <div class="glass rounded-2xl p-6 sm:p-8 lg:sticky lg:top-8">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-white">Vista previa</h2>
                    <span class="text-xs text-slate-500" x-text="form.url ? 'En vivo' : 'En espera'"></span>
                </div>

                <div class="relative w-full aspect-square checker rounded-2xl border border-white/10 overflow-hidden">
                    <div
                        x-ref="qr"
                        class="qr-canvas absolute inset-4 bg-white rounded-xl shadow-2xl shadow-black/40 overflow-hidden"
                        x-show="form.url"
                        x-transition.opacity
                    ></div>

                    <!-- estado vacío -->
                    <div x-show="!form.url" class="absolute inset-0 flex flex-col items-center justify-center text-center px-6">
                        <div class="w-14 h-14 rounded-2xl glass grid place-items-center mb-3">
                            <svg class="w-7 h-7 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                                <path d="M14 14h3v3h-3zM20 14v7M14 20h7"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-300 font-medium">Sin contenido todavía</p>
                        <p class="text-xs text-slate-500 mt-1">Introduce una URL para previsualizar tu QR</p>
                    </div>
                </div>

                <div class="mt-6 space-y-2.5">
                    <button type="button" @click="download('png')" :disabled="!form.url"
                        class="btn-primary w-full text-white rounded-xl py-3 font-medium disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                        Descargar PNG
                    </button>

                    <!-- Enviar a -->
                    <div>
                        <button type="button" @click="emailOpen = !emailOpen" :disabled="!form.url"
                            class="btn-secondary w-full text-slate-100 rounded-xl py-3 font-medium disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Enviar a
                            <svg class="w-3 h-3 ml-auto transition-transform" :class="emailOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="emailOpen" x-transition class="mt-2 glass rounded-xl p-4 space-y-3">
                            <label class="block text-xs text-slate-400 mb-1">Dirección de correo electrónico</label>
                            <input
                                type="email"
                                x-model="emailAddress"
                                placeholder="ejemplo@correo.com"
                                class="input w-full rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-600 focus:outline-none"
                            />
                            <button type="button" @click="sendEmail()" :disabled="!emailAddress || sendingEmail"
                                class="btn-primary w-full text-white rounded-lg py-2.5 text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <template x-if="!sendingEmail">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                </template>
                                <span x-text="sendingEmail ? 'Enviando…' : 'Enviar QR'"></span>
                            </button>
                            <p x-show="emailMessage" x-text="emailMessage"
                                :class="emailMessageType === 'error' ? 'text-rose-400' : 'text-emerald-400'"
                                class="text-xs text-center"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" @click="download('svg')" :disabled="!form.url"
                            class="btn-secondary text-slate-100 rounded-xl py-2.5 font-medium disabled:opacity-40 disabled:cursor-not-allowed text-sm">
                            Descargar SVG
                        </button>
                        <button type="button" @click="save()" :disabled="!form.url || saving"
                            class="btn-secondary text-slate-100 rounded-xl py-2.5 font-medium disabled:opacity-40 disabled:cursor-not-allowed text-sm">
                            <span x-show="!saving">Guardar QR</span>
                            <span x-show="saving">Guardando…</span>
                        </button>
                    </div>
                    <p x-show="message" x-text="message"
                        :class="messageType === 'error' ? 'text-rose-400' : 'text-emerald-400'"
                        class="text-sm text-center pt-2"></p>
                </div>
            </div>
        </aside>
    </div>

    <!-- Historial de QRs guardados -->
    <section x-show="history.length" x-transition class="mt-24" style="margin-top:6rem;padding-top:2rem;">

        {{-- Cabecera --}}
        <div class="flex items-center gap-4 mb-8">
            <div class="flex-1 h-px bg-white/[0.07]"></div>
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-sm font-semibold uppercase tracking-widest text-slate-400">Historial guardado</h2>
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold text-white"
                      style="background:linear-gradient(135deg,#b30000,#ff1a1a)"
                      x-text="history.length"></span>
            </div>
            <div class="flex-1 h-px bg-white/[0.07]"></div>
        </div>

        {{-- Grid de tarjetas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="item in history" :key="item.id">
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-white/[0.08] bg-white/[0.03] backdrop-blur-sm transition-all duration-200 hover:-translate-y-1 hover:border-white/[0.15] hover:bg-white/[0.06] hover:shadow-2xl hover:shadow-black/40">

                    {{-- Barra de color superior --}}
                    <div class="h-[3px] w-full shrink-0" :style="'background:linear-gradient(90deg,' + item.color + ',' + item.color + '88)'"></div>

                    <div class="flex flex-col flex-1 px-6 pt-5 pb-5 gap-4">

                        {{-- URL --}}
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 shrink-0 relative flex items-center justify-center w-7 h-7 rounded-lg bg-white/[0.05] border border-white/[0.08] overflow-hidden">
                                <img :src="faviconUrl(item.url)"
                                     :alt="'Favicon ' + item.url"
                                     class="w-4 h-4 object-contain"
                                     loading="lazy"
                                     referrerpolicy="no-referrer"
                                     x-on:error="$event.target.style.display='none'; $event.target.nextElementSibling.style.display='block'"/>
                                <svg class="w-3.5 h-3.5 text-slate-400" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656-5.656M10.172 13.828a4 4 0 01-5.656-5.656l3-3a4 4 0 015.656 5.656"/>
                                </svg>
                            </span>
                            <p class="text-[13px] font-medium text-slate-200 leading-snug break-all line-clamp-2 flex-1" x-text="item.url"></p>
                        </div>

                        {{-- Badges de configuración --}}
                        <div class="flex flex-wrap gap-1.5 px-0.5">
                            <span class="inline-flex items-center gap-1.5 text-[10px] px-2.5 py-1 rounded-md font-semibold tracking-wide uppercase bg-white/[0.05] text-slate-400 border border-white/[0.08]">
                                <svg class="w-2.5 h-2.5 opacity-60" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                <span x-text="label(item.dots_type)"></span>
                            </span>
                            <span class="text-[10px] px-2.5 py-1 rounded-md font-semibold tracking-wide uppercase bg-white/[0.05] text-slate-400 border border-white/[0.08]" x-text="label(item.corners_square_type)"></span>
                            <span class="text-[10px] px-2.5 py-1 rounded-md font-semibold tracking-wide uppercase bg-white/[0.05] text-slate-400 border border-white/[0.08]" x-text="label(item.corners_dot_type)"></span>
                        </div>

                        {{-- Footer tarjeta --}}
                        <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <svg class="w-3 h-3 text-slate-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                                </svg>
                                <span class="text-[11px] text-slate-600 truncate" x-text="formatDate(item.created_at)"></span>
                            </div>
                            <button @click="loadFromHistory(item)"
                                class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold text-white rounded-lg px-3.5 py-1.5 transition-all duration-150"
                                style="background:linear-gradient(135deg,rgba(179,0,0,0.70),rgba(255,26,26,0.60));box-shadow:0 4px 14px -4px rgba(180,0,0,0.55);border:1px solid rgba(255,80,80,0.25);"
                                onmouseover="this.style.boxShadow='0 6px 20px -4px rgba(180,0,0,0.80)';this.style.filter='brightness(1.15)'"
                                onmouseout="this.style.boxShadow='0 4px 14px -4px rgba(180,0,0,0.55)';this.style.filter=''">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4 9a9 9 0 0114.7-3.7L20 9M4 15l1.3 3.7A9 9 0 0020 15"/>
                                </svg>
                                Cargar
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <footer class="mt-12 text-center text-xs text-slate-600">
        &copy; {{ date('Y') }} Dynamic Tattoos &mdash; Todos los derechos reservados.
    </footer>
</div>

<script>
    const SHAPES = {
        dots: {
            'square':          `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>`,
            'dots':            `<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="6" cy="6" r="3.2"/><circle cx="18" cy="6" r="3.2"/><circle cx="6" cy="18" r="3.2"/><circle cx="18" cy="18" r="3.2"/></svg>`,
            'rounded':         `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>`,
            'extra-rounded':   `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="3.5"/><rect x="14" y="3" width="7" height="7" rx="3.5"/><rect x="3" y="14" width="7" height="7" rx="3.5"/><rect x="14" y="14" width="7" height="7" rx="3.5"/></svg>`,
            'classy':          `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 6a3 3 0 013-3h4v7H3z"/><path d="M14 3h7v4a3 3 0 01-3 3h-4z"/><path d="M3 14h7v4a3 3 0 01-3 3H3z"/><path d="M18 14h3v7h-7v-4a3 3 0 013-3z"/></svg>`,
            'classy-rounded':  `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 3h4v7H3V6a3 3 0 013-3z"/><path d="M14 3h4a3 3 0 013 3v4h-7z"/><path d="M3 14h7v4a3 3 0 01-3 3H3z"/><path d="M14 14h7v4a3 3 0 01-3 3h-4z"/></svg>`,
        },
        cornersSquare: {
            'square':          `<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="3" width="26" height="26"/></svg>`,
            'dot':             `<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3"><circle cx="16" cy="16" r="13"/></svg>`,
            'extra-rounded':   `<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="3" width="26" height="26" rx="8"/></svg>`,
        },
        cornersDot: {
            'square': `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="5" y="5" width="14" height="14"/></svg>`,
            'dot':    `<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="7"/></svg>`,
        },
    };

    const LABELS = {
        'square': 'Cuadrado',
        'dots': 'Puntos',
        'rounded': 'Redondeado',
        'extra-rounded': 'Muy redondeado',
        'classy': 'Elegante',
        'classy-rounded': 'Elegante redondeado',
        'dot': 'Punto',
    };
    window.QR_LABELS = LABELS;

    function buildOptions(values, iconSet) {
        return values.map(v => ({ value: v, label: LABELS[v] || v, icon: iconSet[v] || '' }));
    }

    function qrBuilder(config) {
        return {
            baseUrl: config.baseUrl,
            dotsTypes: buildOptions(@json($dotsTypes), SHAPES.dots),
            cornersSquareTypes: buildOptions(@json($cornersSquareTypes), SHAPES.cornersSquare),
            cornersDotTypes: buildOptions(@json($cornersDotTypes), SHAPES.cornersDot),
            form: {
                slug: config.initialSlug || '',
                url: config.initialSlug ? `${config.baseUrl}/${config.initialSlug}` : '',
                color: '#000000',
                dots_type: 'rounded',
                corners_square_type: 'extra-rounded',
                corners_dot_type: 'dot',
            },
            errors: {},
            saving: false,
            message: '',
            messageType: '',
            emailOpen: false,
            emailAddress: '',
            sendingEmail: false,
            emailMessage: '',
            emailMessageType: '',
            urlValid: false,
            history: @json($history),
            qr: null,

            init() {
                this.qr = new window.QRCodeStyling({
                    width: 600,
                    height: 600,
                    type: 'svg',
                    data: 'https://example.com',
                    margin: 0,
                    qrOptions: { errorCorrectionLevel: 'Q' },
                    dotsOptions: { color: this.form.color, type: this.form.dots_type },
                    cornersSquareOptions: { color: this.form.color, type: this.form.corners_square_type },
                    cornersDotOptions: { color: this.form.color, type: this.form.corners_dot_type },
                    backgroundOptions: { color: '#ffffff' },
                });
                this.qr.append(this.$refs.qr);

                this.$watch('form', () => this.update(), { deep: true });
                this.$watch('form.slug', (val) => {
                    const clean = (val || '').replace(/^\/+/, '');
                    this.form.url = clean ? `${this.baseUrl}/${clean}` : '';
                    this.urlValid = clean.length > 0 && /^[A-Za-z0-9._~\-\/]+$/.test(clean);
                });
                this.update();
            },

            update() {
                if (!this.qr) return;
                this.qr.update({
                    data: this.form.url || ' ',
                    dotsOptions: { color: this.form.color, type: this.form.dots_type },
                    cornersSquareOptions: { color: this.form.color, type: this.form.corners_square_type },
                    cornersDotOptions: { color: this.form.color, type: this.form.corners_dot_type },
                });
            },

            download(ext) {
                if (!this.form.url) return;
                this.qr.download({ name: 'qr-code', extension: ext });
            },

            label(value) {
                return (window.QR_LABELS && window.QR_LABELS[value]) || value;
            },

            faviconUrl(url) {
                try {
                    const host = new URL(url).hostname;
                    return `https://www.google.com/s2/favicons?domain=${encodeURIComponent(host)}&sz=64`;
                } catch (e) {
                    return '';
                }
            },

            loadFromHistory(item) {
                const prefix = this.baseUrl + '/';
                this.form.slug = item.url && item.url.startsWith(prefix)
                    ? item.url.slice(prefix.length)
                    : item.url;
                this.form.url               = item.url;
                this.form.color             = item.color;
                this.form.dots_type         = item.dots_type;
                this.form.corners_square_type = item.corners_square_type;
                this.form.corners_dot_type  = item.corners_dot_type;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            formatDate(dateStr) {
                return new Date(dateStr).toLocaleDateString('es-ES', {
                    day: '2-digit', month: 'short', year: 'numeric'
                });
            },

            async sendEmail() {
                if (!this.emailAddress || !this.form.url) return;
                this.sendingEmail = true;
                this.emailMessage = '';
                try {
                    const blob = await this.qr.getRawData('png');
                    const base64 = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = reject;
                        reader.readAsDataURL(blob);
                    });
                    const res = await fetch(config.emailUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': config.csrf,
                        },
                        body: JSON.stringify({
                            email: this.emailAddress,
                            qr_image: base64,
                            url: this.form.url,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.emailMessage = data.message || 'Error al enviar';
                        this.emailMessageType = 'error';
                    } else {
                        this.emailMessage = data.message;
                        this.emailMessageType = 'success';
                        this.emailAddress = '';
                    }
                } catch (e) {
                    this.emailMessage = 'Error de red';
                    this.emailMessageType = 'error';
                } finally {
                    this.sendingEmail = false;
                }
            },

            async save() {
                this.saving = true;
                this.message = '';
                this.errors = {};
                try {
                    const res = await fetch(config.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': config.csrf,
                        },
                        body: JSON.stringify(this.form),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        if (data.errors) {
                            Object.entries(data.errors).forEach(([k, v]) => this.errors[k] = v[0]);
                        }
                        this.message = data.message || 'Error al guardar';
                        this.messageType = 'error';
                    } else {
                        this.message = data.message;
                        this.messageType = 'success';
                        this.history.unshift(data.qr);
                        if (this.history.length > 20) this.history.pop();
                    }
                } catch (e) {
                    this.message = 'Error de red';
                    this.messageType = 'error';
                } finally {
                    this.saving = false;
                }
            },
        };
    }
</script>
</body>
</html>
