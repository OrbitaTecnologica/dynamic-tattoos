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
                placeholder="Buscar por slug, título o email…"
                class="w-full rounded-xl bg-white/5 border border-white/[0.08] pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition">
        </div>

        <select wire:model.live="filterPublished"
                class="rounded-xl bg-white/5 border border-white/[0.08] px-4 py-2.5 text-sm text-gray-300 focus:outline-none focus:border-cyan-500/40 transition">
            <option value="">Todos los estados</option>
            <option value="1">Publicadas</option>
            <option value="0">Borrador</option>
        </select>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06] text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-5 py-3.5">Slug</th>
                        <th class="px-5 py-3.5">Título</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5 text-center">Vistas</th>
                        <th class="px-5 py-3.5 text-center">Enlaces</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($this->pages as $page)
                        <tr class="group transition hover:bg-white/[0.02]">

                            {{-- Usuario --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-violet-500/30 text-xs font-bold text-white">
                                        {{ strtoupper(substr($page->user?->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white text-sm">{{ $page->user?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-500">{{ $page->user?->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Slug --}}
                            <td class="px-5 py-4">
                                <a href="/u/{{ $page->slug }}" target="_blank"
                                   class="font-mono text-cyan-300 hover:text-cyan-200 transition text-xs tracking-wider">
                                    /u/{{ $page->slug }}
                                </a>
                            </td>

                            {{-- Título --}}
                            <td class="px-5 py-4">
                                <p class="text-sm text-gray-300 truncate max-w-[180px]">{{ $page->title ?? '—' }}</p>
                            </td>

                            {{-- Estado --}}
                            <td class="px-5 py-4">
                                @if($page->is_published)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-emerald-950/60 text-emerald-300 ring-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Publicada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 bg-white/5 text-gray-400 ring-white/10">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>
                                        Borrador
                                    </span>
                                @endif
                            </td>

                            {{-- Vistas --}}
                            <td class="px-5 py-4 text-center">
                                <span class="tabular-nums text-gray-400 text-xs">{{ number_format($page->views_count) }}</span>
                            </td>

                            {{-- Nº enlaces --}}
                            <td class="px-5 py-4 text-center">
                                <span class="tabular-nums text-gray-400 text-xs">{{ $page->links_count }}</span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openLinks({{ $page->id }})"
                                            class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-300 ring-1 ring-white/10 transition hover:ring-cyan-500/40 hover:text-cyan-300">
                                        Ver enlaces
                                    </button>
                                    <button wire:click="togglePublished({{ $page->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="togglePublished({{ $page->id }})"
                                            class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-medium ring-1 transition {{ $page->is_published ? 'text-amber-300 ring-amber-500/20 hover:ring-amber-400/40' : 'text-emerald-300 ring-emerald-500/20 hover:ring-emerald-400/40' }}">
                                        {{ $page->is_published ? 'Despublicar' : 'Publicar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-600">
                                No se encontraron páginas de enlaces.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="border-t border-white/[0.06] px-5 py-3">
            {{ $this->pages->links() }}
        </div>
    </div>

    {{-- ── Modal: Enlaces de la página ────────────────────────────────────── --}}
    @if($showLinksModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-init="$el.querySelector('[data-modal]').focus()">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeLinks"></div>
            <div data-modal tabindex="-1"
                 class="relative glass rounded-2xl w-full max-w-lg max-h-[80vh] flex flex-col outline-none">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4 flex-shrink-0">
                    <h2 class="text-sm font-semibold text-white">Enlaces de la página</h2>
                    <button wire:click="closeLinks" class="text-gray-500 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Contenido --}}
                <div class="overflow-y-auto scrollbar-thin px-6 py-4 flex-1">
                    @forelse($this->pageLinks as $link)
                        <div class="flex items-start gap-4 py-3 border-b border-white/[0.04] last:border-b-0">

                            {{-- Posición --}}
                            <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-white/5 text-xs text-gray-500 font-mono">
                                {{ $link->position }}
                            </span>

                            {{-- Info principal --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-sm font-medium text-gray-200 truncate">{{ $link->displayLabel() }}</span>
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1 flex-shrink-0',
                                        'bg-cyan-950/60 text-cyan-300 ring-cyan-500/20'       => $link->type === 'instagram',
                                        'bg-violet-950/60 text-violet-300 ring-violet-500/20' => $link->type === 'tiktok',
                                        'bg-amber-950/60 text-amber-300 ring-amber-500/20'    => $link->type === 'youtube',
                                        'bg-white/5 text-gray-400 ring-white/10'              => !in_array($link->type, ['instagram', 'tiktok', 'youtube']),
                                    ])>
                                        {{ $link->type }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate">{{ $link->value }}</p>
                            </div>

                            {{-- Clicks + activo --}}
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <span class="text-xs tabular-nums text-gray-400">
                                    {{ number_format($link->clicks_count) }} clics
                                </span>
                                @if($link->is_active)
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-600"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 text-center py-8">Esta página no tiene enlaces configurados.</p>
                    @endforelse
                </div>

                {{-- Footer --}}
                <div class="border-t border-white/[0.06] px-6 py-3 flex-shrink-0">
                    <button wire:click="closeLinks"
                            class="rounded-xl px-4 py-2 text-sm text-gray-500 ring-1 ring-white/10 transition hover:text-gray-300">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
