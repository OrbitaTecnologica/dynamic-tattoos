{{--
    livewire/tattoo-viewer.blade.php
    Rendered inside tattoo/show.blade.php for Gallery and Video content types.
--}}
<div class="min-h-screen bg-gray-950 text-white flex flex-col">

    {{-- ===================================================================
         GALLERY VIEWER
    =================================================================== --}}
    @if($content->isGallery())
        @php
            $images = $content->payload['images'] ?? [];
            $title  = $content->payload['title']  ?? '';
            $total  = count($images);
        @endphp

        <div class="flex flex-col items-center justify-center min-h-screen p-5 gap-6">

            @if($title)
                <h1 class="text-xl font-bold tracking-tight text-center">{{ $title }}</h1>
            @endif

            @if($total > 0)
                {{-- Main image --}}
                <div class="relative w-full max-w-sm aspect-square rounded-3xl overflow-hidden bg-gray-800 shadow-2xl">
                    <img
                        src="{{ Storage::url($images[$currentImageIndex]) }}"
                        alt="Imagen {{ $currentImageIndex + 1 }} de {{ $total }}"
                        class="w-full h-full object-cover"
                        loading="eager"
                    />
                </div>

                {{-- Navigation --}}
                @if($total > 1)
                    <div class="flex items-center gap-6">
                        <button
                            wire:click="previousImage"
                            @disabled($currentImageIndex === 0)
                            class="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                            aria-label="Imagen anterior"
                        >← Anterior</button>

                        <span class="text-sm text-gray-400 tabular-nums">
                            {{ $currentImageIndex + 1 }} / {{ $total }}
                        </span>

                        <button
                            wire:click="nextImage"
                            @disabled($currentImageIndex === $total - 1)
                            class="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                            aria-label="Imagen siguiente"
                        >Siguiente →</button>
                    </div>

                    {{-- Thumbnail strip --}}
                    <div class="flex gap-2 overflow-x-auto pb-1 max-w-sm w-full">
                        @foreach($images as $idx => $image)
                            <button
                                wire:click="$set('currentImageIndex', {{ $idx }})"
                                class="shrink-0 w-14 h-14 rounded-xl overflow-hidden border-2 transition-all {{ $idx === $currentImageIndex ? 'border-white scale-105' : 'border-transparent opacity-50 hover:opacity-75' }}"
                                aria-label="Ver imagen {{ $idx + 1 }}"
                                aria-pressed="{{ $idx === $currentImageIndex ? 'true' : 'false' }}"
                            >
                                <img
                                    src="{{ Storage::url($image) }}"
                                    alt=""
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                            </button>
                        @endforeach
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-sm">No hay imágenes en esta galería todavía.</p>
            @endif
        </div>

    {{-- ===================================================================
         VIDEO VIEWER
    =================================================================== --}}
    @elseif($content->isVideo())
        @php
            $videoUrl    = $content->payload['url']      ?? '';
            $platform    = $content->payload['platform'] ?? 'youtube';
            $videoTitle  = $content->payload['title']    ?? '';
            $autoplay    = $content->payload['autoplay'] ?? false;
            $autoplayStr = $autoplay ? '1' : '0';

            $embedUrl = match ($platform) {
                'youtube' => (static function () use ($videoUrl, $autoplayStr): string {
                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_\-]{11})/', $videoUrl, $m);
                    $id = $m[1] ?? '';
                    return $id
                        ? "https://www.youtube-nocookie.com/embed/{$id}?autoplay={$autoplayStr}&rel=0"
                        : '';
                })(),
                'vimeo' => (static function () use ($videoUrl, $autoplayStr): string {
                    preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m);
                    $id = $m[1] ?? '';
                    return $id
                        ? "https://player.vimeo.com/video/{$id}?autoplay={$autoplayStr}&dnt=1"
                        : '';
                })(),
                default => '',
            };
        @endphp

        <div class="flex flex-col items-center justify-center min-h-screen p-5 gap-6">

            @if($videoTitle)
                <h1 class="text-xl font-bold tracking-tight text-center">{{ $videoTitle }}</h1>
            @endif

            @if($embedUrl)
                <div class="w-full max-w-sm aspect-video rounded-3xl overflow-hidden shadow-2xl">
                    <iframe
                        src="{{ $embedUrl }}"
                        class="w-full h-full"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        title="{{ e($videoTitle ?: 'Video') }}"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                    ></iframe>
                </div>

                @if($platform === 'tiktok')
                    <p class="text-xs text-gray-500 text-center max-w-xs break-all">
                        TikTok no admite embeds directos.
                        <a
                            href="{{ e($videoUrl) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-indigo-400 underline"
                        >Ver en TikTok →</a>
                    </p>
                @endif
            @else
                <p class="text-gray-500 text-sm">Video no disponible.</p>
            @endif
        </div>
    @endif

</div>
