<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Verificación en dos pasos — Dynamic Tattoos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-[#050505] text-gray-100 antialiased" style="font-family:'Outfit',sans-serif;">

    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    <div class="pointer-events-none fixed inset-0" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
        <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-violet-600/10 blur-[120px]"></div>
    </div>

    <div class="relative w-full max-w-sm px-4">
        <div class="mb-8 text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-violet-500 text-xl font-bold text-black shadow-lg shadow-cyan-500/20">DT</span>
            <p class="mt-3 text-xs text-gray-500">Verificación en dos pasos</p>
        </div>

        <div class="rounded-2xl border border-white/[0.07] bg-white/[0.04] p-7 backdrop-blur-xl">
            <p class="mb-5 text-sm text-gray-300">
                Introduce el código de 6 dígitos de tu app de autenticación, o uno de tus códigos de recuperación.
            </p>

            <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="space-y-5">
                @csrf
                <div class="space-y-1.5">
                    <label for="code" class="block text-xs font-medium text-gray-400">Código de verificación</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                           required autofocus
                           class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('code') border-red-500/40 @enderror">
                    @error('code')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
                    Verificar y entrar
                </button>
            </form>

            <p class="mt-4 text-center text-xs text-gray-600">
                <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 transition">Volver al inicio de sesión</a>
            </p>
        </div>
    </div>
</body>
</html>
