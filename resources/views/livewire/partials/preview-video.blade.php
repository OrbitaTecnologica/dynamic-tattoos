{{--
    livewire/partials/preview-video.blade.php
    Shown inside the phone frame in ManageTattoo when activeTab === 'video'.
    $payload: array { url: string, platform: string, autoplay: bool, title: string|null }
--}}
<div class="p-3 flex flex-col gap-3">
    @if(! empty($payload['url']))
        @php
            $icons = ['youtube' => '▶️', 'vimeo' => '🎬', 'tiktok' => '🎵'];
            $icon  = $icons[$payload['platform'] ?? 'youtube'] ?? '🎬';
        @endphp

        {{-- Simulated video thumbnail --}}
        <div class="aspect-video bg-gray-900 rounded-xl overflow-hidden flex flex-col items-center justify-center gap-2 shadow-inner">
            <span class="text-4xl">{{ $icon }}</span>
            <span class="text-white text-[10px] font-medium capitalize opacity-70">
                {{ $payload['platform'] ?? 'video' }}
            </span>
            @if(! empty($payload['autoplay']))
                <span class="text-[9px] text-green-400">▶ Autoplay activado</span>
            @endif
        </div>

        @if(! empty($payload['title']))
            <p class="text-sm font-semibold text-gray-800 text-center">{{ $payload['title'] }}</p>
        @endif

        <p class="text-[10px] text-gray-400 break-all text-center line-clamp-2">{{ $payload['url'] }}</p>
    @else
        <div class="flex flex-col items-center justify-center h-40 text-gray-400 gap-2">
            <span class="text-3xl">🎬</span>
            <p class="text-xs text-center">Ingresa una URL de video para ver la previsualización</p>
        </div>
    @endif
</div>
