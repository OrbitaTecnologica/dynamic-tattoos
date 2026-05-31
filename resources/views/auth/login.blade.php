<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Iniciar sesión — Dynamic Tattoos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-[#050505] text-gray-100 antialiased" style="font-family:'Outfit',sans-serif;">

    {{-- Grid noise --}}
    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    {{-- Glow blobs --}}
    <div class="pointer-events-none fixed inset-0" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
        <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-violet-600/10 blur-[120px]"></div>
    </div>

    <div class="relative w-full max-w-sm px-4">

        {{-- Logo --}}
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-2">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-violet-500 text-xl font-bold text-black shadow-lg shadow-cyan-500/20">
                    DT
                </span>
                <span class="text-sm font-semibold tracking-wide" style="background:linear-gradient(90deg,#00f5ff,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    Dynamic Tattoos
                </span>
            </a>
            <p class="mt-3 text-xs text-gray-500">Inicia sesión para acceder al panel</p>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-white/[0.07] bg-white/[0.04] p-7 backdrop-blur-xl">

            {{-- Session status --}}
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-emerald-950/50 px-4 py-3 text-xs font-medium text-emerald-400 ring-1 ring-emerald-500/20">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-medium text-gray-400">Correo electrónico</label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}"
                           required autofocus autocomplete="username"
                           class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('email') border-red-500/40 @enderror">
                    @error('email')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-medium text-gray-400">Contraseña</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition">
                                ¿Olvidaste la contraseña?
                            </a>
                        @endif
                    </div>
                    <input id="password" name="password" type="password"
                           required autocomplete="current-password"
                           class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('password') border-red-500/40 @enderror">
                    @error('password')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="flex items-center gap-2">
                    <input id="remember_me" name="remember" type="checkbox"
                           class="rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500/40">
                    <label for="remember_me" class="text-xs text-gray-400">Recuérdame</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</body>
</html>
