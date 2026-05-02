{{--
    livewire/partials/preview-gallery.blade.php
    Shown inside the phone frame in ManageTattoo when activeTab === 'gallery'.
    $payload: array { title: string|null, images: string[] }
--}}
<div class="p-3 flex flex-col gap-3">
    @if(! empty($payload['title']))
        <h2 class="text-sm font-bold text-gray-800 text-center">{{ $payload['title'] }}</h2>
    @endif

    @if(! empty($payload['images']))
        <div class="grid grid-cols-2 gap-1.5">
            @foreach(array_slice($payload['images'], 0, 4) as $idx => $image)
                <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                    <img
                        src="{{ Storage::url($image) }}"
                        alt="Imagen {{ $idx + 1 }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                </div>
            @endforeach
        </div>

        @if(count($payload['images']) > 4)
            <p class="text-[10px] text-gray-400 text-center">
                +{{ count($payload['images']) - 4 }} imágenes más
            </p>
        @endif
    @else
        <div class="flex flex-col items-center justify-center h-40 text-gray-400 gap-2">
            <span class="text-3xl">🖼️</span>
            <p class="text-xs text-center">Sube imágenes para ver la previsualización</p>
        </div>
    @endif
</div>
