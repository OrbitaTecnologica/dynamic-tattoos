<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-3xl">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('profile.index') }}" class="lp-btn-ghost">← Volver al panel</a>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span>Paso</span>
                <span class="font-bold text-white">{{ $step }}</span>
                <span>/ 3</span>
            </div>
        </div>

        {{-- Progress --}}
        <div class="flex gap-2 mb-8">
            @for ($i = 1; $i <= 3; $i++)
                <div class="flex-1 h-1.5 rounded-full"
                     style="background: {{ $i <= $step ? 'linear-gradient(90deg,#b30000,#ff1a1a)' : 'rgba(255,255,255,0.08)' }};"></div>
            @endfor
        </div>

        <div class="lp-glass p-6 sm:p-8">

            {{-- ============== PASO 1: identidad ============== --}}
            @if ($step === 1)
                <h1 class="text-2xl font-bold text-white mb-1">Vamos a crear tu tarjeta</h1>
                <p class="text-sm text-slate-400 mb-6">Empezamos por lo básico: cómo quieres aparecer y qué URL pública tendrá tu tarjeta.</p>

                <div class="space-y-5">
                    <div>
                        <label class="lp-label">Nombre o título visible</label>
                        <input type="text" class="lp-input" wire:model.blur="title" placeholder="Tu nombre / estudio / marca">
                        @error('title') <p class="lp-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="lp-label">URL pública</label>
                        <div class="flex items-stretch rounded-[10px] overflow-hidden border border-white/10 focus-within:border-[#ff3333]">
                            <span class="px-3 flex items-center text-sm text-slate-400 bg-white/5">{{ url('/u') }}/</span>
                            <input type="text" class="lp-input border-0 rounded-none" wire:model.live.debounce.300ms="slug" placeholder="tu-perfil">
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5">Mínimo 3 caracteres. Minúsculas, números, guiones y puntos.</p>
                        @error('slug') <p class="lp-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="lp-label">Bio (opcional)</label>
                        <textarea rows="3" class="lp-input" wire:model.blur="bio" maxlength="280"
                                  placeholder="Cuenta brevemente quién eres o qué ofreces"></textarea>
                        @error('bio') <p class="lp-error">{{ $message }}</p> @enderror
                    </div>
                </div>

            {{-- ============== PASO 2: redes ============== --}}
            @elseif ($step === 2)
                <h1 class="text-2xl font-bold text-white mb-1">Selecciona tus redes y contactos</h1>
                <p class="text-sm text-slate-400 mb-6">Activa las que quieras incluir en tu tarjeta. Podrás añadir más o reordenarlas después.</p>

                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach ($catalog as $type => $meta)
                        <button type="button"
                                wire:click="toggleNetwork('{{ $type }}')"
                                class="lp-chip {{ array_key_exists($type, $selected) ? 'is-on' : '' }}">
                            <span class="lp-chip-icon" style="color: {{ $meta['color'] }};">{!! $meta['icon'] !!}</span>
                            <span>{{ $meta['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                @if (count($selected) > 0)
                    <div class="space-y-3 border-t border-white/10 pt-5">
                        @foreach ($selected as $type => $value)
                            @php $meta = $catalog[$type]; @endphp
                            <div>
                                <label class="lp-label flex items-center gap-2">
                                    <span style="color:{{ $meta['color'] }};display:inline-flex;width:14px;height:14px;">{!! $meta['icon'] !!}</span>
                                    {{ $meta['label'] }}
                                </label>
                                <input type="text" class="lp-input"
                                       wire:model.blur="selected.{{ $type }}"
                                       placeholder="{{ $meta['placeholder'] ?? '' }}">
                                @if (!empty($meta['help']))
                                    <p class="text-xs text-slate-500 mt-1">{{ $meta['help'] }}</p>
                                @endif
                                @error("selected.$type") <p class="lp-error">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 italic">Aún no has seleccionado ninguna. Elige al menos una arriba (o continúa y las añades después).</p>
                @endif

            {{-- ============== PASO 3: tema ============== --}}
            @else
                <h1 class="text-2xl font-bold text-white mb-1">Elige un estilo</h1>
                <p class="text-sm text-slate-400 mb-6">Esto es solo el punto de partida; en el editor podrás ajustar colores, fuente y forma de los botones.</p>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach ($themes as $key => $t)
                        @continue($key === 'dtattoos')
                        <button type="button"
                                wire:key="onb-theme-{{ $key }}"
                                wire:click.prevent="pickTheme('{{ $key }}')"
                                class="relative text-left rounded-2xl overflow-hidden border transition cursor-pointer
                                       {{ $theme === $key ? 'border-[#ff4d4d] ring-2 ring-[#ff4d4d]/40' : 'border-white/10 hover:border-white/25' }}">
                            <div style="background: {{ $t['bg'] }}; height: 110px; pointer-events:none;"></div>
                            <div class="p-3 bg-[#0b0d12]" style="pointer-events:none;">
                                <div class="text-sm font-semibold text-white">{{ $t['name'] }}</div>
                                <div class="text-xs text-slate-400 mt-0.5 leading-snug">{{ $t['description'] }}</div>
                            </div>
                            @if ($theme === $key)
                                <span class="absolute top-2 right-2 text-xs font-bold px-2 py-0.5 rounded-full"
                                      style="background:linear-gradient(135deg,#b30000,#ff1a1a);color:#fff;pointer-events:none;">Elegido</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Footer --}}
            <div class="flex items-center justify-between mt-8">
                <button type="button" wire:click="back" class="lp-btn-secondary {{ $step === 1 ? 'invisible' : '' }}">← Atrás</button>

                @if ($step < 3)
                    <button type="button" wire:click="next" class="lp-btn-primary">Siguiente →</button>
                @else
                    <button type="button" wire:click="finish" class="lp-btn-primary">Crear mi tarjeta ✨</button>
                @endif
            </div>
        </div>
    </div>
</div>
