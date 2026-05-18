@props([
    'logoSrc' => null,
    'logoAlt' => 'Evolucion Tatuaje Dinamico',
    'uploadedLogoPath' => 'splash/logo.jpeg',
])

@php
    $resolvedLogoSrc = \Illuminate\Support\Facades\Storage::disk('public')->exists($uploadedLogoPath)
        ? \Illuminate\Support\Facades\Storage::url($uploadedLogoPath)
        : ($logoSrc ?? asset('images/logo.jpeg'));
@endphp

<div
    x-data="{ visible: true }"
    x-init="setTimeout(() => { visible = false }, 5000)"
    x-show="visible"
    x-transition:leave="transition-opacity duration-1000 ease-out"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    class="fixed inset-0 z-[2147483647] flex items-center justify-center overflow-hidden bg-black"
>
    <div class="pointer-events-none absolute inset-0 animate-splash-bg bg-[radial-gradient(circle_at_18%_18%,rgba(24,24,27,0.95),rgba(0,0,0,1)_48%),radial-gradient(circle_at_82%_28%,rgba(38,38,42,0.4),transparent_50%),linear-gradient(140deg,#000000,#09090b,#000000)]"></div>

    <div class="relative z-10 flex flex-col items-center px-6 text-center">
        <img
            src="{{ $resolvedLogoSrc }}"
            alt="{{ $logoAlt }}"
            class="h-auto w-[min(78vw,360px)] max-h-[42vh] animate-splash-breathe object-contain"
            loading="eager"
            decoding="async"
        >

        <p class="mt-6 animate-splash-text-in text-sm font-semibold tracking-[0.2em] text-white/90 sm:text-base">
            No borramos. Evolucionamos.
        </p>
    </div>
</div>
