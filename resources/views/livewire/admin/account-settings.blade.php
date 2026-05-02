<div class="max-w-2xl space-y-6">

    {{-- ── Profile ──────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl p-6">
        <h3 class="mb-5 text-sm font-semibold uppercase tracking-widest text-gray-400">Información de perfil</h3>

        <div class="space-y-4">
            <div class="space-y-1.5">
                <label class="text-xs font-medium text-gray-400">Nombre</label>
                <input wire:model="name" type="text"
                       class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                @error('name') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-medium text-gray-400">Correo electrónico</label>
                <input wire:model="email" type="email"
                       class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                @error('email') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <button wire:click="saveProfile"
                    wire:loading.attr="disabled"
                    wire:target="saveProfile"
                    class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:shadow-cyan-500/40 disabled:opacity-60">
                <span wire:loading.remove wire:target="saveProfile">Guardar perfil</span>
                <span wire:loading wire:target="saveProfile">Guardando…</span>
            </button>
        </div>
    </div>

    {{-- ── Password ──────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl p-6">
        <h3 class="mb-5 text-sm font-semibold uppercase tracking-widest text-gray-400">Cambiar contraseña</h3>

        <div class="space-y-4">
            <div class="space-y-1.5">
                <label class="text-xs font-medium text-gray-400">Contraseña actual</label>
                <input wire:model="currentPassword" type="password"
                       class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                @error('currentPassword') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-medium text-gray-400">Nueva contraseña</label>
                <input wire:model="newPassword" type="password"
                       class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                @error('newPassword') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-medium text-gray-400">Confirmar nueva contraseña</label>
                <input wire:model="confirmPassword" type="password"
                       class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
            </div>

            <button wire:click="savePassword"
                    wire:loading.attr="disabled"
                    wire:target="savePassword"
                    class="rounded-xl bg-white/5 ring-1 ring-white/10 px-5 py-2.5 text-sm font-semibold text-gray-300 transition hover:ring-violet-500/40 hover:text-white disabled:opacity-60">
                <span wire:loading.remove wire:target="savePassword">Actualizar contraseña</span>
                <span wire:loading wire:target="savePassword">Actualizando…</span>
            </button>
        </div>
    </div>

    {{-- ── Danger zone ──────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl p-6 border border-red-900/30">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-widest text-red-500">Zona peligrosa</h3>
        <p class="text-xs text-gray-500 mb-4">Una vez que elimines tu cuenta, no podrás recuperarla.</p>
        <button disabled
                class="rounded-xl px-4 py-2 text-xs font-semibold text-red-500 ring-1 ring-red-500/30 opacity-50 cursor-not-allowed">
            Eliminar cuenta
        </button>
    </div>
</div>
