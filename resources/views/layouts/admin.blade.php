<!doctype html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — Dynamic Tattoos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Solo CSS: Livewire 3 ya incluye y arranca Alpine (@livewireScripts).
         Cargar app.js aquí arrancaría un segundo Alpine y rompería wire:click/wire:model. --}}
    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        :root {
            --bg-base: #050505;
            --neon-cyan: #00f5ff;
            --neon-violet: #8b5cf6;
        }
        body { background-color: var(--bg-base); font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.07);
        }
        .glass-hover:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(0,245,255,0.2);
        }
        .neon-text {
            background: linear-gradient(90deg, #00f5ff, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .neon-border { border-color: rgba(0,245,255,0.35); }
        .sidebar-link.active {
            background: rgba(0,245,255,0.08);
            border-left: 2px solid #00f5ff;
            color: #00f5ff;
        }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(139,92,246,0.4); border-radius: 2px; }
        @keyframes toast-in {
            from { transform: translateX(110%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        @keyframes toast-out {
            from { transform: translateX(0);    opacity: 1; }
            to   { transform: translateX(110%); opacity: 0; }
        }
        .toast-enter { animation: toast-in  .3s ease forwards; }
        .toast-leave { animation: toast-out .3s ease forwards; }
    </style>
</head>
<body class="min-h-screen text-gray-100 antialiased">

    {{-- Grid noise overlay --}}
    <div class="pointer-events-none fixed inset-0 -z-10 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    {{-- Radial glow blobs --}}
    <div class="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
        <div class="absolute top-1/2 -right-32 h-96 w-96 rounded-full bg-violet-600/10 blur-[120px]"></div>
    </div>

    <div x-data="adminLayout()" class="flex h-screen overflow-hidden">

        {{-- ================================================================ --}}
        {{-- SIDEBAR                                                           --}}
        {{-- ================================================================ --}}
        <aside
            :class="sidebarOpen ? 'w-60' : 'w-16'"
            class="relative flex-shrink-0 flex flex-col glass border-r border-white/[0.06] transition-all duration-300 ease-in-out overflow-hidden z-30">

            {{-- Logo --}}
            <div class="flex items-center gap-3 px-4 py-5 border-b border-white/[0.06]">
                <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-400 to-violet-600 text-[#050505] font-bold text-sm">
                    DT
                </div>
                <span
                    x-show="sidebarOpen"
                    x-transition:enter="transition-all duration-200 delay-100"
                    x-transition:enter-start="opacity-0 -translate-x-2"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    class="neon-text font-bold text-base whitespace-nowrap">
                    Admin Panel
                </span>
            </div>

            {{-- Nav items --}}
            <nav class="flex-1 overflow-y-auto scrollbar-thin py-4 space-y-4 px-2">
                @php
                    $navGroups = [
                        'Panel' => [
                            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'chart'],
                        ],
                        'Contenido' => [
                            ['route' => 'admin.tattoos',      'label' => 'Tatuajes',     'icon' => 'qr'],
                            // ['route' => 'admin.qr-generator', 'label' => 'Generar QRs',  'icon' => 'generator'], // oculto a petición del cliente (kickoff)
                            ['route' => 'admin.scans',        'label' => 'Monitor Scans','icon' => 'scan'],
                            ['route' => 'admin.link-pages',   'label' => 'Link Pages',   'icon' => 'link'],
                            ['route' => 'admin.tatuadores',   'label' => 'Tatuadores',   'icon' => 'company'],
                        ],
                        'Clientes' => [
                            ['route' => 'admin.users',         'label' => 'Usuarios',       'icon' => 'users'],
                            ['route' => 'admin.companies',     'label' => 'Empresas',       'icon' => 'company'],
                            ['route' => 'admin.team-members',  'label' => 'Equipos',        'icon' => 'team'],
                            ['route' => 'admin.storage-usage', 'label' => 'Almacenamiento', 'icon' => 'folder'],
                        ],
                        'Monetización' => [
                            ['route' => 'admin.pricing',        'label' => 'Planes',         'icon' => 'pricing'],
                            ['route' => 'admin.storage-packs',  'label' => 'Storage Packs',  'icon' => 'storage'],
                            ['route' => 'admin.subscriptions',  'label' => 'Suscripciones',  'icon' => 'subscription'],
                            ['route' => 'admin.referrals',      'label' => 'Referidos',      'icon' => 'referral'],
                            // ['route' => 'admin.withdrawals',    'label' => 'Retiros',        'icon' => 'referral'], // oculto a petición del cliente (kickoff)
                            // ['route' => 'admin.billing-alerts', 'label' => 'Alertas Billing','icon' => 'alerts'], // oculto a petición del cliente (kickoff)
                        ],
                        'Sistema' => [
                            ['route' => 'admin.activity-log', 'label' => 'Actividad',  'icon' => 'activity'],
                            ['route' => 'admin.api-tokens',   'label' => 'Tokens API', 'icon' => 'key'],
                            ['route' => 'admin.account',      'label' => 'Mi Cuenta',  'icon' => 'account'],
                        ],
                    ];
                @endphp

                @foreach($navGroups as $section => $items)
                    <div class="space-y-1">
                        <p x-show="sidebarOpen" class="px-3 pb-0.5 text-[10px] font-semibold uppercase tracking-widest text-gray-600 whitespace-nowrap">{{ $section }}</p>
                        @foreach($items as $item)
                            <a href="{{ route($item['route']) }}"
                               class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-400 transition-all duration-150 hover:text-white glass-hover {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                <span class="flex-shrink-0 h-5 w-5">
                                    @include('components.admin.icons.' . $item['icon'])
                                </span>
                                <span x-show="sidebarOpen"
                                      x-transition:enter="transition-opacity duration-150 delay-100"
                                      x-transition:enter-start="opacity-0"
                                      x-transition:enter-end="opacity-100"
                                      class="whitespace-nowrap">
                                    {{ $item['label'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            {{-- Collapse toggle --}}
            <div class="border-t border-white/[0.06] p-3">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="flex w-full items-center justify-center rounded-lg p-2 text-gray-500 transition hover:text-cyan-400 hover:bg-white/5">
                    <svg x-show="sidebarOpen"  class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/></svg>
                    <svg x-show="!sidebarOpen" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M6 5l7 7-7 7"/></svg>
                </button>
            </div>
        </aside>

        {{-- ================================================================ --}}
        {{-- MAIN AREA                                                         --}}
        {{-- ================================================================ --}}
        <div class="flex flex-1 flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="glass border-b border-white/[0.06] px-6 py-3 flex items-center justify-between flex-shrink-0">
                <div>
                    <h1 class="text-sm font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-500 mt-0.5">@yield('page-subtitle', 'Dynamic Tattoos — Admin')</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400 ring-1 ring-emerald-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        En línea
                    </span>
                    <div class="h-7 w-7 rounded-full bg-gradient-to-br from-cyan-400 to-violet-500 flex items-center justify-center text-[10px] font-bold text-black">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto scrollbar-thin p-6">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- TOAST CONTAINER (listens for the `toast` browser event)            --}}
    {{-- ================================================================== --}}
    <div
        x-data="toastManager()"
        @toast.window="add($event.detail)"
        class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 items-end"
        aria-live="polite">

        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-8"
                :class="{
                    'border-cyan-500/40 bg-cyan-950/80':   toast.type === 'success',
                    'border-red-500/40 bg-red-950/80':     toast.type === 'error',
                    'border-amber-500/40 bg-amber-950/80': toast.type === 'warning',
                    'border-violet-500/40 bg-violet-950/80': toast.type === 'info',
                }"
                class="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-medium text-white shadow-2xl backdrop-blur-xl min-w-[240px] max-w-sm">

                <span x-text="toast.icon" class="text-lg leading-none"></span>
                <span x-text="toast.message" class="flex-1"></span>
                <button @click="remove(toast.id)" class="text-gray-500 hover:text-white transition">✕</button>
            </div>
        </template>
    </div>

    <script>
        function adminLayout() {
            return {
                sidebarOpen: localStorage.getItem('admin_sidebar') !== 'false',
                init() {
                    this.$watch('sidebarOpen', v => localStorage.setItem('admin_sidebar', v));
                }
            };
        }

        function toastManager() {
            return {
                toasts: [],
                counter: 0,
                add({ message, type = 'success' }) {
                    const icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
                    const id    = ++this.counter;
                    this.toasts.push({ id, message, type, icon: icons[type] ?? '•', visible: true });
                    setTimeout(() => this.remove(id), 4000);
                },
                remove(id) {
                    const t = this.toasts.find(t => t.id === id);
                    if (t) { t.visible = false; setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 300); }
                }
            };
        }
    </script>

    @livewireScripts
</body>
</html>
