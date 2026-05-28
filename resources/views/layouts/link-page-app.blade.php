<!DOCTYPE html>
<html lang="es" class="bg-[#0b0d12]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'dtattoos' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ \App\Services\LinkPageThemes::fontsCssUrl() }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/link-page.js'])
    @livewireStyles
    <style>
        :root { color-scheme: dark; }
        body {
            background:
                radial-gradient(1200px 600px at 80% -10%, rgba(180,0,0,0.28), transparent 60%),
                radial-gradient(900px 500px at -10% 20%, rgba(220,0,0,0.14), transparent 60%),
                #0b0d12;
            color: #e2e8f0;
            font-family: Inter, system-ui, -apple-system, sans-serif;
        }
        .lp-glass {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
            border-radius: 16px;
        }
        .lp-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.10);
            color: #e2e8f0;
            border-radius: 10px;
            padding: .65rem .9rem;
            font-size: .9rem;
            transition: border-color .18s, box-shadow .18s;
        }
        .lp-input::placeholder { color: #64748b; }
        .lp-input:focus { outline: none; border-color: #ff3333; box-shadow: 0 0 0 4px rgba(180,0,0,0.22); }
        .lp-btn-primary {
            background: linear-gradient(135deg, #b30000, #ff1a1a);
            color: #fff; font-weight: 600;
            border-radius: 10px; padding: .65rem 1.1rem; font-size: .9rem;
            box-shadow: 0 10px 30px -10px rgba(180,0,0,0.70);
            transition: transform .15s, box-shadow .15s;
            cursor: pointer; border: 0;
        }
        .lp-btn-primary:hover:not(:disabled) { transform: translateY(-1px); }
        .lp-btn-primary:disabled { opacity: .5; cursor: not-allowed; }
        .lp-btn-secondary {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            color: #e2e8f0;
            border-radius: 10px; padding: .55rem 1rem; font-size: .85rem;
            cursor: pointer;
        }
        .lp-btn-secondary:hover { background: rgba(255,255,255,0.10); }
        .lp-btn-ghost {
            background: transparent; border: 0; color: #94a3b8;
            padding: .4rem .7rem; cursor: pointer; font-size: .85rem;
        }
        .lp-btn-ghost:hover { color: #e2e8f0; }
        .lp-label { display:block; font-size:.7rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; margin-bottom:.4rem; }
        .lp-error { color: #fca5a5; font-size: .78rem; margin-top: .35rem; }
        .lp-chip {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .5rem .8rem; border-radius: 10px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            color: #cbd5e1; cursor: pointer; font-size: .85rem;
            transition: all .15s;
        }
        .lp-chip:hover { background: rgba(255,255,255,0.06); }
        .lp-chip.is-on {
            background: linear-gradient(135deg, rgba(179,0,0,0.22), rgba(255,26,26,0.10));
            border-color: rgba(255,77,77,0.5); color: #fff;
        }
        .lp-chip-icon { width: 18px; height: 18px; display:inline-flex; }
        .lp-color-row {
            display: flex; align-items: stretch; gap: .5rem;
        }
        .lp-color-row .lp-input { flex: 1; min-width: 0; font-family: ui-monospace, monospace; font-size: .8rem; }
        .lp-color-swatch {
            -webkit-appearance: none; appearance: none;
            width: 46px; height: auto; min-height: 42px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 0; cursor: pointer;
            background: transparent;
            flex-shrink: 0;
        }
        .lp-color-swatch::-webkit-color-swatch-wrapper { padding: 3px; }
        .lp-color-swatch::-webkit-color-swatch { border: none; border-radius: 7px; }
        .lp-color-swatch::-moz-color-swatch { border: none; border-radius: 7px; }
        .lp-color-swatch:hover { border-color: rgba(255,77,77,0.6); }
        .lp-select {
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'><path d='M6 9l6 6 6-6'/></svg>");
            background-repeat: no-repeat;
            background-position: right .8rem center;
            padding-right: 2.2rem;
            cursor: pointer;
        }
        .lp-select option { background: #0b0d12; color: #e2e8f0; }

        /* ===== Uploader (avatar / cover) ===== */
        .lp-uploader {
            position: relative;
            display: flex; align-items: center; gap: 14px;
            padding: 14px;
            border: 1.5px dashed rgba(255,255,255,0.16);
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.005));
            cursor: pointer;
            transition: border-color .18s, background .18s, transform .18s;
        }
        .lp-uploader:hover {
            border-color: rgba(255,77,77,0.55);
            background: linear-gradient(180deg, rgba(255,77,77,0.06), rgba(255,77,77,0.02));
        }
        .lp-uploader:focus-within {
            border-color: #ff3333;
            box-shadow: 0 0 0 4px rgba(180,0,0,0.22);
        }
        .lp-uploader.is-loading { opacity: .7; pointer-events: none; }
        .lp-uploader-input {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
            font-size: 0;
        }
        .lp-uploader-preview {
            flex-shrink: 0;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            color: #64748b;
            display: grid; place-items: center;
            overflow: hidden;
        }
        .lp-uploader-preview img { width: 100%; height: 100%; object-fit: cover; }
        .lp-uploader-preview svg { width: 24px; height: 24px; }
        .lp-uploader-preview--avatar { width: 64px; height: 64px; border-radius: 999px; }
        .lp-uploader-preview--cover { width: 92px; height: 56px; border-radius: 10px; }
        .lp-uploader-body { flex: 1; min-width: 0; }
        .lp-uploader-title {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: .85rem; font-weight: 600; color: #fff;
        }
        .lp-uploader-hint {
            font-size: .72rem; color: #94a3b8;
            margin-top: 3px; line-height: 1.4;
        }
        .lp-uploader-progress {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .72rem; color: #fca5a5;
            margin-top: 6px;
        }
        .lp-spinner {
            display: inline-block;
            width: 12px; height: 12px;
            border: 2px solid rgba(252,165,165,0.25);
            border-top-color: #fca5a5;
            border-radius: 50%;
            animation: lp-spin .7s linear infinite;
        }
        @keyframes lp-spin { to { transform: rotate(360deg); } }
        .lp-uploader--wide .lp-uploader-preview--cover { width: 100px; height: 60px; }
        .lp-toast {
            position: fixed; bottom: 24px; right: 24px;
            background: rgba(15,23,42,0.95);
            border: 1px solid rgba(16,185,129,0.4);
            color: #6ee7b7;
            padding: .75rem 1.1rem; border-radius: 12px;
            font-size: .85rem; z-index: 80;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.6);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen">
    {{ $slot }}

    <div x-data="{ show:false, msg:'' }"
         x-on:lp:saved.window="msg = $event.detail.message || 'Guardado'; show = true; clearTimeout($el._t); $el._t = setTimeout(()=>show=false, 2200)"
         x-show="show" x-cloak x-transition class="lp-toast">
        <span x-text="msg"></span>
    </div>

    @livewireScripts
</body>
</html>
