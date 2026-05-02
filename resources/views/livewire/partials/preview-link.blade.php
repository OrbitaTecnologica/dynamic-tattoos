{{--
    livewire/partials/preview-link.blade.php
    Shown inside the phone frame in ManageTattoo when activeTab === 'link'.
    $payload: array { url: string, label: string, open_in_new_tab: bool }
--}}
<div class="p-4 flex flex-col gap-3">
    @if(! empty($payload['url']))
        <div class="rounded-xl border border-gray-200 p-4 bg-white shadow-xs">
            {{-- Simulated browser bar --}}
            <div class="flex items-center gap-1.5 mb-3 px-2 py-1 bg-gray-100 rounded-lg">
                <span class="text-[8px] text-gray-400 truncate">{{ $payload['url'] }}</span>
            </div>

            <div class="flex flex-col items-center gap-3 py-4">
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-lg">🔗</div>

                @if(! empty($payload['label']))
                    <p class="font-semibold text-gray-800 text-sm text-center">{{ $payload['label'] }}</p>
                @endif

                <p class="text-xs text-gray-500 break-all text-center">{{ $payload['url'] }}</p>

                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-600 text-white text-[10px] font-medium">
                    {{ $payload['open_in_new_tab'] ? 'Abre en nueva pestaña' : 'Abre en esta pestaña' }}
                </span>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-40 text-gray-400 gap-2">
            <span class="text-3xl">🔗</span>
            <p class="text-xs text-center">Ingresa una URL para ver la previsualización</p>
        </div>
    @endif
</div>
