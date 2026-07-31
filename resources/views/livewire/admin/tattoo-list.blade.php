<div class="space-y-5">

    {{-- ── Toolbar ────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                wire:model.live.debounce.400ms="search"
                type="search"
                placeholder="Buscar por short_code o nombre…"
                class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>

        <select wire:model.live="filterType"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los tipos</option>
            <option value="link">Link</option>
            <option value="gallery">Galería</option>
            <option value="video">Video</option>
        </select>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Short Code</th>
                        <th class="px-5 py-3.5">Tipo Activo</th>
                        <th class="px-5 py-3.5">Preview</th>
                        <th class="px-5 py-3.5">Propietario</th>
                        <th class="px-5 py-3.5">Versiones</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->tattoos as $tattoo)
                        @php $active = $tattoo->activeContent; @endphp
                        <tr class="group transition hover:bg-white/[0.02]">
                            {{-- short_code --}}
                            <td class="px-5 py-4">
                                <a href="{{ route('tattoo.show', $tattoo->short_code) }}" target="_blank"
                                   class="font-mono text-cyan-300 hover:text-cyan-200 transition text-xs tracking-wider">
                                    {{ $tattoo->short_code }}
                                </a>
                                <p class="text-xs text-gray-600 mt-0.5">{{ $tattoo->name }}</p>
                            </td>

                            {{-- Type badge --}}
                            <td class="px-5 py-4">
                                @if($active)
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1',
                                        'bg-cyan-950/60 text-cyan-300 ring-cyan-500/20'   => $active->type === 'link',
                                        'bg-violet-950/60 text-violet-300 ring-violet-500/20' => $active->type === 'gallery',
                                        'bg-amber-950/60 text-amber-300 ring-amber-500/20'  => $active->type === 'video',
                                    ])>
                                        {{ ucfirst($active->type) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 text-xs">Sin contenido</span>
                                @endif
                            </td>

                            {{-- Preview --}}
                            <td class="px-5 py-4">
                                @if($active?->type === 'link')
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        <span class="truncate max-w-[140px]">{{ $active->payload['label'] ?? parse_url($active->payload['url'] ?? '', PHP_URL_HOST) }}</span>
                                    </div>
                                @elseif($active?->type === 'gallery')
                                    @php $images = $active->payload['images'] ?? []; @endphp
                                    <div class="flex -space-x-2">
                                        @foreach(array_slice($images, 0, 3) as $img)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($img) }}"
                                                 class="h-7 w-7 rounded-md object-cover ring-1 ring-[#050505]"
                                                 alt="">
                                        @endforeach
                                        @if(count($images) > 3)
                                            <span class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-xs text-gray-400 ring-1 ring-[#050505]">+{{ count($images) - 3 }}</span>
                                        @endif
                                    </div>
                                @elseif($active?->type === 'video')
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <span class="capitalize">{{ $active->payload['platform'] ?? 'video' }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-700 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Owner --}}
                            <td class="px-5 py-4">
                                <p class="text-xs text-gray-300">{{ $tattoo->user?->name }}</p>
                                <p class="text-xs text-gray-600">{{ $tattoo->user?->email }}</p>
                            </td>

                            {{-- Version count --}}
                            <td class="px-5 py-4 text-center">
                                <span class="tabular-nums text-gray-400 text-xs">{{ $tattoo->contents_count }}</span>
                            </td>

                            {{-- Active toggle --}}
                            <td class="px-5 py-4">
                                <button wire:click="toggleActive({{ $tattoo->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleActive({{ $tattoo->id }})"
                                        class="transition">
                                    <span @class([
                                        'inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200',
                                        'bg-cyan-500' => $tattoo->is_active,
                                        'bg-white/10' => !$tattoo->is_active,
                                    ])>
                                        <span @class([
                                            'inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform duration-200',
                                            'translate-x-4'   => $tattoo->is_active,
                                            'translate-x-0.5' => !$tattoo->is_active,
                                        ])></span>
                                    </span>
                                </button>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEditName({{ $tattoo->id }})"
                                            class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-cyan-500/40 hover:text-cyan-300">
                                        Renombrar
                                    </button>
                                    <button wire:click="openHistory({{ $tattoo->id }})"
                                            class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-violet-500/40 hover:text-violet-300">
                                        Historial
                                    </button>
                                    <button wire:click="delete({{ $tattoo->id }})"
                                            wire:confirm="¿Eliminar el tatuaje {{ $tattoo->short_code }}? Se borrarán sus versiones, escaneos y archivos."
                                            wire:loading.attr="disabled"
                                            wire:target="delete({{ $tattoo->id }})"
                                            class="rounded-lg bg-white/5 px-2.5 py-1.5 text-xs font-medium text-gray-400 ring-1 ring-white/10 transition hover:ring-red-500/40 hover:text-red-400 disabled:opacity-50">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron tatuajes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->tattoos->links() }}
        </div>
    </div>

    {{-- ── History Modal ────────────────────────────────────────────────────── --}}
    @if($showHistoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-init="$el.querySelector('[data-modal]').focus()">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeHistory"></div>
            <div data-modal tabindex="-1"
                 class="relative glass rounded-2xl w-full max-w-lg max-h-[80vh] flex flex-col outline-none">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4 flex-shrink-0">
                    <h2 class="text-sm font-semibold text-white">Historial de Evolución</h2>
                    <button wire:click="closeHistory" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto scrollbar-thin px-6 py-4 flex-1">
                    @forelse($this->historyItems as $i => $item)
                        <div class="relative flex gap-4 pb-6 last:pb-0">
                            {{-- Timeline line --}}
                            @unless($loop->last)
                                <div class="absolute left-3.5 top-8 bottom-0 w-px bg-white/[0.06]"></div>
                            @endunless

                            {{-- Node --}}
                            <div class="relative flex-shrink-0 flex h-7 w-7 items-center justify-center rounded-full ring-1 {{ $item->is_active ? 'bg-cyan-500/20 ring-cyan-500/40 text-cyan-300' : 'bg-white/5 ring-white/10 text-gray-500' }}">
                                @if($item->is_active)
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span @class([
                                        'text-xs font-semibold rounded-full px-2 py-0.5',
                                        'bg-cyan-950/60 text-cyan-300'    => $item->type === 'link',
                                        'bg-violet-950/60 text-violet-300' => $item->type === 'gallery',
                                        'bg-amber-950/60 text-amber-300'   => $item->type === 'video',
                                    ])>
                                        {{ ucfirst($item->type) }}
                                    </span>
                                    @if($item->is_active)
                                        <span class="text-xs text-cyan-400 font-semibold">● Activo</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">{{ $item->updated_at?->format('d/m/Y H:i') }}</p>
                                @if($item->type === 'link')
                                    <p class="text-xs text-gray-400 mt-1 truncate">{{ $item->payload['url'] ?? '' }}</p>
                                @elseif($item->type === 'gallery')
                                    <p class="text-xs text-gray-400 mt-1">{{ count($item->payload['images'] ?? []) }} imágenes</p>
                                @elseif($item->type === 'video')
                                    <p class="text-xs text-gray-400 mt-1 capitalize">{{ $item->payload['platform'] ?? '' }}</p>
                                @endif
                                @unless($item->is_active)
                                    <button wire:click="activateContent({{ $item->id }})"
                                            class="mt-2 rounded-lg bg-white/5 px-2.5 py-1 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-cyan-500/40 hover:text-cyan-300">
                                        Activar esta versión
                                    </button>
                                @endunless
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 text-center py-8">Sin historial disponible.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ── Rename Modal ─────────────────────────────────────────────────────── --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
            <div class="relative glass rounded-2xl w-full max-w-md">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4">
                    <h2 class="text-sm font-semibold text-white">Renombrar tatuaje</h2>
                    <button wire:click="$set('showEditModal', false)" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-1.5">
                    <label class="text-xs font-medium text-gray-400">Nombre</label>
                    <input wire:model="editName" type="text"
                           class="w-full rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
                    @error('editName') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="border-t border-white/[0.06] px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="$set('showEditModal', false)"
                            class="rounded-xl px-4 py-2 text-sm text-gray-500 ring-1 ring-white/10 transition hover:text-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="saveName"
                            wire:loading.attr="disabled"
                            wire:target="saveName"
                            class="rounded-xl bg-gradient-to-r from-cyan-500 to-violet-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveName">Guardar</span>
                        <span wire:loading wire:target="saveName">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
