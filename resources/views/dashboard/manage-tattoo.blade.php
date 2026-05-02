{{--
    dashboard/manage-tattoo.blade.php
    Authenticated wrapper that mounts the ManageTattoo Livewire component.
    Assumes a Breeze / Jetstream-style x-app-layout component is available.
--}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a
                href="{{ route('dashboard') }}"
                class="text-gray-400 hover:text-gray-600 transition-colors"
                aria-label="Volver al dashboard"
            >←</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestionar contenido del tatuaje
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <livewire:manage-tattoo :tattoo="$tattoo" />
    </div>
</x-app-layout>
