<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Evolucion Tatuaje Dinamico | Tu historia viva en la piel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100 antialiased" style="font-family: 'Outfit', sans-serif;">
    <x-landing.splash-screen />

    <div class="relative isolate">
        <div class="pointer-events-none absolute inset-0 -z-20 bg-[radial-gradient(circle_at_15%_15%,rgba(34,211,238,0.22),transparent_35%),radial-gradient(circle_at_85%_20%,rgba(217,70,239,0.2),transparent_36%),radial-gradient(circle_at_50%_90%,rgba(139,92,246,0.22),transparent_34%),linear-gradient(120deg,#020617,#0b1120,#111827,#0f172a)] bg-[length:240%_240%] animate-gradient-x"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:34px_34px]"></div>

        <div class="pointer-events-none fixed inset-x-0 top-0 z-50 h-1 bg-gradient-to-r from-transparent via-cyan-300 to-transparent opacity-0 shadow-[0_0_25px_rgba(34,211,238,0.9)] animate-scan-once"></div>

        <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-lg font-bold tracking-tight text-white">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-cyan-300 to-violet-400 text-slate-900">DT</span>
                Evolucion Tatuaje Dinamico
            </a>
            <div class="flex items-center gap-3">
                <a href="/up" class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-cyan-300 hover:text-cyan-200">
                    Estado
                </a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           class="rounded-full bg-gradient-to-r from-cyan-500 to-violet-500 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
                            Admin Panel
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400 transition hover:border-red-500/40 hover:text-red-400">
                            Salir
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-cyan-300 hover:text-cyan-200">
                        Iniciar sesión
                    </a>
                @endauth
            </div>
        </header>

        <x-landing.hero />
        <x-landing.value />
        @livewire('landing-mockup')
        <x-landing.footer />
    </div>

    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    @livewireScripts
</body>
</html>
