@php
    // Parámetros:
    //  $page (LinkPage), $title, $bio, $theme (array resuelto),
    //  $links (Collection de LinkPageLink activos), $catalog (array LinkCatalog::all()),
    //  $embedded (bool) — true en el preview, false en la página pública.
    use App\Services\LinkCatalog;
    use App\Services\LinkPageThemes;

    $cssVars  = LinkPageThemes::toCssVars($theme);
    $btnStyle = $theme['button_style'] ?? 'solid';
    $embedded = $embedded ?? false;
    $title    = $title ?? $page->title;
    $bio      = $bio ?? $page->bio;
    $catalog  = $catalog ?? LinkCatalog::all();
    $scope    = 'lp-card-' . $page->id . ($embedded ? '-preview' : '');
    $themeKey = $page->theme_key ?? LinkPageThemes::DEFAULT_KEY;
@endphp

<style>
    .{{ $scope }} {
        {!! $cssVars !!}
        --lp-gap: 14px;
        background: var(--lp-bg);
        color: var(--lp-text);
        font-family: var(--lp-font);
        min-height: 100%;
        position: relative;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* ===== Cabecera con cover y degradado de legibilidad ===== */
    .{{ $scope }} .lp-header {
        position: relative;
        height: 150px;
        background: var(--lp-fade);
        overflow: hidden;
    }
    .{{ $scope }} .lp-cover {
        position: absolute; inset: 0;
        background-size: cover; background-position: center;
        filter: saturate(1.05);
    }
    .{{ $scope }} .lp-cover::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(180deg, transparent 30%, var(--lp-bg) 100%);
    }
    .{{ $scope }} .lp-header.no-cover {
        height: 90px;
        background: linear-gradient(180deg, var(--lp-fade), transparent);
    }

    /* ===== Contenido principal centrado ===== */
    .{{ $scope }} .lp-body {
        max-width: 480px;
        margin: 0 auto;
        padding: 0 22px 56px;
        position: relative;
        z-index: 2;
        text-align: center;
    }

    /* ===== Avatar elevado sobre la cabecera ===== */
    .{{ $scope }} .lp-avatar-wrap {
        margin-top: -48px;
        margin-bottom: 18px;
        display: flex; justify-content: center;
    }
    .{{ $scope }} .lp-avatar {
        width: 96px; height: 96px;
        border-radius: 999px;
        background: var(--lp-fade);
        background-size: cover; background-position: center;
        border: 4px solid var(--lp-bg);
        box-shadow:
            0 18px 40px -18px rgba(0,0,0,0.55),
            0 0 0 1px rgba(255,255,255,0.06);
        position: relative;
    }
    .{{ $scope }} .lp-avatar.is-placeholder::before {
        content: '';
        position: absolute; inset: 0;
        border-radius: inherit;
        background:
            radial-gradient(120% 80% at 50% 20%, rgba(255,255,255,0.12), transparent 60%),
            var(--lp-fade);
    }

    /* ===== Tipografía ===== */
    .{{ $scope }} .lp-title {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.01em;
        line-height: 1.2;
    }
    .{{ $scope }} .lp-handle {
        display: inline-block;
        font-size: .75rem;
        color: var(--lp-muted);
        margin-top: 4px;
        opacity: .8;
        letter-spacing: .02em;
    }
    .{{ $scope }} .lp-bio {
        color: var(--lp-muted);
        font-size: .9rem;
        line-height: 1.55;
        margin: 14px auto 26px;
        max-width: 38ch;
    }

    /* ===== Lista de enlaces ===== */
    .{{ $scope }} .lp-links {
        display: flex;
        flex-direction: column;
        gap: var(--lp-gap);
        margin-top: 4px;
    }
    .{{ $scope }} a.lp-link {
        position: relative;
        display: grid;
        grid-template-columns: 36px 1fr 36px;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 14px 16px;
        background: var(--lp-btn-bg);
        color: var(--lp-btn-text);
        border-radius: var(--lp-btn-radius);
        font-weight: 600;
        font-size: .95rem;
        line-height: 1.2;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease, background .18s ease;
        border: {{ $btnStyle === 'outline' ? '1.5px solid currentColor' : '1px solid rgba(255,255,255,0.05)' }};
        box-shadow:
            {{ $btnStyle === 'hard-shadow' ? '4px 4px 0 0 rgba(0,0,0,0.85)' : (
               $btnStyle === 'glow'        ? '0 0 28px var(--lp-fade), 0 6px 18px -10px rgba(0,0,0,0.45)' : (
               $btnStyle === 'glass'       ? 'inset 0 1px 0 0 rgba(255,255,255,0.12), 0 8px 24px -12px rgba(0,0,0,0.45)' :
                                             '0 6px 18px -12px rgba(0,0,0,0.55)')) }};
        backdrop-filter: {{ $btnStyle === 'glass' ? 'blur(14px) saturate(140%)' : 'none' }};
        -webkit-backdrop-filter: {{ $btnStyle === 'glass' ? 'blur(14px) saturate(140%)' : 'none' }};
        cursor: pointer;
    }
    .{{ $scope }} a.lp-link:hover {
        transform: translateY(-2px);
        filter: brightness(1.06);
        box-shadow: {{ $btnStyle === 'hard-shadow' ? '6px 6px 0 0 rgba(0,0,0,0.85)' : '0 14px 32px -16px rgba(0,0,0,0.55)' }};
    }
    .{{ $scope }} a.lp-link:active { transform: translateY(0); }
    .{{ $scope }} a.lp-link:focus-visible {
        outline: 2px solid currentColor;
        outline-offset: 3px;
    }
    .{{ $scope }} a.lp-link .lp-icon {
        display: inline-flex;
        align-items: center; justify-content: center;
        width: 32px; height: 32px;
        border-radius: 10px;
        background: rgba(255,255,255,0.10);
        flex-shrink: 0;
    }
    .{{ $scope }} a.lp-link .lp-icon svg { width: 18px; height: 18px; }
    .{{ $scope }} a.lp-link .lp-label {
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .{{ $scope }} a.lp-link .lp-chev {
        display: inline-flex; justify-content: flex-end;
        opacity: .45;
        transition: opacity .18s, transform .18s;
    }
    .{{ $scope }} a.lp-link:hover .lp-chev {
        opacity: .9;
        transform: translateX(2px);
    }

    /* Estado vacío */
    .{{ $scope }} .lp-empty {
        color: var(--lp-muted);
        font-size: .9rem;
        margin: 40px auto 0;
        padding: 22px 16px;
        border: 1px dashed rgba(255,255,255,0.14);
        border-radius: 14px;
        max-width: 320px;
    }

    /* ===== Footer (consistente en todos los temas) ===== */
    .{{ $scope }} .lp-foot {
        margin: 40px auto 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 6px 14px;
        font-size: .65rem;
        color: var(--lp-text);
        background: rgba(0,0,0,0.18);
        border: 1px solid rgba(255,255,255,0.10);
        border-radius: 999px;
        letter-spacing: .14em;
        text-transform: uppercase;
        font-weight: 700;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    .{{ $scope }} .lp-foot-wrap {
        margin-top: 40px;
        text-align: center;
    }
    .{{ $scope }} .lp-foot-wrap .lp-foot { margin-top: 0; }

    /* ============================================================
       === Personalidad por tema ===================================
       ============================================================ */

    /* ====== NEON ====== */
    .{{ $scope }}[data-theme="neon"] {
        background:
            linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px) 0 0 / 28px 28px,
            linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px) 0 0 / 28px 28px,
            var(--lp-bg);
    }
    .{{ $scope }}[data-theme="neon"] .lp-foot {
        color: #e0e7ff;
        background: rgba(11,2,32,0.55);
        border-color: rgba(168,85,247,0.55);
        box-shadow: 0 0 14px rgba(168,85,247,0.35);
    }
    .{{ $scope }}[data-theme="neon"] .lp-header.no-cover {
        background:
            radial-gradient(60% 90% at 50% 100%, rgba(34,211,238,0.30), transparent 70%),
            radial-gradient(60% 90% at 50% 0%, rgba(168,85,247,0.35), transparent 70%);
    }
    .{{ $scope }}[data-theme="neon"] .lp-avatar {
        border-color: #0b0220;
        box-shadow:
            0 0 0 2px rgba(168,85,247,0.55),
            0 0 30px 4px rgba(34,211,238,0.45),
            0 18px 40px -18px rgba(0,0,0,0.7);
    }
    .{{ $scope }}[data-theme="neon"] .lp-title {
        text-shadow:
            0 0 14px rgba(168,85,247,0.55),
            0 0 32px rgba(34,211,238,0.35);
        letter-spacing: .01em;
    }
    .{{ $scope }}[data-theme="neon"] .lp-handle {
        color: #67e8f9;
        text-transform: uppercase;
        letter-spacing: .18em;
        font-weight: 600;
    }
    .{{ $scope }}[data-theme="neon"] a.lp-link {
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        font-size: .85rem;
        border: 1px solid rgba(255,255,255,0.18);
    }
    .{{ $scope }}[data-theme="neon"] a.lp-link:hover {
        box-shadow:
            0 0 24px rgba(34,211,238,0.55),
            0 0 40px rgba(168,85,247,0.45);
    }
    .{{ $scope }}[data-theme="neon"] a.lp-link .lp-icon {
        background: rgba(11,2,32,0.35);
    }

    /* ====== PASTEL ====== */
    .{{ $scope }}[data-theme="pastel"] { font-weight: 500; }
    .{{ $scope }}[data-theme="pastel"] .lp-foot {
        color: #7c2d12;
        background: rgba(255,255,255,0.55);
        border-color: rgba(124,45,18,0.20);
    }
    .{{ $scope }}[data-theme="pastel"] .lp-header.no-cover {
        background:
            radial-gradient(50% 70% at 20% 100%, rgba(251,113,133,0.35), transparent 60%),
            radial-gradient(50% 70% at 85% 80%, rgba(252,211,77,0.45), transparent 60%);
    }
    .{{ $scope }}[data-theme="pastel"] .lp-body::before {
        content: '';
        position: absolute;
        top: -20px; right: -40px;
        width: 160px; height: 160px;
        background: radial-gradient(closest-side, rgba(251,191,36,0.28), transparent 70%);
        border-radius: 50%;
        pointer-events: none; z-index: 0;
    }
    .{{ $scope }}[data-theme="pastel"] .lp-body::after {
        content: '';
        position: absolute;
        bottom: 40px; left: -50px;
        width: 180px; height: 180px;
        background: radial-gradient(closest-side, rgba(251,113,133,0.22), transparent 70%);
        border-radius: 50%;
        pointer-events: none; z-index: 0;
    }
    .{{ $scope }}[data-theme="pastel"] .lp-avatar-wrap,
    .{{ $scope }}[data-theme="pastel"] .lp-title,
    .{{ $scope }}[data-theme="pastel"] .lp-handle,
    .{{ $scope }}[data-theme="pastel"] .lp-bio,
    .{{ $scope }}[data-theme="pastel"] .lp-links,
    .{{ $scope }}[data-theme="pastel"] .lp-foot-wrap { position: relative; z-index: 1; }
    .{{ $scope }}[data-theme="pastel"] .lp-avatar {
        border-color: #fff1f2;
        box-shadow:
            0 0 0 3px rgba(251,113,133,0.35),
            0 18px 30px -14px rgba(180,83,9,0.35);
    }
    .{{ $scope }}[data-theme="pastel"] .lp-title {
        font-weight: 700;
        background: linear-gradient(135deg, #be185d, #b45309);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .{{ $scope }}[data-theme="pastel"] a.lp-link {
        box-shadow:
            0 10px 24px -10px rgba(251,113,133,0.55),
            inset 0 1px 0 rgba(255,255,255,0.30);
    }
    .{{ $scope }}[data-theme="pastel"] a.lp-link:hover {
        box-shadow:
            0 14px 32px -10px rgba(251,113,133,0.65),
            inset 0 1px 0 rgba(255,255,255,0.40);
    }
    .{{ $scope }}[data-theme="pastel"] a.lp-link .lp-icon {
        background: rgba(255,255,255,0.30);
    }

    /* ====== GLASS ====== */
    .{{ $scope }}[data-theme="glass"] {
        background: var(--lp-bg);
        background-size: 200% 200%;
        animation: lp-glass-shift 18s ease-in-out infinite;
    }
    .{{ $scope }}[data-theme="glass"] .lp-foot {
        color: #ffffff;
        background: rgba(255,255,255,0.18);
        border-color: rgba(255,255,255,0.40);
        text-shadow: 0 1px 2px rgba(0,0,0,0.25);
    }
    @keyframes lp-glass-shift {
        0%,100% { background-position: 0% 50%; }
        50%     { background-position: 100% 50%; }
    }
    .{{ $scope }}[data-theme="glass"] .lp-header.no-cover { background: transparent; }
    .{{ $scope }}[data-theme="glass"] .lp-header::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(60% 80% at 30% 0%, rgba(255,255,255,0.25), transparent 60%),
            radial-gradient(40% 60% at 90% 100%, rgba(255,255,255,0.18), transparent 60%);
        pointer-events: none;
    }
    .{{ $scope }}[data-theme="glass"] .lp-avatar {
        border-color: rgba(255,255,255,0.55);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow:
            0 0 0 1px rgba(255,255,255,0.30),
            0 20px 40px -16px rgba(0,0,0,0.45);
    }
    .{{ $scope }}[data-theme="glass"] .lp-title { font-weight: 700; letter-spacing: -.015em; }
    .{{ $scope }}[data-theme="glass"] .lp-handle {
        background: rgba(255,255,255,0.18);
        padding: 3px 10px;
        border-radius: 999px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.25);
    }
    .{{ $scope }}[data-theme="glass"] a.lp-link {
        border: 1px solid rgba(255,255,255,0.30);
    }
    .{{ $scope }}[data-theme="glass"] a.lp-link .lp-icon {
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.20);
    }
    .{{ $scope }}[data-theme="glass"] .lp-empty {
        background: rgba(255,255,255,0.10);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-color: rgba(255,255,255,0.30);
    }

    /* ====== RETRO ====== */
    .{{ $scope }}[data-theme="retro"] {
        background:
            repeating-linear-gradient(135deg, rgba(28,25,23,0.05) 0 12px, transparent 12px 24px),
            var(--lp-bg);
    }
    .{{ $scope }}[data-theme="retro"] .lp-header.no-cover {
        background:
            repeating-linear-gradient(90deg, rgba(28,25,23,0.10) 0 8px, transparent 8px 16px),
            linear-gradient(180deg, rgba(28,25,23,0.15), transparent);
    }
    .{{ $scope }}[data-theme="retro"] .lp-avatar {
        border-radius: 18px;
        border: 3px solid #1c1917;
        box-shadow: 6px 6px 0 0 #1c1917;
    }
    .{{ $scope }}[data-theme="retro"] .lp-avatar.is-placeholder::before { border-radius: inherit; }
    .{{ $scope }}[data-theme="retro"] .lp-title {
        font-size: 2.4rem;
        line-height: 1;
        letter-spacing: .04em;
        text-transform: uppercase;
        font-weight: 400;
    }
    .{{ $scope }}[data-theme="retro"] .lp-handle {
        background: #1c1917;
        color: #fde68a;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .15em;
        font-size: .68rem;
        box-shadow: 3px 3px 0 0 rgba(28,25,23,0.5);
    }
    .{{ $scope }}[data-theme="retro"] .lp-bio {
        font-family: Georgia, serif;
        font-style: italic;
        font-size: .95rem;
    }
    .{{ $scope }}[data-theme="retro"] a.lp-link {
        text-transform: uppercase;
        letter-spacing: .14em;
        font-size: 1.05rem;
        font-weight: 400;
        border: 2px solid #1c1917;
    }
    .{{ $scope }}[data-theme="retro"] a.lp-link:hover {
        transform: translate(-2px, -2px);
        box-shadow: 7px 7px 0 0 rgba(28,25,23,0.9);
    }
    .{{ $scope }}[data-theme="retro"] a.lp-link .lp-icon {
        background: #fde68a;
        color: #1c1917 !important;
        border-radius: 4px;
    }
    .{{ $scope }}[data-theme="retro"] .lp-empty {
        border: 2px dashed #1c1917;
        background: rgba(255,255,255,0.20);
        color: #1c1917;
        border-radius: 6px;
    }
    .{{ $scope }}[data-theme="retro"] .lp-foot {
        color: #fde68a;
        background: #1c1917;
        border-color: #1c1917;
        border-radius: 4px;
        box-shadow: 3px 3px 0 0 rgba(28,25,23,0.5);
    }
</style>

<div class="{{ $scope }}" data-theme="{{ $themeKey }}">
    @if ($page->cover_path)
        <div class="lp-header">
            <div class="lp-cover" style="background-image:url('{{ asset('storage/' . $page->cover_path) }}');"></div>
        </div>
    @else
        <div class="lp-header no-cover"></div>
    @endif

    <div class="lp-body">
        <div class="lp-avatar-wrap">
            <div class="lp-avatar {{ $page->avatar_path ? '' : 'is-placeholder' }}"
                 @if($page->avatar_path) style="background-image:url('{{ asset('storage/' . $page->avatar_path) }}');" @endif></div>
        </div>

        <h1 class="lp-title">{{ $title ?: 'Tu título' }}</h1>
        <span class="lp-handle">/{{ $page->slug }}</span>

        @if ($bio)
            <p class="lp-bio">{{ $bio }}</p>
        @else
            <div style="height:22px;"></div>
        @endif

        <div class="lp-links">
            @forelse ($links as $link)
                @php
                    $meta = $catalog[$link->type] ?? $catalog['custom'];
                    $href = $embedded ? '#' : route('link-page.redirect', ['slug' => $page->slug, 'link' => $link->id]);
                @endphp
                <a class="lp-link"
                   href="{{ $href }}"
                   @if(! $embedded) rel="noopener" target="_blank" @endif
                   @if($embedded) onclick="return false;" @endif>
                    <span class="lp-icon" style="color:{{ $meta['color'] }};">{!! $meta['icon'] !!}</span>
                    <span class="lp-label">{{ $link->displayLabel() }}</span>
                    <span class="lp-chev" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 6l6 6-6 6"/>
                        </svg>
                    </span>
                </a>
            @empty
                <div class="lp-empty">Aún no hay enlaces.</div>
            @endforelse
        </div>

        <div class="lp-foot-wrap">
            <span class="lp-foot">hecho con dtattoos</span>
        </div>
    </div>
</div>
