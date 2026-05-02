<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Registro — Dynamic Tattoos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#050505] text-gray-100 antialiased" style="font-family:'Outfit',sans-serif;">
    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    <div class="pointer-events-none fixed inset-0" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
        <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-violet-600/10 blur-[120px]"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen w-full max-w-xl items-center px-4 py-8">
        <div class="w-full rounded-2xl border border-white/[0.07] bg-white/[0.04] p-7 backdrop-blur-xl sm:p-8" x-data="{ role: '{{ old('role', 'artist') }}' }">
            <div class="mb-6 text-center">
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-2">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-violet-500 text-xl font-bold text-black shadow-lg shadow-cyan-500/20">DT</span>
                    <span class="text-sm font-semibold tracking-wide" style="background:linear-gradient(90deg,#00f5ff,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Dynamic Tattoos</span>
                </a>
                <p class="mt-3 text-xs text-gray-500">Crea tu cuenta y empieza a evolucionar tu tinta</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-medium text-gray-400">Nombre</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('name') border-red-500/40 @enderror">
                    @error('name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-medium text-gray-400">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                           class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('email') border-red-500/40 @enderror">
                    @error('email')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-400">Tipo de cuenta</p>
                    <input type="hidden" name="role" x-model="role">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button type="button" @click="role = 'artist'"
                                :class="role === 'artist' ? 'border-cyan-400/40 bg-cyan-500/10 text-cyan-200' : 'border-white/10 bg-white/5 text-gray-300'"
                                class="rounded-xl border px-4 py-3 text-left transition">
                            <p class="text-sm font-semibold">Artista / Tatuador</p>
                            <p class="mt-1 text-xs text-gray-500">Gestiona tatuajes de clientes</p>
                        </button>
                        <button type="button" @click="role = 'user'"
                                :class="role === 'user' ? 'border-violet-400/40 bg-violet-500/10 text-violet-200' : 'border-white/10 bg-white/5 text-gray-300'"
                                class="rounded-xl border px-4 py-3 text-left transition">
                            <p class="text-sm font-semibold">Usuario tatuado</p>
                            <p class="mt-1 text-xs text-gray-500">Requiere plan anual activo</p>
                        </button>
                    </div>
                    @error('role')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div x-show="role === 'user'" x-cloak class="space-y-1.5">
                    <label for="plan_id" class="block text-xs font-medium text-gray-400">Plan anual</label>
                    <select id="plan_id" name="plan_id"
                            :required="role === 'user'"
                            class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('plan_id') border-red-500/40 @enderror">
                        <option value="">Selecciona un plan anual</option>
                        @foreach($yearlyPlans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>
                                {{ $plan->name }} — ${{ number_format((float) $plan->price, 2) }}/año
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500">Solo se muestran planes anuales activos con costo.</p>
                    @if($yearlyPlans->isEmpty())
                        <p class="text-xs text-amber-400">No hay planes anuales disponibles en este momento. Contacta soporte para activar uno.</p>
                    @endif
                    @error('plan_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-medium text-gray-400">Contraseña</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20 @error('password') border-red-500/40 @enderror">
                        @error('password')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-medium text-gray-400">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                               class="w-full rounded-xl border border-white/[0.08] bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 transition focus:border-cyan-500/40 focus:outline-none focus:ring-1 focus:ring-cyan-500/20">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40">
                    Registrarme
                </button>

                <p class="text-center text-xs text-gray-500">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 transition">Inicia sesión</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
