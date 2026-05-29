<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>QR Studio — Dynamic Tattoos</title>
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="robots" content="noindex, nofollow" />
<link rel="icon" type="image/png" href="{{ asset('images/designer/dynamic-tattoos-icon.png') }}" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.6.0-rc.1/lib/qr-code-styling.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
  /* ============= Tokens (matched to landing) ============= */
  :root{
    --paper:#ffffff;
    --paper-2:#f5f5f5;
    --paper-3:#ebebeb;
    --ink:#1a1a1a;
    --ink-2:#2a2a2a;
    --graphite:#4a4a4a;
    --ash:#6b6b6b;
    --ash-2:#9a9a9a;
    --light-gray:#c8c8c8;
    --red:#b3252c;
    --red-deep:#8a1c22;
    --rule:#1a1a1a;
    --ok:#1a7a3e;
    --warn:#b27300;
    --display:'Sora', system-ui, sans-serif;
    --sans:'Manrope', system-ui, sans-serif;
    --mono:'JetBrains Mono', ui-monospace, monospace;
  }
  *{box-sizing:border-box}
  html,body{margin:0;padding:0}
  body{
    background:var(--paper);color:var(--ink);
    font-family:var(--sans);font-size:16px;line-height:1.5;
    -webkit-font-smoothing:antialiased;overflow-x:hidden;
    padding-top:72px;
  }
  body::before{
    content:"";position:fixed;inset:0;pointer-events:none;z-index:1000;
    opacity:.28;mix-blend-mode:multiply;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='240'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 .35 0'/></filter><rect width='100%25' height='100%25' filter='url(%23n)'/></svg>");
  }
  .mono{font-family:var(--mono);font-weight:400}
  .upper{text-transform:uppercase;letter-spacing:.14em}
  .wrap{max-width:1360px;margin:0 auto;padding:0 40px;position:relative}
  @media (max-width:720px){.wrap{padding:0 22px}}

  /* ============= NAV ============= */
  nav.top{
    position:fixed;top:0;left:0;right:0;z-index:50;
    background:#fff;border-bottom:1px solid rgba(26,26,26,.12);
    transition:background-color .35s, border-color .35s;
  }
  nav.top.scrolled{
    background:rgba(255,255,255,.7);
    backdrop-filter:blur(18px) saturate(160%);
    -webkit-backdrop-filter:blur(18px) saturate(160%);
  }
  nav.top .row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;gap:24px}
  .brand{display:flex;align-items:center;text-decoration:none;color:inherit}
  .brand img{height:46px;width:auto;display:block}
  nav ul{list-style:none;display:flex;gap:28px;margin:0;padding:0}
  nav ul a{
    color:#1a1a1a;text-decoration:none;font-size:13px;
    text-transform:uppercase;letter-spacing:.12em;
    padding:6px 0;transition:color .2s;
  }
  nav ul a:hover{color:var(--red)}
  nav .cta{
    background:var(--red);color:#fff;
    padding:13px 22px;text-decoration:none;
    font-family:var(--mono);font-size:12px;
    text-transform:uppercase;letter-spacing:.14em;font-weight:500;
    border:1px solid var(--red);
    display:inline-flex;align-items:center;gap:10px;
    transition:.2s;cursor:pointer;
  }
  nav .cta:hover{background:#fff;color:#0c0c0c;border-color:#1a1a1a}
  nav .cta::after{content:"→";transition:transform .15s}
  nav .cta:hover::after{transform:translateX(3px)}
  @media (max-width:880px){nav ul{display:none}}

  /* ============= Marquee ============= */
  .marquee{
    background:var(--ink);color:#f5f5f5;
    border-bottom:1px solid var(--ink);overflow:hidden;
    font-family:var(--mono);font-size:12px;
    text-transform:uppercase;letter-spacing:.18em;padding:9px 0;
  }
  .marquee-track{
    display:flex;gap:48px;white-space:nowrap;
    animation:scroll 38s linear infinite;width:max-content;
  }
  .marquee-track span{display:inline-flex;align-items:center;gap:48px}
  .marquee-track span::after{content:"✦";color:var(--red)}
  @keyframes scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

  /* ============= Studio header (editorial) ============= */
  .studio-hero{
    background:var(--paper);
    border-bottom:1px solid var(--ink);
    padding:80px 0 60px;position:relative;overflow:hidden;
  }
  .studio-hero::before{
    content:"";position:absolute;top:0;right:0;width:50%;height:100%;
    background:radial-gradient(600px 280px at 100% 0%, rgba(179,37,44,.10), transparent 70%);
    pointer-events:none;
  }
  .studio-hero .wrap{position:relative;z-index:1}
  .studio-eyebrow{
    font-family:var(--mono);font-size:11px;
    text-transform:uppercase;letter-spacing:.22em;color:var(--red);
    display:inline-flex;align-items:center;gap:14px;margin-bottom:22px;
  }
  .studio-eyebrow::before{content:"";width:48px;height:1px;background:var(--red)}
  .studio-title{
    font-family:var(--display);font-weight:800;
    font-size:clamp(40px, 6vw, 76px);line-height:.96;letter-spacing:-.025em;
    margin:0;color:var(--ink);text-wrap:balance;
  }
  .studio-title em{font-style:italic;color:var(--red);font-weight:700}
  .studio-lead{
    font-size:18px;color:var(--ash);
    margin:24px 0 0;max-width:62ch;text-wrap:pretty;
  }
  .studio-meta{
    display:flex;gap:32px;flex-wrap:wrap;margin-top:32px;
    padding-top:24px;border-top:1px solid rgba(26,26,26,.12);
    font-family:var(--mono);font-size:11px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.14em;
  }
  .studio-meta strong{color:var(--ink);font-weight:600;margin-right:6px;font-family:var(--display)}

  /* ============= Layout ============= */
  main.studio{padding:60px 0 120px;background:var(--paper-2)}
  .studio-grid{
    display:grid;grid-template-columns:1.5fr 1fr;gap:32px;align-items:start;
  }
  @media (max-width:980px){.studio-grid{grid-template-columns:1fr}}

  /* ============= Card / panel ============= */
  .panel{
    background:#fff;border:1px solid var(--ink);
    padding:0;position:relative;
  }
  .panel + .panel{margin-top:24px}

  /* ============= Step ============= */
  .step{padding:36px 36px 36px;border-bottom:1px solid rgba(26,26,26,.12)}
  .step:last-child{border-bottom:0}
  @media (max-width:720px){.step{padding:24px}}
  .step-head{
    display:flex;align-items:center;gap:14px;margin-bottom:20px;
  }
  .step-num{
    width:36px;height:36px;display:grid;place-items:center;
    background:var(--red);color:#fff;
    font-family:var(--display);font-weight:700;font-size:16px;
    flex-shrink:0;
  }
  .step-title{
    font-family:var(--display);font-weight:700;font-size:22px;
    color:var(--ink);letter-spacing:-.01em;margin:0;
  }
  .step-sub-label{
    font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.18em;
    color:var(--ash);margin:0 0 12px;font-weight:500;
  }

  /* ============= URL input ============= */
  .url-row{
    display:flex;align-items:stretch;
    border:1px solid var(--ink);background:#fff;
    transition:border-color .15s;
  }
  .url-row:focus-within{border-color:var(--red)}
  .url-prefix{
    display:flex;align-items:center;gap:10px;
    padding:14px 16px;background:var(--paper-2);
    border-right:1px solid var(--ink);
    font-family:var(--mono);font-size:13px;color:var(--graphite);
    user-select:none;
  }
  .url-prefix svg{width:14px;height:14px;opacity:.6}
  .url-input{
    flex:1;min-width:0;background:transparent;border:0;
    padding:14px 16px;font-family:var(--mono);font-size:14px;color:var(--ink);
    outline:none;
  }
  .url-input::placeholder{color:var(--ash-2)}
  .url-status{
    display:flex;align-items:center;padding-right:14px;
  }
  .url-status .ok{color:var(--ok)}
  .url-status .err{color:var(--red)}
  .url-hint{
    font-family:var(--mono);font-size:11px;color:var(--ash);
    margin:10px 0 0;text-transform:uppercase;letter-spacing:.10em;
  }
  .url-hint .final{color:var(--ink);text-transform:none;font-weight:500;letter-spacing:0}
  .url-error{
    font-family:var(--mono);font-size:11px;color:var(--red);
    margin:8px 0 0;text-transform:uppercase;letter-spacing:.12em;
  }

  /* ============= Warning / alerts ============= */
  .alert{
    display:flex;gap:12px;align-items:flex-start;
    border:1px solid var(--red);background:#fff7f7;
    padding:14px 16px;margin-top:18px;
    border-left-width:4px;
  }
  .alert svg{width:14px;height:14px;color:var(--red);flex-shrink:0;margin-top:3px}
  .alert .alert-title{
    font-family:var(--mono);font-size:10px;font-weight:600;
    text-transform:uppercase;letter-spacing:.16em;color:var(--red);
    margin:0 0 4px;
  }
  .alert p{
    font-size:13px;color:var(--ink);margin:0;line-height:1.5;
  }
  .alert.warn{border-color:var(--warn);background:#fff8ed}
  .alert.warn .alert-title,.alert.warn svg{color:var(--warn)}

  /* ============= Color picker ============= */
  .color-row{display:flex;align-items:center;gap:18px}
  .color-swatch{
    width:56px;height:56px;background:#000;
    border:1px solid var(--ink);position:relative;cursor:pointer;
    display:grid;place-items:center;
  }
  .color-swatch.is-selected::after{
    content:"";position:absolute;inset:-5px;border:1px solid var(--red);
  }
  .color-swatch svg{width:20px;height:20px;color:#fff;opacity:.9}
  .color-label{
    font-family:var(--display);font-weight:600;font-size:16px;color:var(--ink);
    margin:0 0 4px;
  }
  .color-sub{
    font-family:var(--mono);font-size:10px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.14em;margin:0;
  }

  /* ============= Tile pickers ============= */
  .tile-grid{display:grid;gap:10px}
  .tile-grid.cols-4{grid-template-columns:repeat(4, 1fr)}
  .tile-grid.cols-3{grid-template-columns:repeat(3, 1fr)}
  @media (max-width:520px){
    .tile-grid.cols-4{grid-template-columns:repeat(2, 1fr)}
  }
  .tile{
    background:#fff;border:1px solid rgba(26,26,26,.25);
    padding:14px 10px;cursor:pointer;
    display:flex;flex-direction:column;align-items:center;gap:8px;
    transition:.18s;position:relative;
  }
  .tile:hover{border-color:var(--ink);transform:translateY(-2px);box-shadow:4px 4px 0 var(--ink)}
  .tile.active{
    background:var(--ink);color:#fff;border-color:var(--ink);
    transform:none;box-shadow:4px 4px 0 var(--red);
  }
  .tile.active .tile-label{color:#fff}
  .tile .tile-icon{
    width:32px;height:32px;display:grid;place-items:center;color:inherit;
  }
  .tile .tile-icon svg{width:100%;height:100%}
  .tile .tile-label{
    font-family:var(--mono);font-size:10px;color:var(--ink);
    text-transform:uppercase;letter-spacing:.12em;
    text-align:center;line-height:1.2;
  }
  .tile-grid.cols-3 .tile{padding:18px 10px}
  .tile-grid.cols-3 .tile .tile-icon{width:40px;height:40px}

  /* ============= Preview panel ============= */
  .preview-sticky{position:sticky;top:96px}
  @media (max-width:980px){.preview-sticky{position:static;top:auto}}
  .preview-panel{
    background:var(--ink);color:#f1ebd9;
    border:1px solid var(--ink);padding:36px;position:relative;overflow:hidden;
  }
  .preview-panel::after{
    content:"";position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(500px 220px at 100% 0%, rgba(179,37,44,.4), transparent 60%);
  }
  .preview-panel > *{position:relative;z-index:1}
  .preview-head{
    display:flex;justify-content:space-between;align-items:baseline;
    margin-bottom:22px;flex-wrap:wrap;gap:8px;
  }
  .preview-head h2{
    font-family:var(--display);font-weight:700;font-size:22px;color:#fff;margin:0;letter-spacing:-.01em;
  }
  .preview-head .status{
    font-family:var(--mono);font-size:10px;color:rgba(241,235,217,.6);
    text-transform:uppercase;letter-spacing:.18em;
    display:inline-flex;align-items:center;gap:8px;
  }
  .preview-head .status::before{
    content:"";width:6px;height:6px;background:var(--ash);border-radius:50%;
  }
  .preview-head .status.live::before{background:var(--red);box-shadow:0 0 0 3px rgba(179,37,44,.25)}

  .preview-stage{
    position:relative;width:100%;aspect-ratio:1/1;
    background:
      linear-gradient(45deg, rgba(241,235,217,.04) 25%, transparent 25%, transparent 75%, rgba(241,235,217,.04) 75%),
      linear-gradient(45deg, rgba(241,235,217,.04) 25%, transparent 25%, transparent 75%, rgba(241,235,217,.04) 75%);
    background-size:24px 24px;
    background-position:0 0, 12px 12px;
    border:1px solid rgba(241,235,217,.18);
    overflow:hidden;
  }
  .qr-canvas{
    position:absolute;inset:18px;background:#fff;
    box-shadow:0 30px 60px -20px rgba(0,0,0,.6);
    overflow:hidden;display:flex;align-items:center;justify-content:center;
    padding:12px;
  }
  .qr-canvas svg,
  .qr-canvas canvas,
  .qr-canvas img{
    display:block;
    width:100% !important;height:100% !important;
    max-width:100%;max-height:100%;
    object-fit:contain;
  }
  .preview-empty{
    position:absolute;inset:0;display:flex;flex-direction:column;
    align-items:center;justify-content:center;gap:14px;text-align:center;padding:20px;
  }
  .preview-empty .ic{
    width:64px;height:64px;border:1px solid rgba(241,235,217,.3);
    display:grid;place-items:center;color:rgba(241,235,217,.5);
  }
  .preview-empty p{
    font-family:var(--mono);font-size:11px;color:rgba(241,235,217,.55);
    text-transform:uppercase;letter-spacing:.18em;margin:0;max-width:260px;
  }
  .preview-empty .hint{
    font-family:var(--display);font-weight:600;font-size:16px;color:#f1ebd9;
    text-transform:none;letter-spacing:-.01em;
  }

  /* ============= Action buttons ============= */
  .btn{
    display:inline-flex;align-items:center;justify-content:center;gap:10px;
    font-family:var(--mono);font-size:11px;font-weight:500;
    text-transform:uppercase;letter-spacing:.14em;
    padding:14px 20px;cursor:pointer;border:1px solid;
    text-decoration:none;width:100%;
    transition:.2s;
  }
  .btn svg{width:14px;height:14px}
  .btn:disabled{opacity:.35;cursor:not-allowed}
  .btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
  .btn-primary:hover:not(:disabled){background:#fff;color:var(--ink);border-color:#fff}
  .btn-ghost{background:transparent;color:#f1ebd9;border-color:rgba(241,235,217,.4)}
  .btn-ghost:hover:not(:disabled){background:#f1ebd9;color:var(--ink);border-color:#f1ebd9}
  .btn-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px}
  .btn-sm{padding:10px 14px;font-size:10px}

  .actions{margin-top:24px;display:flex;flex-direction:column;gap:10px}

  /* Email collapse */
  .email-collapse{
    background:rgba(241,235,217,.04);border:1px solid rgba(241,235,217,.18);
    padding:18px;margin-top:10px;
  }
  .email-label{
    font-family:var(--mono);font-size:10px;color:rgba(241,235,217,.65);
    text-transform:uppercase;letter-spacing:.16em;display:block;margin-bottom:8px;
  }
  .email-input{
    width:100%;background:transparent;border:0;border-bottom:1px solid rgba(241,235,217,.35);
    color:#f1ebd9;padding:10px 0;font-family:var(--mono);font-size:13px;outline:none;
    margin-bottom:14px;
  }
  .email-input:focus{border-bottom-color:var(--red)}
  .email-input::placeholder{color:rgba(241,235,217,.4)}
  .toggle-arrow-wrap{margin-left:auto;display:inline-flex;align-items:center;transition:transform .2s}
  .toggle-arrow-wrap.open{transform:rotate(180deg)}
  .toggle-arrow{width:12px;height:12px}
  .msg{
    font-family:var(--mono);font-size:10px;text-transform:uppercase;letter-spacing:.14em;
    text-align:center;margin:6px 0 0;padding:8px;
  }
  .msg.success{color:var(--ok);background:rgba(26,122,62,.08);border:1px solid rgba(26,122,62,.4)}
  .msg.error{color:var(--red);background:rgba(179,37,44,.08);border:1px solid rgba(179,37,44,.4)}

  /* ============= History ============= */
  .history-section{margin-top:80px;padding-top:48px}
  .history-head{
    display:flex;align-items:center;gap:18px;margin-bottom:36px;
  }
  .history-head .line{flex:1;height:1px;background:var(--ink)}
  .history-head .label{
    font-family:var(--mono);font-size:11px;color:var(--ink);
    text-transform:uppercase;letter-spacing:.22em;
    display:inline-flex;align-items:center;gap:12px;
  }
  .history-head .count{
    font-family:var(--display);font-weight:700;font-size:14px;
    background:var(--red);color:#fff;
    padding:2px 10px;letter-spacing:0;
  }
  .history-grid{
    display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:18px;
  }
  .history-card{
    background:#fff;border:1px solid var(--ink);padding:18px;cursor:pointer;
    transition:.2s;display:flex;flex-direction:column;gap:12px;
  }
  .history-card:hover{transform:translateY(-3px);box-shadow:6px 6px 0 var(--ink)}
  .history-qr{
    aspect-ratio:1/1;background:var(--paper-2);border:1px solid var(--ink);
    display:grid;place-items:center;overflow:hidden;
  }
  .history-qr img{width:100%;height:100%;object-fit:contain;padding:8px}
  .history-meta .url{
    font-family:var(--mono);font-size:11px;color:var(--ink);
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    display:flex;align-items:center;gap:6px;
  }
  .history-meta .url svg{width:10px;height:10px;opacity:.6;flex-shrink:0}
  .history-meta .date{
    font-family:var(--mono);font-size:10px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.14em;margin-top:4px;
  }

  /* ============= Footer ============= */
  footer{
    background:var(--ink);color:#f1ebd9;
    padding:80px 0 32px;margin-top:0;
  }
  footer .foot-top{
    display:flex;justify-content:space-between;align-items:flex-start;gap:60px;
    flex-wrap:wrap;padding-bottom:48px;border-bottom:1px solid rgba(241,235,217,.18);
  }
  footer .foot-logo{height:80px;width:auto}
  footer .foot-cols{display:flex;gap:60px;flex-wrap:wrap}
  footer .foot-col h5{
    font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.18em;
    color:rgba(241,235,217,.5);margin:0 0 16px;
  }
  footer .foot-col ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px}
  footer .foot-col a{color:#f1ebd9;text-decoration:none;font-size:14px;transition:color .2s}
  footer .foot-col a:hover{color:var(--red)}
  footer .foot-bottom{
    display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px;padding-top:24px;
    font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;
    color:rgba(241,235,217,.5);
  }

  [x-cloak]{display:none !important}
  .fade-enter{animation:fadeIn .3s ease}
  @keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<!-- NAV (matches landing) -->
<nav class="top" id="topNav">
  <div class="wrap row">
    <a class="brand" href="{{ route('home') }}" aria-label="Dynamic Tattoos">
      <img src="{{ asset('images/designer/logo-nav-black.png') }}" alt="Dynamic Tattoos" />
    </a>
    <ul>
      <li><a href="{{ route('home') }}#concepto">Concepto</a></li>
      <li><a href="{{ route('home') }}#demo">Demo</a></li>
      <li><a href="{{ route('home') }}#tecnico">Técnico</a></li>
      <li><a href="{{ route('home') }}#planes">Planes</a></li>
      <li><a href="{{ route('profile.index') }}">Mi cuenta</a></li>
    </ul>
    <a class="cta" href="#preview-panel">Crear QR</a>
  </div>
</nav>

<!-- Marquee -->
<div class="marquee">
  <div class="marquee-track">
    <span>QR Studio</span>
    <span>Diseña · personaliza · descarga</span>
    <span>Tu piel · tu canal</span>
    <span>Edición ilimitada del destino</span>
    <span>4 × 4 cm validado</span>
    <span>QR Studio</span>
    <span>Diseña · personaliza · descarga</span>
    <span>Tu piel · tu canal</span>
    <span>Edición ilimitada del destino</span>
    <span>4 × 4 cm validado</span>
  </div>
</div>

<!-- ============= STUDIO HERO ============= -->
<section class="studio-hero">
  <div class="wrap">
    <p class="studio-eyebrow">Studio · Generador</p>
    <h1 class="studio-title">Crea tu código <em>QR</em> personalizado.</h1>
    <p class="studio-lead">Diseña un QR único para tu URL. Personaliza la forma del cuerpo, el marco y el ojo central. Descarga en PNG o SVG, envíalo por email o guárdalo para editar el destino más tarde.</p>
    <div class="studio-meta">
      <span><strong>01</strong> Define un enlace</span>
      <span><strong>02</strong> Elige un color</span>
      <span><strong>03</strong> Personaliza el diseño</span>
      <span><strong>04</strong> Descarga o guarda</span>
    </div>
  </div>
</section>

<!-- ============= MAIN ============= -->
<main class="studio" x-data='qrBuilder({ baseUrl: @json($baseUrl), initialSlug: @json($initialSlug), history: @json($history), storeUrl: @json(route("qr.store")), emailUrl: @json(route("qr.email")), csrf: @json(csrf_token()) })' x-init="init()">
  <div class="wrap">
    <div class="studio-grid">

      <!-- LEFT — Steps -->
      <div class="panel">

        <!-- Step 1 — URL -->
        <div class="step">
          <div class="step-head">
            <span class="step-num">1</span>
            <h2 class="step-title">Define tu enlace</h2>
          </div>
          <p class="step-sub-label">Destino del QR</p>

          <div class="url-row">
            <span class="url-prefix">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
              d-tattoo.com/
            </span>
            <input id="slug" type="text" class="url-input"
                   autocomplete="off" spellcheck="false" placeholder="mi-tatuaje"
                   x-model.debounce.300ms="form.slug"
                   @input="form.slug = $event.target.value.replace(/\s+/g,'-').replace(/[^A-Za-z0-9._~\-\/]/g,'')"/>
            <span class="url-status" x-show="form.slug" x-cloak>
              <svg x-show="urlValid" class="ok" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:16px;height:16px"><polyline points="20 6 9 17 4 12"></polyline></svg>
              <svg x-show="!urlValid" class="err" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:16px;height:16px"><path d="M6 18L18 6M6 6l12 12"></path></svg>
            </span>
          </div>

          <p class="url-hint">
            URL final · <span class="final mono" x-text="form.url || 'https://d-tattoo.com/…'">https://d-tattoo.com/…</span>
          </p>
          <p x-show="errors.slug || errors.url" x-text="errors.slug || errors.url" class="url-error" x-cloak></p>

          <!-- Warn: too many chars -->
          <div class="alert warn" x-show="form.slug.length > 8" x-cloak>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"></circle><path stroke-linecap="round" d="M12 8v4m0 3.5h.01"></path></svg>
            <div>
              <p class="alert-title">Atención · Legibilidad</p>
              <p>Has superado los <strong>8 caracteres</strong> recomendados (<span class="mono" x-text="form.slug.length"></span>). Solo garantizamos la correcta legibilidad del QR dentro de ese límite.</p>
            </div>
          </div>

          <!-- Warn: URL is final -->
          <div class="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 3.5h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
            <div>
              <p class="alert-title">Importante</p>
              <p>Una vez generado el QR de forma definitiva, <strong>la URL no podrá modificarse</strong>. Asegúrate de que el destino es correcto antes de guardar.</p>
            </div>
          </div>
        </div>

        <!-- Step 2 — Color -->
        <div class="step">
          <div class="step-head">
            <span class="step-num">2</span>
            <h2 class="step-title">Elige el color</h2>
          </div>
          <div class="color-row">
            <button type="button" class="color-swatch is-selected" title="Negro sólido">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <div>
              <p class="color-label">Negro sólido</p>
              <p class="color-sub">Más colores y gradientes próximamente</p>
            </div>
          </div>
        </div>

        <!-- Step 3 — Personalización -->
        <div class="step">
          <div class="step-head">
            <span class="step-num">3</span>
            <h2 class="step-title">Personaliza el diseño</h2>
          </div>

          <div style="display:flex;flex-direction:column;gap:28px">
            <div>
              <p class="step-sub-label">Forma del cuerpo</p>
              <div class="tile-grid cols-4">
                <template x-for="opt in dotsTypes" :key="opt.value">
                  <button type="button" class="tile"
                          :class="{ active: form.dots_type === opt.value }"
                          @click="form.dots_type = opt.value"
                          :title="opt.label">
                    <span class="tile-icon" x-html="opt.icon"></span>
                    <span class="tile-label" x-text="opt.label"></span>
                  </button>
                </template>
              </div>
            </div>

            <div>
              <p class="step-sub-label">Marco del ojo</p>
              <div class="tile-grid cols-3" style="max-width:440px">
                <template x-for="opt in cornersSquareTypes" :key="opt.value">
                  <button type="button" class="tile"
                          :class="{ active: form.corners_square_type === opt.value }"
                          @click="form.corners_square_type = opt.value"
                          :title="opt.label">
                    <span class="tile-icon" x-html="opt.icon"></span>
                    <span class="tile-label" x-text="opt.label"></span>
                  </button>
                </template>
              </div>
            </div>

            <div>
              <p class="step-sub-label">Ojo (centro)</p>
              <div class="tile-grid cols-3" style="max-width:300px">
                <template x-for="opt in cornersDotTypes" :key="opt.value">
                  <button type="button" class="tile"
                          :class="{ active: form.corners_dot_type === opt.value }"
                          @click="form.corners_dot_type = opt.value"
                          :title="opt.label">
                    <span class="tile-icon" x-html="opt.icon"></span>
                    <span class="tile-label" x-text="opt.label"></span>
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT — Preview -->
      <div>
        <div class="preview-sticky">
          <div class="preview-panel" id="preview-panel">
            <div class="preview-head">
              <h2>Vista previa</h2>
              <span class="status" :class="form.url ? 'live' : ''" x-text="form.url ? 'En vivo' : 'En espera'">En espera</span>
            </div>

            <div class="preview-stage">
              <div x-ref="qr" class="qr-canvas" x-show="form.url" x-transition.opacity x-cloak></div>
              <div x-show="!form.url" class="preview-empty">
                <div class="ic">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width:28px;height:28px">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    <path d="M14 14h3v3h-3zM20 14v7M14 20h7"></path>
                  </svg>
                </div>
                <p class="hint">Sin contenido todavía</p>
                <p>Introduce una URL para previsualizar tu QR</p>
              </div>
            </div>

            <div class="actions">
              <button type="button" class="btn btn-primary" :disabled="!form.url" @click="download('png')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"></path></svg>
                Descargar PNG
              </button>

              <!-- Send to email -->
              <div>
                <button type="button" class="btn btn-ghost" :disabled="!form.url" @click="emailOpen = !emailOpen">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  Enviar por email
                  <span class="toggle-arrow-wrap" :class="{ open: emailOpen }"><svg class="toggle-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg></span>
                </button>
                <div x-show="emailOpen" x-transition class="email-collapse" x-cloak>
                  <label class="email-label">Dirección de correo</label>
                  <input type="email" class="email-input" x-model="emailAddress" placeholder="ejemplo@correo.com"/>
                  <button type="button" class="btn btn-primary btn-sm"
                          :disabled="!emailAddress || sendingEmail" @click="sendEmail()">
                    <svg x-show="!sendingEmail" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
                    <span x-text="sendingEmail ? 'Enviando…' : 'Enviar QR'">Enviar QR</span>
                  </button>
                  <p x-show="emailMessage" x-text="emailMessage" :class="'msg ' + (emailMessageType === 'error' ? 'error' : 'success')" x-cloak></p>
                </div>
              </div>

              <div class="btn-row">
                <button type="button" class="btn btn-ghost btn-sm" :disabled="!form.url" @click="download('svg')">
                  Descargar SVG
                </button>
                <button type="button" class="btn btn-ghost btn-sm" :disabled="!form.url || saving" @click="save()">
                  <span x-show="!saving">Guardar QR</span>
                  <span x-show="saving" x-cloak>Guardando…</span>
                </button>
              </div>

              <p x-show="message" x-text="message" :class="'msg ' + (messageType === 'error' ? 'error' : 'success')" x-cloak></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- History -->
    <section class="history-section" x-show="history.length" x-cloak>
      <div class="history-head">
        <div class="line"></div>
        <span class="label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Historial guardado
          <span class="count" x-text="history.length"></span>
        </span>
        <div class="line"></div>
      </div>
      <div class="history-grid">
        <template x-for="item in history" :key="item.id">
          <div class="history-card" @click="loadFromHistory(item)">
            <div class="history-qr">
              <img :src="item.image" :alt="item.url"/>
            </div>
            <div class="history-meta">
              <p class="url">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1"/><path d="M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/></svg>
                <span x-text="item.url"></span>
              </p>
              <p class="date" x-text="formatDate(item.created_at)"></p>
            </div>
          </div>
        </template>
      </div>
    </section>
  </div>
</main>

<!-- ============= Footer ============= -->
<footer>
  <div class="wrap">
    <div class="foot-top">
      <div>
        <img class="foot-logo" src="{{ asset('images/designer/logo-hero-qr.png') }}" alt="Dynamic Tattoos"/>
      </div>
      <div class="foot-cols">
        <div class="foot-col">
          <h5>Producto</h5>
          <ul>
            <li><a href="{{ route('home') }}#demo">Demo en vivo</a></li>
            <li><a href="{{ route('home') }}#concepto">Cómo funciona</a></li>
            <li><a href="{{ route('home') }}#tecnico">Técnico</a></li>
            <li><a href="{{ route('home') }}#planes">Planes</a></li>
          </ul>
        </div>
        <div class="foot-col">
          <h5>Soporte</h5>
          <ul>
            <li><a href="{{ route('home') }}#faq">FAQ</a></li>
            <li><a href="#">Estudios partner</a></li>
            <li><a href="#">Guía técnica</a></li>
            <li><a href="#">Contacto</a></li>
          </ul>
        </div>
        <div class="foot-col">
          <h5>Cuenta</h5>
          <ul>
            <li><a href="{{ route('profile.index') }}">Mi cuenta</a></li>
            <li><a href="QR Studio.html">QR Studio</a></li>
            <li><a href="#">Cerrar sesión</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="foot-bottom">
      <div>© 2026 Dynamic Tattoos · Madrid</div>
      <div>Tatuajes que evolucionan</div>
    </div>
  </div>
</footer>

<script>
  // Nav scrolled state
  (function(){
    const nav = document.getElementById('topNav');
    const onScroll = () => {
      if (window.scrollY > 12) nav.classList.add('scrolled');
      else nav.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive:true });
    onScroll();
  })();

  // QR shape icons + labels
  const SHAPES = {
    dots: {
      'square':         `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>`,
      'dots':           `<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="6" cy="6" r="3.2"/><circle cx="18" cy="6" r="3.2"/><circle cx="6" cy="18" r="3.2"/><circle cx="18" cy="18" r="3.2"/></svg>`,
      'rounded':        `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>`,
      'extra-rounded':  `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="3.5"/><rect x="14" y="3" width="7" height="7" rx="3.5"/><rect x="3" y="14" width="7" height="7" rx="3.5"/><rect x="14" y="14" width="7" height="7" rx="3.5"/></svg>`,
    },
    cornersSquare: {
      'square':         `<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="3" width="26" height="26"/></svg>`,
      'dot':            `<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3"><circle cx="16" cy="16" r="13"/></svg>`,
      'extra-rounded':  `<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="3" width="26" height="26" rx="8"/></svg>`,
    },
    cornersDot: {
      'square':         `<svg viewBox="0 0 24 24" fill="currentColor"><rect x="5" y="5" width="14" height="14"/></svg>`,
      'dot':            `<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="7"/></svg>`,
    },
  };
  const LABELS = {
    'square':'Cuadrado','dots':'Puntos','rounded':'Redondeado','extra-rounded':'Muy redondeado',
    'classy':'Elegante','classy-rounded':'Elegante redondeado','dot':'Punto',
  };
  function buildOptions(values, iconSet){
    return values.map(v => ({ value:v, label:LABELS[v] || v, icon:iconSet[v] || '' }));
  }

  function qrBuilder(config){
    return {
      baseUrl: config.baseUrl,
      dotsTypes:         buildOptions(['dots','rounded','extra-rounded','square'], SHAPES.dots),
      cornersSquareTypes:buildOptions(['dot','extra-rounded','square'],           SHAPES.cornersSquare),
      cornersDotTypes:   buildOptions(['dot','square'],                            SHAPES.cornersDot),
      form: {
        slug: config.initialSlug || '',
        url:  config.initialSlug ? `${config.baseUrl}/${config.initialSlug}` : '',
        color: '#1a1a1a',
        dots_type: 'rounded',
        corners_square_type: 'extra-rounded',
        corners_dot_type: 'dot',
      },
      errors:{}, saving:false, message:'', messageType:'',
      emailOpen:false, emailAddress:'', sendingEmail:false,
      emailMessage:'', emailMessageType:'',
      urlValid:false, history: config.history || [], qr:null,
      storeUrl: config.storeUrl, emailUrl: config.emailUrl, csrf: config.csrf,

      init(){
        // Wait for QR library to be available
        const start = () => {
          if (!window.QRCodeStyling) { setTimeout(start, 50); return; }
          this.qr = new window.QRCodeStyling({
            width:600, height:600, type:'svg', data:'https://example.com',
            margin:0, qrOptions:{ errorCorrectionLevel:'Q' },
            dotsOptions:{ color:this.form.color, type:this.form.dots_type },
            cornersSquareOptions:{ color:this.form.color, type:this.form.corners_square_type },
            cornersDotOptions:{ color:this.form.color, type:this.form.corners_dot_type },
            backgroundOptions:{ color:'#ffffff' },
          });
          this.qr.append(this.$refs.qr);

          this.$watch('form', () => this.update(), { deep:true });
          this.$watch('form.slug', (val) => {
            const clean = (val || '').replace(/^\/+/, '');
            this.form.url = clean ? `${this.baseUrl}/${clean}` : '';
            this.urlValid = clean.length > 0 && /^[A-Za-z0-9._~\-\/]+$/.test(clean);
          });
          this.update();
        };
        start();
      },
      update(){
        if (!this.qr) return;
        this.qr.update({
          data: this.form.url || ' ',
          dotsOptions:         { color:this.form.color, type:this.form.dots_type },
          cornersSquareOptions:{ color:this.form.color, type:this.form.corners_square_type },
          cornersDotOptions:   { color:this.form.color, type:this.form.corners_dot_type },
        });
        this.$nextTick(() => this.fitSvg());
      },
      fitSvg(){
        const root = this.$refs.qr;
        if (!root) return;
        const svg = root.querySelector('svg');
        if (!svg) return;
        const w = parseFloat(svg.getAttribute('width')) || 600;
        const h = parseFloat(svg.getAttribute('height')) || 600;
        if (!svg.getAttribute('viewBox')) {
          svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
        }
        svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        svg.removeAttribute('width');
        svg.removeAttribute('height');
      },
      download(ext){
        if (!this.form.url) return;
        this.qr.download({ name:'qr-code', extension:ext });
      },
      faviconUrl(url){
        try { const host = new URL(url).hostname; return `https://www.google.com/s2/favicons?domain=${encodeURIComponent(host)}&sz=64`; }
        catch(e){ return ''; }
      },
      loadFromHistory(item){
        const prefix = this.baseUrl + '/';
        this.form.slug = item.url && item.url.startsWith(prefix) ? item.url.slice(prefix.length) : item.url;
        this.form.url = item.url;
        this.form.color = item.color;
        this.form.dots_type = item.dots_type;
        this.form.corners_square_type = item.corners_square_type;
        this.form.corners_dot_type = item.corners_dot_type;
        window.scrollTo({ top:0, behavior:'smooth' });
      },
      formatDate(dateStr){
        return new Date(dateStr).toLocaleDateString('es-ES',{ day:'2-digit', month:'short', year:'numeric' });
      },
      async sendEmail(){
        if (!this.emailAddress || !this.form.url) return;
        this.sendingEmail = true;
        this.emailMessage = '';
        try {
          const blob = await this.qr.getRawData('png');
          const base64 = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
          });
          const res = await fetch(this.emailUrl, {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ email:this.emailAddress, qr_image:base64, url:this.form.url }),
          });
          const data = await res.json();
          if (!res.ok) {
            this.emailMessage = data.message || 'Error al enviar';
            this.emailMessageType = 'error';
          } else {
            this.emailMessage = data.message || `QR enviado a ${this.emailAddress}`;
            this.emailMessageType = 'success';
            this.emailAddress = '';
          }
        } catch(e){
          this.emailMessage = 'Error de red';
          this.emailMessageType = 'error';
        } finally {
          this.sendingEmail = false;
        }
      },
      async save(){
        if (!this.form.url) return;
        this.saving = true;
        this.message = '';
        this.errors = {};
        try {
          const res = await fetch(this.storeUrl, {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({
              slug: this.form.slug,
              color: this.form.color,
              dots_type: this.form.dots_type,
              corners_square_type: this.form.corners_square_type,
              corners_dot_type: this.form.corners_dot_type,
            }),
          });
          const data = await res.json();
          if (!res.ok) {
            if (data.errors) {
              Object.entries(data.errors).forEach(([k, v]) => this.errors[k] = v[0]);
            }
            this.message = data.message || 'Error al guardar';
            this.messageType = 'error';
          } else {
            this.message = data.message || 'QR guardado correctamente.';
            this.messageType = 'success';
            if (data.qr) {
              this.history.unshift(data.qr);
              if (this.history.length > 20) this.history.pop();
            }
          }
        } catch(e){
          this.message = 'No se pudo guardar el QR.';
          this.messageType = 'error';
        } finally {
          this.saving = false;
        }
      },
    };
  }
  // expose to Alpine
  window.qrBuilder = qrBuilder;
</script>
</body>
</html>
