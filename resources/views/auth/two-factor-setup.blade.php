<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Configurar verificación en dos pasos — Dynamic Tattoos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-[#050505] text-gray-100 antialiased py-10" style="font-family:'Outfit',sans-serif;">

    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    <div class="pointer-events-none fixed inset-0" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
        <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-violet-600/10 blur-[120px]"></div>
    </div>

    <div class="relative w-full max-w-md px-4">
        <div class="mb-8 text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-violet-500 text-xl font-bold text-black shadow-lg shadow-cyan-500/20">DT</span>
            <p class="mt-3 text-xs text-gray-500">El panel requiere verificación en dos pasos para administradores</p>
        </div>

        <div class="rounded-2xl border border-white/[0.07] bg-white/[0.04] p-7 backdrop-blur-xl space-y-6">

            <div>
                <h1 class="text-sm font-semibold text-white mb-2">1 · Escanea el código con tu app</h1>
                <p class="text-xs text-gray-500 mb-4">Google Authenticator, 1Password, Authy o cualquier app TOTP.</p>
                <div class="flex justify-center rounded-xl bg-white p-4">{!! $qrSvg !!}</div>
                <p class="mt-3 text-center text-[11px] text-gray-600">
                    ¿No puedes escanear? Clave manual:
                    <code class="rounded bg-white/5 px-1.5 py-0.5 text-cyan-300">{{ $secret }}</code>
                </p>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-white mb-2">2 · Guarda tus códigos de recuperación</h2>
                <p class="text-xs text-gray-500 mb-3">Sirven para entrar si pierdes el móvil. Guárdalos en un lugar seguro.</p>
                <div class="grid grid-cols-2 gap-2 rounded-xl bg-white/5 p-4 font-mono text-xs text-gray-300 ring-1 ring-white/10">
                    @foreach($recoveryCodes as $code)
                        <span>{{ $code }}</span>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('two-factor.setup.confirm') }}" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="code" class="block text-xs font-medium text-gray-400">3 · Introduce el código de 6 dígitos</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required
                           class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('code') border-red-500/40 @enderror">
                    @error('code')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
                    Activar y entrar al panel
                </button>
            </form>
        </div>
    </div>
</body>
</html>
