<div x-data="lpEditor()" x-init="init()" class="min-h-screen">
    {{-- Top bar --}}
    <header class="border-b border-white/10 bg-[#0b0d12]/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('profile.index') }}" class="lp-btn-ghost">← Panel</a>
                <span class="text-sm text-slate-400 hidden sm:inline">/</span>
                <span class="text-sm font-semibold text-white">Tarjeta de links</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ $page->publicUrl() }}" target="_blank"
                   class="lp-btn-secondary inline-flex items-center gap-2">
                    <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg>
                    Ver pública
                </a>
                <button type="button" @click="openQr = true" class="lp-btn-secondary">QR</button>
            </div>
        </div>
        {{-- Tabs --}}
        <div class="max-w-7xl mx-auto px-4 flex gap-1 overflow-x-auto">
            @foreach (['content' => 'Contenido', 'design' => 'Diseño', 'analytics' => 'Estadísticas'] as $key => $label)
                <button type="button" wire:click="$set('tab', '{{ $key }}')"
                        class="px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap
                               {{ $tab === $key ? 'border-[#ff3333] text-white' : 'border-transparent text-slate-400 hover:text-slate-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6">
        {{-- ====================== COLUMNA IZQ: CONTROLES ====================== --}}
        <main class="space-y-6">

            {{-- =============== TAB: CONTENIDO =============== --}}
            @if ($tab === 'content')

                {{-- Identidad --}}
                <section class="lp-glass p-5">
                    <h2 class="text-base font-semibold text-white mb-4">Identidad</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="lp-label">Título visible</label>
                            <input type="text" class="lp-input" wire:model.live.debounce.400ms="title">
                            @error('title') <p class="lp-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="lp-label">URL pública</label>
                            <div class="flex items-stretch rounded-[10px] overflow-hidden border border-white/10">
                                <span class="px-3 flex items-center text-sm text-slate-400 bg-white/5">{{ url('/u') }}/</span>
                                <input type="text" class="lp-input border-0 rounded-none" wire:model.blur="slug">
                            </div>
                            @error('slug') <p class="lp-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="lp-label">Bio</label>
                            <textarea rows="3" class="lp-input" wire:model.live.debounce.500ms="bio" maxlength="280"></textarea>
                            @error('bio') <p class="lp-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="button" wire:click="saveIdentity" class="lp-btn-primary">Guardar</button>
                    </div>
                </section>

                {{-- Avatar + Cover --}}
                <section class="lp-glass p-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- ===== Avatar ===== --}}
                    <div>
                        <h3 class="text-sm font-semibold text-white mb-1">Avatar</h3>
                        <p class="text-xs text-slate-500 mb-3">Imagen circular, recomendado cuadrado 400×400 px. Máx 2 MB.</p>

                        <label class="lp-uploader" wire:loading.class="is-loading" wire:target="avatarUpload">
                            <input type="file" wire:model="avatarUpload" accept="image/*" class="lp-uploader-input">

                            <div class="lp-uploader-preview lp-uploader-preview--avatar">
                                @if ($page->avatar_path)
                                    <img src="{{ asset('storage/' . $page->avatar_path) }}" alt="Avatar actual">
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                @endif
                            </div>

                            <div class="lp-uploader-body">
                                <div class="lp-uploader-title">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span>{{ $page->avatar_path ? 'Cambiar avatar' : 'Subir avatar' }}</span>
                                </div>
                                <div class="lp-uploader-hint">Haz click o arrastra una imagen aquí · JPG, PNG, WEBP</div>
                                <div wire:loading wire:target="avatarUpload" class="lp-uploader-progress">
                                    <span class="lp-spinner"></span> Subiendo imagen…
                                </div>
                            </div>
                        </label>

                        @error('avatarUpload') <p class="lp-error">{{ $message }}</p> @enderror

                        @if ($page->avatar_path)
                            <button type="button" wire:click="removeAvatar" class="lp-btn-ghost text-xs mt-2" wire:confirm="¿Quitar el avatar?">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="display:inline;vertical-align:-2px;margin-right:4px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Quitar avatar
                            </button>
                        @endif
                    </div>

                    {{-- ===== Cover ===== --}}
                    <div>
                        <h3 class="text-sm font-semibold text-white mb-1">Cabecera</h3>
                        <p class="text-xs text-slate-500 mb-3">Imagen panorámica de fondo. Recomendado 1500×500 px. Máx 5 MB.</p>

                        <label class="lp-uploader lp-uploader--wide" wire:loading.class="is-loading" wire:target="coverUpload">
                            <input type="file" wire:model="coverUpload" accept="image/*" class="lp-uploader-input">

                            <div class="lp-uploader-preview lp-uploader-preview--cover">
                                @if ($page->cover_path)
                                    <img src="{{ asset('storage/' . $page->cover_path) }}" alt="Cabecera actual">
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 15l-5-5L5 19"/></svg>
                                @endif
                            </div>

                            <div class="lp-uploader-body">
                                <div class="lp-uploader-title">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span>{{ $page->cover_path ? 'Cambiar cabecera' : 'Subir cabecera' }}</span>
                                </div>
                                <div class="lp-uploader-hint">Haz click o arrastra una imagen aquí · JPG, PNG, WEBP</div>
                                <div wire:loading wire:target="coverUpload" class="lp-uploader-progress">
                                    <span class="lp-spinner"></span> Subiendo imagen…
                                </div>
                            </div>
                        </label>

                        @error('coverUpload') <p class="lp-error">{{ $message }}</p> @enderror

                        @if ($page->cover_path)
                            <button type="button" wire:click="removeCover" class="lp-btn-ghost text-xs mt-2" wire:confirm="¿Quitar la cabecera?">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="display:inline;vertical-align:-2px;margin-right:4px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Quitar cabecera
                            </button>
                        @endif
                    </div>
                </section>

                {{-- Lista de enlaces (drag & drop) --}}
                <section class="lp-glass p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-white">Enlaces</h2>
                        <span class="text-xs text-slate-400">{{ $page->links->count() }} en total</span>
                    </div>

                    @if ($page->links->isEmpty())
                        <p class="text-sm text-slate-500 italic mb-4">Aún no tienes enlaces. Añade el primero abajo ↓</p>
                    @endif

                    <ul x-ref="list" class="space-y-2">
                        @foreach ($page->links as $link)
                            @php $meta = $catalog[$link->type] ?? $catalog['custom']; @endphp
                            <li wire:key="link-{{ $link->id }}"
                                draggable="true"
                                data-id="{{ $link->id }}"
                                @dragstart="onDragStart($event)"
                                @dragover.prevent="onDragOver($event)"
                                @drop="onDrop($event)"
                                @dragend="onDragEnd($event)"
                                class="group flex items-center gap-3 p-3 rounded-xl border border-white/10 bg-white/[0.025] hover:bg-white/[0.05] transition">

                                <span class="cursor-grab text-slate-500 select-none" title="Arrastra para reordenar">⋮⋮</span>

                                <span class="w-9 h-9 rounded-lg grid place-items-center flex-shrink-0"
                                      style="background: {{ $meta['color'] }}22; color: {{ $meta['color'] }};">
                                    <span style="width:18px;height:18px;display:inline-flex;">{!! $meta['icon'] !!}</span>
                                </span>

                                <div class="flex-1 min-w-0 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input type="text"
                                           class="lp-input text-sm"
                                           value="{{ $link->label ?? $meta['label'] }}"
                                           placeholder="{{ $meta['label'] }}"
                                           wire:change="updateLink({{ $link->id }}, 'label', $event.target.value)">
                                    <input type="text"
                                           class="lp-input text-sm"
                                           value="{{ $link->value }}"
                                           placeholder="{{ $meta['placeholder'] ?? '' }}"
                                           wire:change="updateLink({{ $link->id }}, 'value', $event.target.value)">
                                </div>

                                <button type="button" wire:click="toggleLink({{ $link->id }})"
                                        class="text-xs font-semibold px-2.5 py-1 rounded-md border transition
                                               {{ $link->is_active ? 'border-emerald-400/50 text-emerald-300 bg-emerald-400/10' : 'border-white/15 text-slate-400 bg-white/[0.03]' }}"
                                        title="{{ $link->is_active ? 'Activo' : 'Oculto' }}">
                                    {{ $link->is_active ? 'ON' : 'OFF' }}
                                </button>

                                <button type="button"
                                        wire:click="deleteLink({{ $link->id }})"
                                        wire:confirm="¿Eliminar este enlace?"
                                        class="text-slate-500 hover:text-red-400 px-2"
                                        title="Eliminar">✕</button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Añadir nuevo --}}
                    <div class="mt-5 pt-5 border-t border-white/10">
                        <h3 class="text-sm font-semibold text-white mb-3">Añadir enlace</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr_1fr_auto] gap-2">
                            <select class="lp-input lp-select" wire:model.live="newType">
                                @foreach ($catalog as $type => $meta)
                                    <option value="{{ $type }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="lp-input" wire:model="newLabel" placeholder="Etiqueta (opcional)">
                            <input type="text" class="lp-input" wire:model="newValue" placeholder="{{ $catalog[$newType]['placeholder'] ?? '' }}">
                            <button type="button" wire:click="addLink" class="lp-btn-primary">Añadir</button>
                        </div>
                        @error('newValue') <p class="lp-error">{{ $message }}</p> @enderror
                        @if (!empty($catalog[$newType]['help']))
                            <p class="text-xs text-slate-500 mt-1.5">{{ $catalog[$newType]['help'] }}</p>
                        @endif
                    </div>
                </section>

            {{-- =============== TAB: DISEÑO =============== --}}
            @elseif ($tab === 'design')

                <section class="lp-glass p-5">
                    <h2 class="text-base font-semibold text-white mb-4">Tema</h2>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($themes as $key => $t)
                            @continue($key === 'dtattoos')
                            <button type="button"
                                    wire:key="ed-theme-{{ $key }}"
                                    wire:click.prevent="pickTheme('{{ $key }}')"
                                    class="relative text-left rounded-2xl overflow-hidden border transition cursor-pointer
                                           {{ $themeKey === $key ? 'border-[#ff4d4d] ring-2 ring-[#ff4d4d]/40' : 'border-white/10 hover:border-white/25' }}">
                                <div style="background: {{ $t['bg'] }}; height: 90px; pointer-events:none;"></div>
                                <div class="p-2.5 bg-[#0b0d12]" style="pointer-events:none;">
                                    <div class="text-xs font-semibold text-white">{{ $t['name'] }}</div>
                                </div>
                                @if ($themeKey === $key)
                                    <span class="absolute top-1.5 right-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                          style="background:linear-gradient(135deg,#b30000,#ff1a1a);color:#fff;pointer-events:none;">✓</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </section>

                <section class="lp-glass p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-white">Personalización fina</h2>
                        <button type="button" wire:click="resetThemeOverrides" class="lp-btn-ghost text-xs">Restaurar valores del tema</button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="lp-label">Color de texto</label>
                            <div class="lp-color-row">
                                <input type="color"
                                       class="lp-color-swatch"
                                       wire:model.live.debounce.200ms="themeOverrides.text_color"
                                       value="{{ $this->theme['text_color'] ?? '#f8fafc' }}">
                                <input type="text"
                                       class="lp-input"
                                       wire:model.live.debounce.300ms="themeOverrides.text_color"
                                       placeholder="#f8fafc">
                            </div>
                        </div>
                        <div>
                            <label class="lp-label">Fondo del botón</label>
                            <div class="lp-color-row">
                                <input type="color"
                                       class="lp-color-swatch"
                                       wire:model.live.debounce.200ms="themeOverrides.button_bg"
                                       value="{{ str_starts_with(trim($this->theme['button_bg'] ?? '#1f2937'), '#') ? $this->theme['button_bg'] : '#1f2937' }}">
                                <input type="text"
                                       class="lp-input"
                                       wire:model.live.debounce.300ms="themeOverrides.button_bg"
                                       placeholder="#1f2937 o gradiente">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">El selector aplica un color sólido. Puedes pegar un gradiente CSS en el texto.</p>
                        </div>
                        <div>
                            <label class="lp-label">Color del texto del botón</label>
                            <div class="lp-color-row">
                                <input type="color"
                                       class="lp-color-swatch"
                                       wire:model.live.debounce.200ms="themeOverrides.button_text"
                                       value="{{ $this->theme['button_text'] ?? '#ffffff' }}">
                                <input type="text"
                                       class="lp-input"
                                       wire:model.live.debounce.300ms="themeOverrides.button_text"
                                       placeholder="#ffffff">
                            </div>
                        </div>
                        <div>
                            <label class="lp-label">Radio del botón</label>
                            <input type="text" class="lp-input" wire:model.live.debounce.300ms="themeOverrides.button_radius" placeholder="12px / 999px">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="lp-label">Fuente</label>
                            <select class="lp-input lp-select" wire:model.live="themeOverrides.font">
                                <option value="">— Usar la fuente del tema —</option>
                                @foreach ($fonts as $fk => $f)
                                    <option value="{{ $f['stack'] }}" style="font-family: {{ $f['stack'] }};">{{ $f['label'] }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1" style="font-family: {{ $this->theme['font'] ?? 'inherit' }};">
                                Aa Bb Cc — vista previa con la fuente seleccionada.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button" wire:click="saveTheme" class="lp-btn-primary">Guardar diseño</button>
                    </div>
                </section>

            {{-- =============== TAB: ESTADÍSTICAS =============== --}}
            @else
                <section class="lp-glass p-5">
                    <h2 class="text-base font-semibold text-white mb-4">Resumen</h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="p-4 rounded-xl bg-white/[0.03] border border-white/10">
                            <div class="text-xs text-slate-400 uppercase tracking-wider">Visitas</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ number_format($page->views_count) }}</div>
                        </div>
                        <div class="p-4 rounded-xl bg-white/[0.03] border border-white/10">
                            <div class="text-xs text-slate-400 uppercase tracking-wider">Clics totales</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalClicks) }}</div>
                        </div>
                        <div class="p-4 rounded-xl bg-white/[0.03] border border-white/10">
                            <div class="text-xs text-slate-400 uppercase tracking-wider">Enlaces activos</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ $page->links->where('is_active', true)->count() }}</div>
                        </div>
                    </div>
                </section>

                <section class="lp-glass p-5">
                    <h2 class="text-base font-semibold text-white mb-4">Clics por enlace</h2>
                    @if ($page->links->isEmpty())
                        <p class="text-sm text-slate-500 italic">Aún no hay enlaces.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($page->links->sortByDesc('clicks_count') as $link)
                                @php
                                    $meta = $catalog[$link->type] ?? $catalog['custom'];
                                    $max = max(1, $page->links->max('clicks_count'));
                                    $pct = round(($link->clicks_count / $max) * 100);
                                @endphp
                                <li class="flex items-center gap-3 p-3 rounded-lg bg-white/[0.02] border border-white/10">
                                    <span class="w-8 h-8 rounded-lg grid place-items-center" style="background: {{ $meta['color'] }}22; color: {{ $meta['color'] }};">
                                        <span style="width:16px;height:16px;display:inline-flex;">{!! $meta['icon'] !!}</span>
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm text-white truncate">{{ $link->displayLabel() }}</div>
                                        <div class="mt-1 h-1.5 bg-white/5 rounded-full overflow-hidden">
                                            <div class="h-full" style="width:{{ $pct }}%; background: linear-gradient(90deg,#b30000,#ff1a1a);"></div>
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-white tabular-nums">{{ number_format($link->clicks_count) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
        </main>

        {{-- ====================== COLUMNA DCHA: PREVIEW ====================== --}}
        <aside class="lg:sticky lg:top-[125px] self-start">
            <div class="lp-glass p-3">
                <div class="flex items-center justify-between mb-2 px-2 pt-1">
                    <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Vista previa</span>
                    <span class="text-[10px] text-emerald-300">en vivo</span>
                </div>
                {{-- Marco tipo móvil --}}
                <div class="rounded-[28px] border border-white/10 overflow-hidden bg-black mx-auto" style="max-width:340px;">
                    <div class="relative" style="height: 600px; overflow:hidden;">
                        @include('public.link-page-card', [
                            'page'         => $page,
                            'title'        => $title,
                            'bio'          => $bio,
                            'theme'        => $this->theme,
                            'links'        => $page->links->where('is_active', true)->values(),
                            'catalog'      => $catalog,
                            'embedded'     => true,
                        ])
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- Modal QR --}}
    <div x-show="openQr" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 grid place-items-center bg-black/70 backdrop-blur-sm p-4"
         @click.self="openQr = false">
        <div class="lp-glass p-6 max-w-sm w-full text-center" @click.stop>
            <h3 class="text-lg font-semibold text-white mb-1">QR de tu tarjeta</h3>
            <p class="text-xs text-slate-400 mb-4 break-all">{{ $page->publicUrl() }}</p>
            <div class="flex justify-center mb-4">
                <div x-ref="qrTarget"
                     class="bg-white rounded-xl shadow-lg"
                     style="padding:16px; line-height:0; display:inline-block;"></div>
            </div>
            <button type="button" @click="openQr = false" class="lp-btn-secondary">Cerrar</button>
        </div>
    </div>
</div>

@script
<script>
    window.lpEditor = function () {
        return {
            openQr: false,
            dragSrc: null,
            qrInstance: null,

            init() {
                this.$watch('openQr', (v) => {
                    if (v) this.$nextTick(() => this.renderQr());
                });
            },

            renderQr() {
                if (!window.QRCodeStyling) return;
                this.$refs.qrTarget.innerHTML = '';
                this.qrInstance = new window.QRCodeStyling({
                    width: 240, height: 240,
                    margin: 0,
                    data: @json($page->publicUrl()),
                    dotsOptions: { color: '#0b0d12', type: 'rounded' },
                    backgroundOptions: { color: '#ffffff' },
                });
                this.qrInstance.append(this.$refs.qrTarget);
                // Asegura que el canvas se renderice como bloque y centrado.
                this.$nextTick(() => {
                    const node = this.$refs.qrTarget.querySelector('canvas, svg');
                    if (node) {
                        node.style.display = 'block';
                        node.style.width = '240px';
                        node.style.height = '240px';
                    }
                });
            },

            onDragStart(e) {
                this.dragSrc = e.currentTarget;
                e.currentTarget.style.opacity = '0.4';
                e.dataTransfer.effectAllowed = 'move';
            },
            onDragOver(e) {
                e.dataTransfer.dropEffect = 'move';
                const target = e.currentTarget;
                if (!this.dragSrc || this.dragSrc === target) return;
                const rect = target.getBoundingClientRect();
                const after = (e.clientY - rect.top) > rect.height / 2;
                target.parentNode.insertBefore(this.dragSrc, after ? target.nextSibling : target);
            },
            onDrop(e) { e.preventDefault(); },
            onDragEnd(e) {
                e.currentTarget.style.opacity = '';
                const ids = [...this.$refs.list.querySelectorAll('[data-id]')].map(el => parseInt(el.dataset.id, 10));
                this.$wire.reorderLinks(ids);
                this.dragSrc = null;
            },
        }
    }
</script>
@endscript
