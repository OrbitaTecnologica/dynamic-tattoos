@php
    use App\Services\LinkCatalog;
    use App\Services\LinkPageThemes;
    $theme    = LinkPageThemes::resolve($page->theme_key, $page->theme_overrides);
    $catalog  = LinkCatalog::all();
    $links    = $page->links->where('is_active', true)->values();
    $cssVars  = LinkPageThemes::toCssVars($theme);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title ?: $page->slug }} · links</title>
    <meta name="description" content="{{ $page->bio }}">
    <meta property="og:title" content="{{ $page->title ?: $page->slug }}">
    @if ($page->bio) <meta property="og:description" content="{{ $page->bio }}"> @endif
    @if ($page->avatar_path) <meta property="og:image" content="{{ asset('storage/' . $page->avatar_path) }}"> @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ LinkPageThemes::fontsCssUrl() }}" rel="stylesheet">
    <style>
        :root { color-scheme: light dark; {!! $cssVars !!} }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100%; }

        /* ===== MOBILE (por defecto) — tarjeta a pantalla completa ===== */
        body {
            font-family: var(--lp-font, system-ui, sans-serif);
            background: var(--lp-bg);
            color: var(--lp-text);
            min-height: 100vh;
        }
        .lp-page { min-height: 100vh; display: flex; flex-direction: column; }
        .lp-wrap {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            position: relative;
        }
        .lp-desktop-side { display: none; }
        .lp-desktop-foot { display: none; }

        /* ===== TABLET (>= 768px) — la tarjeta se convierte en pieza flotante ===== */
        @media (min-width: 768px) {
            body {
                /* fondo ambiente del tema en todo el viewport */
                background: var(--lp-bg);
                background-attachment: fixed;
            }
            .lp-page {
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 40px 24px;
            }
            .lp-wrap {
                max-width: 460px;
                min-height: auto;
                width: 100%;
                border-radius: 28px;
                overflow: hidden;
                box-shadow:
                    0 40px 80px -30px rgba(0,0,0,0.55),
                    0 18px 40px -20px rgba(0,0,0,0.35),
                    0 0 0 1px rgba(255,255,255,0.06);
                isolation: isolate;
            }
            .lp-desktop-foot {
                display: block;
                margin-top: 18px;
                text-align: center;
                font-size: .7rem;
                letter-spacing: .14em;
                text-transform: uppercase;
                color: rgba(255,255,255,0.75);
                text-shadow: 0 1px 2px rgba(0,0,0,0.35);
            }
            .lp-desktop-foot a { color: inherit; text-decoration: none; opacity: .8; }
            .lp-desktop-foot a:hover { opacity: 1; }
        }

        /* ===== DESKTOP grande (>= 1100px) — layout split con info lateral ===== */
        @media (min-width: 1100px) {
            .lp-page {
                grid-template-columns: 1fr 460px 1fr;
                gap: 48px;
                padding: 48px 64px;
            }
            .lp-wrap { grid-column: 2; }
            .lp-desktop-side {
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 18px;
                grid-column: 3;
                color: var(--lp-text);
                max-width: 360px;
            }
            .lp-desktop-side h2 {
                font-family: var(--lp-font);
                font-size: 2.1rem;
                line-height: 1.15;
                margin: 0;
                letter-spacing: -.01em;
                font-weight: 700;
            }
            .lp-desktop-side p {
                margin: 0;
                font-size: .95rem;
                line-height: 1.55;
                opacity: .85;
            }
            .lp-desktop-side .lp-side-actions {
                display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px;
            }
            .lp-desktop-side .lp-side-btn {
                display: inline-flex; align-items: center; gap: 8px;
                padding: 10px 16px;
                border-radius: 999px;
                background: rgba(0,0,0,0.25);
                border: 1px solid rgba(255,255,255,0.18);
                color: inherit;
                font-size: .85rem; font-weight: 600;
                text-decoration: none;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                transition: transform .15s, background .15s;
            }
            .lp-desktop-side .lp-side-btn:hover {
                transform: translateY(-2px);
                background: rgba(0,0,0,0.35);
            }
            .lp-desktop-foot { display: none; } /* se mueve dentro del side */
        }
    </style>
</head>
<body>
    <div class="lp-page">
        <div class="lp-wrap">
            @include('public.link-page-card', [
                'page'     => $page,
                'title'    => $page->title,
                'bio'      => $page->bio,
                'theme'    => $theme,
                'links'    => $links,
                'catalog'  => $catalog,
                'embedded' => false,
            ])
        </div>

        {{-- Panel lateral solo en desktop grande --}}
        <aside class="lp-desktop-side" aria-hidden="true">
            <h2>{{ $page->title ?: $page->slug }}</h2>
            @if ($page->bio)
                <p>{{ $page->bio }}</p>
            @endif
            <div class="lp-side-actions">
                <a class="lp-side-btn" href="{{ $page->publicUrl() }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Copiar enlace
                </a>
                <a class="lp-side-btn" href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($page->publicUrl()) }}" target="_blank" rel="noopener">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM20 14h1M14 20h1M20 17v4"/></svg>
                    QR
                </a>
            </div>
            <div class="lp-desktop-foot" style="display:block; margin-top:24px;">
                hecho con · dtattoos
            </div>
        </aside>

        {{-- Pie visible en tablet (no llega al breakpoint grande) --}}
        <div class="lp-desktop-foot">hecho con · dtattoos</div>
    </div>
</body>
</html>
