{{--
    livewire/manage-tattoo.blade.php
    Livewire 3 component view for ManageTattoo.

    Sections:
      • Header with tattoo name + QR URL
      • Content-type tab switcher (Link / Gallery / Video)
      • Per-type reactive form
      • Saved-content list with quick-load
      • Sticky live mobile preview (toggled)
--}}
<div
    class="max-w-6xl mx-auto p-4 sm:p-6"
    x-data="{ activeTab: @entangle('activeTab').live }"
>

    {{-- ===================================================================
         HEADER
    =================================================================== --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $tattoo->name }}
            </h1>
            <p class="text-xs text-gray-500 mt-1 font-mono">
                QR →
                <a
                    href="{{ $tattoo->qr_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-indigo-600 hover:underline"
                >{{ $tattoo->qr_url }}</a>
            </p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Toggle preview --}}
            <button
                wire:click="togglePreview"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                {{ $showPreview ? 'Ocultar preview' : 'Preview móvil' }}
            </button>

            {{-- Save --}}
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60 transition-colors"
            >
                <span wire:loading.remove wire:target="save">Guardar cambios</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </div>

    {{-- ===================================================================
         SUCCESS NOTICE
    =================================================================== --}}
    @if($saved)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition
            class="mb-6 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm"
        >
            <svg class="h-5 w-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Contenido actualizado. Caché QR invalidado — el siguiente escaneo verá los cambios de inmediato.
        </div>
    @endif

    {{-- ===================================================================
         MAIN GRID – Editor + (optional) Preview
    =================================================================== --}}
    <div @class([
        'grid gap-8 items-start',
        'grid-cols-1'                       => ! $showPreview,
        'grid-cols-1 lg:grid-cols-[1fr_300px]' => $showPreview,
    ])>

        {{-- =============================================================
             EDITOR PANEL
        ============================================================= --}}
        <div>
            {{-- Tab switcher --}}
            <div class="inline-flex rounded-xl bg-gray-100 p-1 mb-6 gap-1">
                @foreach([
                    'link'    => ['label' => 'Link',    'icon' => '🔗'],
                    'gallery' => ['label' => 'Galería', 'icon' => '🖼️'],
                    'video'   => ['label' => 'Video',   'icon' => '🎬'],
                ] as $type => $meta)
                    <button
                        wire:click="$set('activeTab', '{{ $type }}')"
                        @class([
                            'flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all',
                            'bg-white text-indigo-700 shadow-sm'  => $activeTab === $type,
                            'text-gray-500 hover:text-gray-700'   => $activeTab !== $type,
                        ])
                    >
                        <span>{{ $meta['icon'] }}</span> {{ $meta['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Form card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                {{-- ─── LINK FORM ─────────────────────────────────── --}}
                @if($activeTab === 'link')
                    <div class="space-y-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                URL de destino <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="url"
                                wire:model.live.debounce.400ms="linkUrl"
                                placeholder="https://mi-portfolio.com"
                                autocomplete="off"
                                class="w-full rounded-lg border px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow @error('linkUrl') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                            />
                            @error('linkUrl')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Etiqueta descriptiva
                            </label>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="linkLabel"
                                placeholder="Visita mi portfolio"
                                maxlength="255"
                                class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow"
                            />
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                wire:model.live="linkOpenInNewTab"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span class="text-sm text-gray-700">Abrir en nueva pestaña</span>
                        </label>
                    </div>
                @endif

                {{-- ─── GALLERY FORM ───────────────────────────────── --}}
                @if($activeTab === 'gallery')
                    <div class="space-y-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Título de la galería
                            </label>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="galleryTitle"
                                placeholder="Mi colección de arte corporal"
                                maxlength="255"
                                class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Subir imágenes
                            </label>
                            <div class="flex items-start gap-3">
                                <input
                                    type="file"
                                    wire:model="newImages"
                                    multiple
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                    class="flex-1 text-sm text-gray-500
                                           file:mr-3 file:py-1.5 file:px-3 file:rounded-md
                                           file:border-0 file:text-sm file:font-medium
                                           file:bg-indigo-50 file:text-indigo-700
                                           hover:file:bg-indigo-100 cursor-pointer"
                                />
                                <button
                                    wire:click="uploadGalleryImages"
                                    wire:loading.attr="disabled"
                                    class="shrink-0 px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60 transition-colors"
                                >
                                    <span wire:loading.remove wire:target="uploadGalleryImages">Subir</span>
                                    <span wire:loading wire:target="uploadGalleryImages">…</span>
                                </button>
                            </div>
                            @error('newImages.*')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @error('galleryImages')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if(! empty($galleryImages))
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                @foreach($galleryImages as $idx => $image)
                                    <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100">
                                        <img
                                            src="{{ Storage::url($image) }}"
                                            alt="Imagen {{ $idx + 1 }}"
                                            class="w-full h-full object-cover"
                                            loading="lazy"
                                        />
                                        <button
                                            wire:click="removeGalleryImage({{ $idx }})"
                                            wire:confirm="¿Eliminar esta imagen permanentemente?"
                                            class="absolute top-1 right-1 h-6 w-6 flex items-center justify-center
                                                   rounded-full bg-red-600 text-white text-xs
                                                   opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-700"
                                            title="Eliminar imagen"
                                            aria-label="Eliminar imagen {{ $idx + 1 }}"
                                        >✕</button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center justify-center h-32 rounded-xl border-2 border-dashed border-gray-200 text-gray-400 text-sm">
                                Sube al menos una imagen
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ─── VIDEO FORM ─────────────────────────────────── --}}
                @if($activeTab === 'video')
                    <div class="space-y-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                URL del video <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="url"
                                wire:model.live.debounce.400ms="videoUrl"
                                placeholder="https://youtube.com/watch?v=…"
                                autocomplete="off"
                                class="w-full rounded-lg border px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow @error('videoUrl') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                            />
                            @error('videoUrl')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Plataforma <span class="text-red-500">*</span>
                            </label>
                            <select
                                wire:model.live="videoPlatform"
                                class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white transition-shadow"
                            >
                                <option value="youtube">YouTube</option>
                                <option value="vimeo">Vimeo</option>
                                <option value="tiktok">TikTok</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Título del video
                            </label>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="videoTitle"
                                placeholder="Mi proceso de tatuaje"
                                maxlength="255"
                                class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow"
                            />
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                wire:model.live="videoAutoplay"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span class="text-sm text-gray-700">Reproducción automática al escanear</span>
                        </label>
                    </div>
                @endif

            </div>{{-- /form card --}}

            {{-- ─── SAVED CONTENTS LIST ───────────────────────────── --}}
            @if($this->contents->isNotEmpty())
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-700">Contenidos guardados</h3>
                        <button
                            wire:click="addNewContent"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                        >+ Nuevo</button>
                    </div>

                    <div class="space-y-2">
                        @foreach($this->contents as $content)
                            <button
                                wire:click="setActiveContent({{ $content->id }})"
                                @class([
                                    'w-full text-left flex items-center justify-between px-4 py-3 rounded-xl border text-sm transition-all',
                                    'border-indigo-300 bg-indigo-50 text-indigo-900 font-medium' => $activeContentId === $content->id,
                                    'border-gray-200 bg-white hover:border-gray-300 text-gray-700' => $activeContentId !== $content->id,
                                ])
                            >
                                <div class="flex items-center gap-2">
                                    <span class="capitalize">{{ $content->type }}</span>
                                    @if($content->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            Activo
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400">
                                    {{ $content->updated_at->diffForHumans() }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>{{-- /editor panel --}}

        {{-- =============================================================
             LIVE MOBILE PREVIEW
        ============================================================= --}}
        @if($showPreview)
            <div class="sticky top-6">
                <p class="text-xs font-semibold text-gray-500 text-center uppercase tracking-wide mb-3">
                    Previsualización en vivo
                </p>

                {{-- Phone frame --}}
                <div class="mx-auto w-[260px]">
                    <div class="relative bg-gray-900 rounded-[2.5rem] p-2.5 shadow-2xl ring-[3px] ring-gray-700">
                        {{-- Notch --}}
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-20 h-4 bg-gray-900 rounded-b-2xl z-10 pointer-events-none"></div>

                        {{-- Screen --}}
                        <div class="bg-white rounded-[2rem] overflow-hidden" style="height: 500px;">

                            {{-- Status bar --}}
                            <div class="flex items-center justify-between px-4 pt-2.5 pb-1 bg-gray-50 text-[10px] font-semibold text-gray-500">
                                <span>9:41</span>
                                <span>⚡ 100%</span>
                            </div>

                            {{-- Preview content --}}
                            <div class="overflow-y-auto h-[calc(100%-28px)]">
                                @if($activeTab === 'link')
                                    @include('livewire.partials.preview-link', ['payload' => $this->previewPayload])
                                @elseif($activeTab === 'gallery')
                                    @include('livewire.partials.preview-gallery', ['payload' => $this->previewPayload])
                                @elseif($activeTab === 'video')
                                    @include('livewire.partials.preview-video', ['payload' => $this->previewPayload])
                                @endif
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-[11px] text-gray-400 mt-3">
                        Así se verá al escanear el QR
                    </p>
                </div>
            </div>
        @endif

    </div>{{-- /main grid --}}
</div>
