<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi panel') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Bienvenida --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Bienvenido de nuevo') }}</p>
                        <h3 class="text-2xl font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                    </div>
                    <a href="{{ route('qr.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-700 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <path d="M14 14h3v3h-3zM20 14v7M14 20h7"/>
                        </svg>
                        QR Studio
                    </a>
                </div>
            </div>

            {{-- Mis tatuajes --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 px-1">{{ __('Mis tatuajes') }}</h3>

                @if ($tattoos->isEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                        <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <path d="M14 14h3v3h-3zM20 14v7M14 20h7"/>
                        </svg>
                        <p class="text-gray-500 font-medium">{{ __('Todavía no tienes ningún tatuaje registrado.') }}</p>
                        <p class="text-gray-400 text-sm mt-1">{{ __('Contacta con tu administrador para que te asigne uno.') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach ($tattoos as $tattoo)
                            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden flex flex-col">
                                {{-- Cabecera tarjeta --}}
                                <div class="px-5 pt-5 pb-4 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 truncate">{{ $tattoo->name ?? 'Tatuaje ' . $tattoo->short_code }}</p>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $tattoo->short_code }}</p>
                                    </div>
                                    <span @class([
                                        'shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-green-100 text-green-700' => $tattoo->is_active,
                                        'bg-gray-100 text-gray-500'   => ! $tattoo->is_active,
                                    ])>
                                        {{ $tattoo->is_active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </div>

                                {{-- Contenido activo --}}
                                <div class="px-5 pb-4 flex-1">
                                    @if ($tattoo->activeContent)
                                        <p class="text-xs uppercase tracking-wider text-gray-400 mb-1">{{ __('Contenido activo') }}</p>
                                        <div class="flex items-center gap-2">
                                            @switch($tattoo->activeContent->type)
                                                @case('link')
                                                    <svg class="w-4 h-4 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656-5.656M10.172 13.828a4 4 0 01-5.656-5.656l3-3a4 4 0 015.656 5.656"/></svg>
                                                    <span class="text-sm text-gray-700 truncate">{{ $tattoo->activeContent->payload['url'] ?? '—' }}</span>
                                                    @break
                                                @case('gallery')
                                                    <svg class="w-4 h-4 text-fuchsia-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/></svg>
                                                    <span class="text-sm text-gray-700">{{ $tattoo->activeContent->payload['title'] ?? __('Galería') }}</span>
                                                    @break
                                                @case('video')
                                                    <svg class="w-4 h-4 text-red-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                                    <span class="text-sm text-gray-700">{{ $tattoo->activeContent->payload['title'] ?? __('Video') }}</span>
                                                    @break
                                            @endswitch
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-400 italic">{{ __('Sin contenido activo') }}</p>
                                    @endif
                                </div>

                                {{-- Acciones --}}
                                <div class="border-t border-gray-100 px-5 py-3 flex items-center gap-2">
                                    <a href="{{ route('tattoos.manage', $tattoo) }}"
                                       class="flex-1 text-center rounded-md border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                        {{ __('Gestionar contenido') }}
                                    </a>
                                    <a href="{{ route('qr.create', ['tattoo' => $tattoo->short_code]) }}"
                                       class="flex-1 text-center rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700 transition">
                                        {{ __('Diseñar QR') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Sección facturación --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-900">{{ __('Suscripción y facturación') }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ __('Gestiona tu plan y métodos de pago.') }}</p>
                </div>
                <a href="{{ route('billing') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    {{ __('Ir a facturación') }}
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
