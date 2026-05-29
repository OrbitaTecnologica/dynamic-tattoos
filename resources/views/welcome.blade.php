<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<title>Dynamic Tattoos — Tatuajes que evolucionan</title>
<link rel="icon" type="image/png" href="{{ asset('images/designer/dynamic-tattoos-icon.png') }}" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">

<style>
  :root{
    /* Brand palette */
    --paper:#ffffff;          /* white background */
    --paper-2:#f5f5f5;        /* subtle off-white */
    --bone:#ffffff;           /* alias */
    --bone-2:#f5f5f5;
    --ink:#1a1a1a;            /* Negro Tinta */
    --ink-2:#2a2a2a;
    --graphite:#4a4a4a;       /* Gris Grafito */
    --ash:#6b6b6b;
    --ash-2:#9a9a9a;
    --light-gray:#c8c8c8;     /* Gris Claro */
    --red:#b3252c;            /* Rojo Pasión */
    --red-deep:#8a1c22;
    --rule:#1a1a1a;
    /* Type */
    --display:'Sora', system-ui, sans-serif;
    --sans:'Manrope', system-ui, sans-serif;
    --serif:'Manrope', system-ui, sans-serif; /* kept name for back-compat */
    --mono:'JetBrains Mono', ui-monospace, monospace;
  }
  *{box-sizing:border-box}
  html,body{margin:0;padding:0}
  body{
    --page-bg:#ffffff;
    --page-fg:#1a1a1a;
    background:var(--page-bg);
    color:var(--page-fg);
    font-family:var(--sans);
    font-size:16px;
    line-height:1.5;
    -webkit-font-smoothing:antialiased;
    overflow-x:hidden;
    transition:background-color .9s cubic-bezier(.4,0,.2,1), color .9s cubic-bezier(.4,0,.2,1);
  }
  /* Scroll-driven themes — each section flips the page palette */
  section, header.hero{position:relative;z-index:1}
  [data-theme="paper"]{--theme-bg:#ffffff;--theme-fg:#1a1a1a;--theme-mute:#6b6b6b;--theme-rule:#1a1a1a;--theme-card:#f5f5f5}
  [data-theme="bone"]{--theme-bg:#d9d9d9;--theme-fg:#1a1a1a;--theme-mute:#4a4a4a;--theme-rule:#1a1a1a;--theme-card:#ffffff}
  [data-theme="ink"]{--theme-bg:#0c0c0c;--theme-fg:#f1ebd9;--theme-mute:#9a9489;--theme-rule:#f1ebd9;--theme-card:#161616}
  [data-theme="char"]{--theme-bg:#1a1a1a;--theme-fg:#f1ebd9;--theme-mute:#9a9489;--theme-rule:#f1ebd9;--theme-card:#0c0c0c}
  [data-theme="blood"]{--theme-bg:#3a0d10;--theme-fg:#f7e9d8;--theme-mute:#d7b9a2;--theme-rule:#f7e9d8;--theme-card:#220609}
  /* Grain overlay */
  body::before{
    content:"";
    position:fixed;inset:0;
    pointer-events:none;
    z-index:1000;
    opacity:.35;
    mix-blend-mode:multiply;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='240'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 .35 0'/></filter><rect width='100%25' height='100%25' filter='url(%23n)'/></svg>");
  }

  /* Typography */
  .serif{font-family:var(--display);font-weight:800;letter-spacing:-.02em}
  .display{font-family:var(--display);font-weight:800;letter-spacing:-.02em}
  .mono{font-family:var(--mono);font-weight:400}
  .italic{font-style:italic}
  .upper{text-transform:uppercase;letter-spacing:.14em}
  .tiny{font-size:11px}
  .ruby{color:var(--red)}

  /* Layout */
  .wrap{max-width:1360px;margin:0 auto;padding:0 40px;position:relative}
  @media (max-width:720px){.wrap{padding:0 22px}}

  hr.rule{border:0;border-top:1px solid var(--rule);margin:0}
  hr.thin{border:0;border-top:1px solid rgba(12,11,10,.18);margin:0}

  /* NAV — solid white initially, glass when scrolled / over dark sections */
  nav.top{
    position:fixed;top:0;left:0;right:0;z-index:50;
    background:#ffffff;
    border-bottom:1px solid rgba(26,26,26,.12);
    transition:background-color .35s, border-color .35s, color .35s, backdrop-filter .35s;
  }
  /* Scrolled = translucent glass over light section */
  nav.top.scrolled{
    background:rgba(255,255,255,.6);
    backdrop-filter:blur(18px) saturate(160%);
    -webkit-backdrop-filter:blur(18px) saturate(160%);
  }
  /* When over hero / dark sections, switch nav to dark glass */
  nav.top.on-dark{
    background:rgba(12,12,12,.35);
    backdrop-filter:blur(18px) saturate(160%);
    -webkit-backdrop-filter:blur(18px) saturate(160%);
    border-bottom:1px solid rgba(247,241,227,.12);
    color:#f7f1e3;
  }
  nav.top.on-dark ul a{color:#f7f1e3}
  nav.top.on-dark ul a:hover{color:var(--red)}
  nav.top.on-dark .cta{background:var(--red);color:#fff;border-color:var(--red)}
  nav.top.on-dark .cta:hover{background:#fff;color:#0c0c0c;border-color:#fff}

  /* Spacer so content below sticky nav isn't covered */
  body{padding-top:72px}
  nav.top .row{
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 0;gap:24px;
  }
  /* Logo swap: light version on white nav, dark/white version on dark glass nav */
  .brand{display:flex;align-items:center;gap:0;text-decoration:none;color:inherit;position:relative}
  .brand .brand-logo{
    height:46px;width:auto;display:block;
    transition:opacity .35s ease;
  }
  .brand .brand-dark{position:absolute;left:0;top:0;opacity:0;pointer-events:none}
  nav.top.on-dark .brand .brand-light{opacity:0}
  nav.top.on-dark .brand .brand-dark{opacity:1}
  .brand .name{
    font-family:var(--display);font-size:18px;line-height:1;font-weight:800;
    text-transform:uppercase;letter-spacing:.04em;
    display:inline-flex;align-items:baseline;gap:0;
  }
  .brand .name .dyn{color:var(--ink)}
  .brand .name .dot{display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--red);margin:0 2px;transform:translateY(-2px)}
  .brand .name .tat{color:var(--red);margin-left:6px;letter-spacing:.08em}
  nav ul{list-style:none;display:flex;gap:28px;margin:0;padding:0}
  nav ul a{
    color:#1a1a1a;text-decoration:none;font-size:13px;
    text-transform:uppercase;letter-spacing:.12em;
    position:relative;padding:6px 0;
    transition:color .2s;
  }
  nav ul a:hover{color:var(--red)}
  /* Reservar QR — botón sólido rojo, hover blanco/negro (matches hero buttons) */
  nav .cta{
    background:var(--red);
    color:#fff !important;
    padding:13px 22px;text-decoration:none;
    font-family:var(--mono);font-size:12px;
    text-transform:uppercase;letter-spacing:.14em;font-weight:500;
    border:1px solid var(--red);
    display:inline-flex;align-items:center;gap:10px;
    transition:background .2s ease, color .2s ease, border-color .2s ease;
    cursor:pointer;
  }
  nav .cta:hover{
    background:#ffffff;
    color:#0c0c0c !important;
    border-color:#ffffff;
  }
  nav .cta::after{content:"→";font-weight:400;display:inline-block;transition:transform .15s}
  nav .cta:hover::after{transform:translateX(3px)}
  @media (max-width:880px){nav ul{display:none}}

  /* Marquee */
  .marquee{
    background:var(--ink);color:var(--bone);
    border-bottom:1px solid var(--ink);
    overflow:hidden;
    font-family:var(--mono);font-size:12px;
    text-transform:uppercase;letter-spacing:.18em;
    padding:9px 0;
  }
  .marquee-track{
    display:flex;gap:48px;white-space:nowrap;
    animation:scroll 38s linear infinite;
    width:max-content;
  }
  .marquee-track span{display:inline-flex;align-items:center;gap:48px}
  .marquee-track span::after{content:"✦";color:var(--red)}
  @keyframes scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

  /* HERO */
  .hero{
    position:relative;
    padding:100px 0 110px;
    color:#f7f1e3;
    overflow:hidden;
    isolation:isolate;
  }
  .hero-bg{
    position:absolute;inset:0;z-index:-2;
    background:
      radial-gradient(80% 60% at 30% 30%, #2a0c0f 0%, #0c0c0c 60%),
      linear-gradient(180deg,#0c0c0c 0%, #161210 100%);
    overflow:hidden;
  }
  .hero-bg .hero-video{
    position:absolute;inset:-2%;
    width:104%;height:104%;
    object-fit:cover;
    filter:saturate(.85) contrast(1.05);
    opacity:0;transition:opacity 1s ease;
    z-index:2;
  }
  .hero-bg .hero-video.ready{opacity:1}
  .hero-bg .hero-video-placeholder{z-index:1}
  /* When video is ready, hide the placeholder */
  .hero-bg:has(.hero-video.ready) .hero-video-placeholder{opacity:0}
  .hero-bg .hero-video-placeholder{transition:opacity .6s ease}
  .hero-bg .hero-video-placeholder{
    position:absolute;inset:0;
    background:
      repeating-linear-gradient(115deg, rgba(255,255,255,.02) 0 14px, transparent 14px 28px),
      radial-gradient(50% 70% at 20% 80%, rgba(179,37,44,.45) 0%, transparent 60%),
      radial-gradient(60% 80% at 80% 20%, rgba(179,37,44,.18) 0%, transparent 60%),
      linear-gradient(180deg,#100806 0%, #1a0c0a 50%, #0a0707 100%);
    animation:heroDrift 22s ease-in-out infinite alternate;
  }
  @keyframes heroDrift{
    0%{transform:scale(1.04) translate(0,0)}
    100%{transform:scale(1.12) translate(-2%,1%)}
  }
  .hero-overlay{
    position:absolute;inset:0;z-index:-1;
    background:var(--hero-overlay-color, #0a0707);
    opacity:var(--hero-overlay, .62);
    transition:opacity .3s, background-color .3s;
    pointer-events:none;
  }
  .hero-overlay::after{
    content:"";position:absolute;inset:0;
    background:
      linear-gradient(180deg, rgba(10,7,7,.55) 0%, transparent 30%, transparent 70%, rgba(10,7,7,.85) 100%),
      linear-gradient(90deg, rgba(10,7,7,.45) 0%, transparent 40%);
    pointer-events:none;
  }
  /* Upload-video button — discrete top-right of hero */
  .hero-upload-btn{
    position:absolute;top:84px;right:32px;z-index:3;
    display:inline-flex;align-items:center;gap:8px;
    padding:9px 14px;
    background:rgba(247,241,227,.08);
    border:1px solid rgba(247,241,227,.25);
    color:#f7f1e3;
    font-family:var(--mono);font-size:10px;letter-spacing:.18em;text-transform:uppercase;
    cursor:pointer;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);
    transition:all .2s;
  }
  .hero-upload-btn:hover{background:var(--red);border-color:var(--red);color:#fff}
  .hero-upload-btn svg{flex-shrink:0}
  @media (max-width:720px){.hero-upload-btn{top:78px;right:18px;padding:8px 12px;font-size:9px}}
  .hero-grid{
    display:grid;grid-template-columns: 1.2fr .8fr;gap:60px;
    align-items:center;
  }
  @media (max-width:980px){.hero-grid{grid-template-columns:1fr;gap:36px}}

  .eyebrow{
    font-family:var(--mono);font-size:12px;
    text-transform:uppercase;letter-spacing:.22em;
    display:flex;align-items:center;gap:14px;
    color:rgba(247,241,227,.7);
  }
  .eyebrow::before{
    content:"";width:36px;height:1px;background:var(--red);
  }

  h1.head{
    font-family:var(--display);
    font-size:clamp(48px, 7.6vw, 108px);
    line-height:.98;
    letter-spacing:-.03em;
    margin:22px 0 0;
    font-weight:800;
    text-wrap:balance;
    color:#f7f1e3;
  }
  h1.head .ev{color:var(--red);position:relative;display:inline-block}
  h1.head .ev .pdot{
    display:inline-block;width:.18em;height:.18em;border-radius:50%;
    background:var(--red);margin-left:.05em;transform:translateY(-.05em);
  }
  h1.head em{font-style:italic;color:#f7f1e3;font-weight:300}

  .hero-lede{
    margin-top:30px;
    font-size:19px;line-height:1.5;max-width:560px;
    color:rgba(247,241,227,.78);
  }
  .hero-lede strong{font-weight:600;color:#f7f1e3}

  .hero-ctas{
    margin-top:36px;display:flex;gap:14px;flex-wrap:wrap;align-items:center;
  }
  .btn{
    display:inline-flex;align-items:center;gap:10px;
    padding:16px 26px;text-decoration:none;
    font-family:var(--mono);font-size:13px;
    text-transform:uppercase;letter-spacing:.14em;
    border:1px solid var(--ink);
    transition:all .2s;cursor:pointer;background:none;
  }
  .btn-primary{background:var(--ink);color:var(--bone)}
  .btn-primary:hover{background:var(--red);border-color:var(--red)}
  .btn-ghost{color:var(--ink)}
  .btn-ghost:hover{background:var(--ink);color:var(--bone)}
  .btn .arrow{transition:transform .2s}
  .btn:hover .arrow{transform:translateX(4px)}

  /* Hero buttons override (live on dark) */
  .hero .btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
  .hero .btn-primary:hover{background:#fff;color:#0c0c0c;border-color:#fff}
  .hero .btn-ghost{color:#f7f1e3;border-color:rgba(247,241,227,.55)}
  .hero .btn-ghost:hover{background:#f7f1e3;color:#0c0c0c;border-color:#f7f1e3}

  .hero-meta{
    display:flex;gap:30px;margin-top:48px;
    border-top:1px solid rgba(247,241,227,.25);padding-top:24px;
    max-width:580px;flex-wrap:wrap;
  }
  .hero-meta .m{flex:1;min-width:120px}
  .hero-meta .num{
    font-family:var(--display);font-size:38px;line-height:1;font-weight:800;letter-spacing:-.02em;
    color:#f7f1e3;
  }
  .hero-meta .num .ruby{color:var(--red)}
  .hero-meta .lbl{
    font-family:var(--mono);font-size:11px;
    text-transform:uppercase;letter-spacing:.16em;
    color:rgba(247,241,227,.6);margin-top:6px;
  }

  /* Hero right column: stage + Ver video button */
  .hero-right{display:flex;flex-direction:column;align-items:center;gap:18px;width:100%;max-width:420px;margin:0 auto}
  @media(max-width:980px){.hero-right{max-width:360px}}
  .qr-stage{
    position:relative;
    aspect-ratio: 1 / 1.06;
    width:100%;
    background:rgba(247,241,227,.05);
    border:1px solid rgba(247,241,227,.25);
    padding:36px;
    display:flex;align-items:center;justify-content:center;
    backdrop-filter:blur(6px);
    -webkit-backdrop-filter:blur(6px);
  }
  .qr-stage::before{
    content:"";position:absolute;inset:10px;border:1px dashed rgba(247,241,227,.2);pointer-events:none;
  }
  .qr-art{
    width:100%;height:100%;
    display:flex;align-items:center;justify-content:center;
    position:relative;
  }
  .qr-art img.logo-img{
    max-width:100%;max-height:100%;width:auto;height:auto;
    object-fit:contain;display:block;
  }

  /* Ver video button — red, below hero stage */
  .video-cta{
    display:inline-flex;align-items:center;gap:12px;
    padding:16px 32px;
    background:var(--red);color:#fff;
    border:1px solid var(--red);
    font-family:var(--mono);font-size:13px;font-weight:500;
    text-transform:uppercase;letter-spacing:.18em;
    cursor:pointer;
    transition:background .2s, color .2s, border-color .2s, transform .15s;
    box-shadow:0 18px 40px -16px rgba(179,37,44,.6);
  }
  .video-cta:hover{background:#fff;color:#0c0c0c;border-color:#fff}
  .video-cta:active{transform:translateY(1px)}
  .video-cta .vc-play{
    width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.18);
    display:inline-flex;align-items:center;justify-content:center;
    transition:background .2s, color .2s;
  }
  .video-cta:hover .vc-play{background:rgba(12,12,12,.12);color:#0c0c0c}

  /* Demo video popup — full-viewport modal, video keeps its aspect ratio */
  .video-modal{
    position:fixed;inset:0;z-index:200;
    background:rgba(8,5,5,.86);
    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
    display:none;align-items:center;justify-content:center;
    padding:64px 32px 32px;
    opacity:0;transition:opacity .25s ease;
  }
  .video-modal.open{display:flex;opacity:1}
  .video-modal .vm-stage{
    position:relative;
    max-width:100%;max-height:100%;
    display:flex;align-items:center;justify-content:center;
    transform:scale(.96);transition:transform .35s cubic-bezier(.2,.9,.3,1.1);
  }
  .video-modal.open .vm-stage{transform:scale(1)}
  .video-modal video{
    display:block;
    max-width:90vw;
    max-height:calc(100vh - 96px);
    width:auto;height:auto;
    background:#000;
    box-shadow:0 40px 80px -20px rgba(0,0,0,.8),0 0 0 1px rgba(247,241,227,.1);
  }
  .video-modal .vm-empty{
    width:min(560px,90vw);aspect-ratio:9/16;
    background:#0c0c0c;
    border:1px dashed rgba(247,241,227,.3);
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;
    padding:40px;text-align:center;color:#f7f1e3;
  }
  .video-modal .vm-empty .ic{
    width:64px;height:64px;border-radius:50%;
    border:1px solid rgba(247,241,227,.35);
    display:flex;align-items:center;justify-content:center;
    font-size:24px;
  }
  .video-modal .vm-empty h4{
    font-family:var(--display);font-size:22px;font-weight:700;margin:0;letter-spacing:-.01em;
  }
  .video-modal .vm-empty p{
    font-family:var(--mono);font-size:11px;line-height:1.6;
    letter-spacing:.14em;text-transform:uppercase;
    color:rgba(247,241,227,.6);margin:0;max-width:340px;
  }
  .video-modal .vm-close{
    position:absolute;top:24px;right:24px;z-index:2;
    width:44px;height:44px;border-radius:50%;
    background:rgba(247,241,227,.08);border:1px solid rgba(247,241,227,.3);
    color:#f7f1e3;
    display:flex;align-items:center;justify-content:center;cursor:pointer;
    font-family:var(--mono);font-size:18px;
    transition:background .2s, color .2s, border-color .2s, transform .15s;
  }
  .video-modal .vm-close:hover{background:var(--red);border-color:var(--red);color:#fff;transform:rotate(90deg)}

  /* Side rail decorations */
  .siderail{
    position:absolute;top:140px;right:40px;
    font-family:var(--mono);font-size:11px;color:rgba(247,241,227,.6);
    text-transform:uppercase;letter-spacing:.18em;
    writing-mode:vertical-rl;transform:rotate(180deg);
    z-index:2;
  }

  /* SECTION HEADER */
  .sec-head{
    display:grid;grid-template-columns: 1fr 2fr;gap:40px;
    padding:80px 0 36px;border-bottom:1px solid currentColor;
    align-items:end;
  }
  @media(max-width:820px){.sec-head{grid-template-columns:1fr;gap:18px}}
  .sec-head .idx{
    font-family:var(--mono);font-size:12px;text-transform:uppercase;letter-spacing:.2em;
    opacity:.6;
  }
  .sec-head .idx .ruby{color:var(--red);opacity:1}
  .sec-head h2{
    font-family:var(--display);font-weight:800;
    font-size:clamp(38px,5.2vw,72px);line-height:.98;letter-spacing:-.025em;
    margin:0;text-wrap:balance;
  }
  .sec-head h2 em{font-style:normal;color:var(--red);font-weight:800}
  .sec-head h2 .pdot{display:inline-block;width:.16em;height:.16em;border-radius:50%;background:var(--red);margin-left:.02em;transform:translateY(-.04em)}

  /* DEMO: change-your-destination */
  .demo{padding:50px 0 80px;border-bottom:1px solid var(--ink)}
  .demo-grid{
    display:grid;grid-template-columns: 1fr 1fr;gap:60px;align-items:center;
  }
  @media(max-width:980px){.demo-grid{grid-template-columns:1fr}}
  .destinations{display:flex;flex-direction:column;gap:0;border-top:1px solid var(--ink)}
  .dest{
    border-bottom:1px solid var(--ink);
    padding:18px 6px;display:grid;grid-template-columns:48px 1fr auto;gap:16px;align-items:center;
    background:none;text-align:left;font-family:inherit;color:inherit;
    cursor:pointer;font-size:inherit;
    transition:background .25s, padding .25s;
    position:relative;width:100%;border-left:0;border-right:0;border-top:0;
  }
  .dest::before{
    content:"";position:absolute;left:0;top:0;bottom:0;width:0;background:var(--red);
    transition:width .25s;
  }
  .dest.active{padding-left:18px;background:rgba(26,26,26,.08)}
  .dest.active::before{width:4px}
  .dest .n{
    font-family:var(--mono);font-size:11px;color:var(--ash);letter-spacing:.16em;
  }
  .dest .t{
    font-family:var(--display);font-size:22px;line-height:1.15;font-weight:700;letter-spacing:-.01em;
  }
  .dest .t small{
    font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.16em;
    color:var(--ash);display:block;margin-top:4px;
  }
  .dest .a{
    font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.16em;color:var(--ash);
  }
  .dest.active .a{color:var(--red)}
  .demo-note{
    margin-top:22px;font-family:var(--mono);font-size:11px;letter-spacing:.14em;
    text-transform:uppercase;color:var(--ash);display:flex;align-items:center;gap:10px;
  }
  .demo-note::before{content:"";width:24px;height:1px;background:var(--red);display:inline-block}

  .phone{
    width:100%;
    max-width:300px;margin:0 auto;
    aspect-ratio:9/19.5;
    border-radius:46px;
    background:linear-gradient(140deg,#1a1a1a 0%,#0a0a0a 35%,#222 60%,#0a0a0a 100%);
    padding:11px;position:relative;
    box-shadow:
      0 1px 0 1px #2a2a2a inset,
      0 0 0 2px #3a3a3a,
      0 50px 80px -40px rgba(0,0,0,.65),
      0 20px 40px -20px rgba(0,0,0,.4);
  }
  /* Side buttons */
  .phone::before{
    /* volume + silent on left */
    content:"";position:absolute;left:-3px;top:18%;width:3px;height:30px;
    background:linear-gradient(90deg,#0a0a0a,#2a2a2a);border-radius:2px 0 0 2px;
    box-shadow:0 50px 0 #2a2a2a, 0 90px 0 #2a2a2a;
  }
  .phone::after{
    /* power on right */
    content:"";position:absolute;right:-3px;top:24%;width:3px;height:64px;
    background:linear-gradient(270deg,#0a0a0a,#2a2a2a);border-radius:0 2px 2px 0;
  }
  .phone .screen{
    width:100%;height:100%;border-radius:36px;
    background:var(--paper);overflow:hidden;
    display:flex;flex-direction:column;
    position:relative;
    box-shadow:0 0 0 1px rgba(0,0,0,.6) inset;
  }
  /* Dynamic Island */
  .phone .screen::before{
    content:"";position:absolute;top:11px;left:50%;transform:translateX(-50%);
    width:96px;height:30px;background:#000;border-radius:18px;z-index:5;
    box-shadow:0 0 0 1px #111 inset;
  }
  .phone .screen::after{
    /* camera dot on dynamic island */
    content:"";position:absolute;top:21px;left:calc(50% + 30px);
    width:9px;height:9px;border-radius:50%;
    background:radial-gradient(circle at 30% 30%, #1a3040 0%, #050505 70%);
    box-shadow:0 0 0 1px #0a0a0a, inset 0 0 0 2px #050505;
    z-index:6;
  }
  .phone .topbar{
    height:50px;display:flex;align-items:flex-end;justify-content:space-between;
    padding:0 28px 6px;font-family:var(--display);font-size:13px;font-weight:600;color:var(--ink);
    position:relative;z-index:2;
  }
  .phone .topbar .icons{display:flex;align-items:center;gap:5px;font-size:11px}
  .phone .topbar .sig{
    display:inline-flex;align-items:flex-end;gap:1px;height:9px;
  }
  .phone .topbar .sig i{display:inline-block;width:2.5px;background:var(--ink);border-radius:1px}
  .phone .topbar .sig i:nth-child(1){height:3px}
  .phone .topbar .sig i:nth-child(2){height:5px}
  .phone .topbar .sig i:nth-child(3){height:7px}
  .phone .topbar .sig i:nth-child(4){height:9px}
  .phone .topbar .bat{
    display:inline-flex;align-items:center;gap:2px;
    width:22px;height:11px;border:1.2px solid var(--ink);border-radius:3px;
    padding:1px;position:relative;
  }
  .phone .topbar .bat::after{
    content:"";position:absolute;right:-3px;top:50%;transform:translateY(-50%);
    width:2px;height:5px;background:var(--ink);border-radius:0 1px 1px 0;
  }
  .phone .topbar .bat i{flex:1;height:100%;background:var(--ink);border-radius:1px}
  .phone .content{
    flex:1;padding:14px 22px 22px;display:flex;flex-direction:column;gap:14px;
  }
  .phone .frame-label{
    font-family:var(--mono);font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:var(--ash);
  }
  .phone .frame-title{
    font-family:var(--display);font-size:24px;line-height:1.1;font-weight:800;letter-spacing:-.02em;
  }
  .phone .frame-title em{color:var(--red);font-style:normal;font-weight:800}
  .phone .media{
    aspect-ratio:1/1;background:#1a1815;color:#efe9dc;
    display:flex;align-items:center;justify-content:center;
    font-family:var(--mono);font-size:10px;letter-spacing:.18em;
    position:relative;overflow:hidden;border-radius:12px;
  }
  .phone .media img.frame-img{
    position:absolute;inset:0;width:100%;height:100%;object-fit:cover;
    transition:opacity .35s ease;
    z-index:1;
  }
  .phone .media .ph-icon{
    font-size:48px;font-family:var(--display);font-weight:800;
    position:relative;z-index:3;
    text-shadow:0 4px 24px rgba(0,0,0,.6);
    opacity:.95;
  }
  .phone .media .scrim{
    position:absolute;inset:0;z-index:2;pointer-events:none;
    background:linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,.5) 100%),
               linear-gradient(180deg,rgba(0,0,0,.18) 0%,rgba(0,0,0,0) 30%);
  }
  .phone .media .stripes{
    position:absolute;inset:0;z-index:1;
    background:repeating-linear-gradient(45deg, transparent 0 14px, rgba(255,255,255,.04) 14px 28px);
    opacity:0;transition:opacity .3s;
  }
  .phone .media.no-img .stripes{opacity:1}
  .phone .media::after{
    /* corner play marker */
    content:"";position:absolute;bottom:10px;right:10px;
    width:24px;height:24px;border-radius:50%;background:rgba(255,255,255,.15);
    backdrop-filter:blur(4px);
  }
  .phone .meta{display:flex;justify-content:space-between;align-items:center;font-family:var(--mono);font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--ash)}
  .phone .meta .pill{padding:4px 8px;background:var(--ink);color:var(--bone);border-radius:0;display:inline-flex;align-items:center;gap:5px}
  .phone .meta .pill::before{
    content:"";width:5px;height:5px;background:var(--red);border-radius:50%;
    box-shadow:0 0 0 0 rgba(196,48,37,.6);animation:pulse 1.6s infinite;
  }
  .phone .home-indicator{
    position:absolute;bottom:7px;left:50%;transform:translateX(-50%);
    width:120px;height:4px;background:var(--ink);border-radius:2px;opacity:.85;
  }

  /* HOW it works */
  .steps{padding:50px 0 80px;border-bottom:1px solid var(--ink);position:relative}
  .steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-top:1px solid var(--ink);border-bottom:1px solid var(--ink)}
  @media(max-width:820px){.steps-grid{grid-template-columns:1fr}}
  .step{
    padding:34px 28px 38px;border-right:1px solid var(--ink);
    position:relative;display:flex;flex-direction:column;gap:14px;min-height:300px;
  }
  .step:last-child{border-right:0}
  @media(max-width:820px){.step{border-right:0;border-bottom:1px solid var(--ink)}.step:last-child{border-bottom:0}}
  .step .sn{
    font-family:var(--display);font-size:64px;line-height:1;color:var(--ink);font-weight:800;letter-spacing:-.03em;
  }
  .step .sn em{font-style:normal;color:var(--red);font-weight:800}
  .step h3{
    font-family:var(--display);font-weight:800;font-size:26px;line-height:1.1;margin:4px 0 0;letter-spacing:-.02em;
  }
  .step p{font-size:15px;line-height:1.55;color:var(--ink-2);margin:0}
  .step .stag{
    margin-top:auto;font-family:var(--mono);font-size:10px;text-transform:uppercase;letter-spacing:.18em;color:var(--ash);
  }

  /* TECH SPECS */
  .specs{padding:50px 0 80px;border-bottom:1px solid var(--ink)}
  .specs-grid{display:grid;grid-template-columns: 1.05fr .95fr;gap:60px;align-items:start}
  @media(max-width:980px){.specs-grid{grid-template-columns:1fr;gap:40px}}
  .spec-card{
    border:1px solid var(--ink);padding:30px;background:var(--paper);
    display:flex;flex-direction:column;gap:22px;
  }
  .spec-row{
    display:grid;grid-template-columns:auto 1fr;gap:22px;padding-bottom:18px;border-bottom:1px dashed rgba(12,11,10,.25);
  }
  .spec-row:last-child{border-bottom:0;padding-bottom:0}
  .spec-row .k{font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.18em;color:var(--ash);min-width:120px}
  .spec-row .v{font-family:var(--display);font-size:20px;line-height:1.25;font-weight:700;letter-spacing:-.01em}
  .spec-row .v small{font-family:var(--mono);font-size:11px;color:var(--ash);text-transform:uppercase;letter-spacing:.16em;display:block;margin-top:6px;line-height:1.5}

  /* Body zone map — V2 */
  .body-zone{
    border:1px solid currentColor;background:var(--theme-card, #fff);padding:0;
    display:grid;grid-template-columns: 1fr;gap:0;align-items:stretch;
    overflow:hidden;
  }
  .bz-head{
    display:flex;justify-content:space-between;align-items:center;
    padding:18px 24px;border-bottom:1px solid currentColor;gap:16px;
  }
  .bz-head h4{margin:0;font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.18em;opacity:.7;font-weight:500}
  .bz-toggle{
    display:flex;border:1px solid currentColor;background:transparent;
  }
  .bz-toggle button{
    background:transparent;border:0;
    padding:7px 14px;font-family:var(--mono);font-size:11px;
    text-transform:uppercase;letter-spacing:.14em;cursor:pointer;
    color:inherit;transition:background .2s, color .2s;
  }
  .bz-toggle button + button{border-left:1px solid currentColor}
  .bz-toggle button.on{background:var(--ink);color:var(--bone)}

  .bz-body{
    display:grid;grid-template-columns: 280px 1fr 240px;gap:0;
    min-height:480px;
  }
  @media(max-width:980px){.bz-body{grid-template-columns:1fr;min-height:auto}}

  .bz-list-col{
    padding:22px 22px 24px;border-right:1px solid currentColor;
    display:flex;flex-direction:column;gap:0;
  }
  @media(max-width:980px){.bz-list-col{border-right:0;border-bottom:1px solid currentColor}}
  .bz-list-head{
    font-family:var(--mono);font-size:10px;letter-spacing:.18em;text-transform:uppercase;
    opacity:.55;margin-bottom:12px;
  }
  .zone-list{display:flex;flex-direction:column;gap:0}
  .zone-li{
    display:grid;grid-template-columns:18px 1fr auto;gap:12px;align-items:center;
    padding:13px 4px;border-bottom:1px dashed rgba(127,127,127,.25);cursor:pointer;
    background:none;border-left:0;border-right:0;border-top:0;font-family:inherit;color:inherit;
    text-align:left;width:100%;font-size:inherit;
    transition:padding .2s, background .2s, color .2s;
    position:relative;
  }
  .zone-li:last-child{border-bottom:0}
  .zone-li::before{
    content:"";position:absolute;left:-22px;top:0;bottom:0;width:0;
    background:var(--red);transition:width .25s;
  }
  .zone-li.active{padding-left:14px;color:var(--red)}
  .zone-li.active::before{width:3px;left:-12px}
  .zone-li .dt{
    width:11px;height:11px;border-radius:50%;background:#1a1a1a;
    box-shadow:0 0 0 0 rgba(26,26,26,.4);transition:box-shadow .3s;
  }
  .zone-li.warn .dt{background:#b3252c}
  .zone-li.active .dt{box-shadow:0 0 0 4px rgba(179,37,44,.18)}
  .zone-li .nm{font-family:var(--display);font-size:15px;font-weight:600;letter-spacing:-.005em}
  .zone-li .tg{font-family:var(--mono);font-size:9px;letter-spacing:.16em;text-transform:uppercase;opacity:.55}
  .zone-li.warn .tg{color:#b3252c;opacity:1}

  /* Map column */
  .bz-map-col{
    position:relative;padding:18px;
    background:
      linear-gradient(rgba(127,127,127,.04) 1px, transparent 1px) 0 0/100% 24px,
      linear-gradient(90deg, rgba(127,127,127,.04) 1px, transparent 1px) 0 0/24px 100%;
    display:flex;align-items:stretch;justify-content:center;
    min-height:520px;
  }
  /* Coordinate corner ticks */
  .bz-map-col::before, .bz-map-col::after{
    content:"";position:absolute;width:14px;height:14px;
    border:1px solid currentColor;opacity:.6;
  }
  .bz-map-col::before{top:14px;left:14px;border-right:0;border-bottom:0}
  .bz-map-col::after{bottom:14px;right:14px;border-left:0;border-top:0}
  .bz-corner-tr, .bz-corner-bl{position:absolute;width:14px;height:14px;border:1px solid currentColor;opacity:.6}
  .bz-corner-tr{top:14px;right:14px;border-left:0;border-bottom:0}
  .bz-corner-bl{bottom:14px;left:14px;border-right:0;border-top:0}

  .body-map{
    position:relative;
    width:min(440px, 100%);
    aspect-ratio:1395/1690;
    margin:0 auto;
    max-height:520px;
  }
  .body-map svg.silhouette,
  .body-map img.silhouette{
    position:absolute;inset:0;width:100%;height:100%;
    object-fit:contain;
    transition:opacity .35s ease;
    pointer-events:none;
  }
  .body-map svg.silhouette.hidden,
  .body-map img.silhouette.hidden{opacity:0;pointer-events:none}

  /* Pulsing dots */
  .zone-dot{
    position:absolute;width:14px;height:14px;border-radius:50%;
    background:#1a1a1a;
    border:0;cursor:pointer;
    transform:translate(-50%,-50%);
    transition:transform .25s ease;
    z-index:3;
  }
  .zone-dot::before{
    content:"";position:absolute;inset:-7px;border-radius:50%;
    border:1.5px solid currentColor;opacity:.35;
    transition:opacity .25s ease, inset .25s ease;
  }
  .zone-dot::after{
    content:"";position:absolute;inset:-7px;border-radius:50%;
    background:currentColor;opacity:0;
    animation:zonePulse 2.4s ease-out infinite;
  }
  @keyframes zonePulse{
    0%{inset:-3px;opacity:.45}
    100%{inset:-14px;opacity:0}
  }
  .zone-dot.hidden{display:none}
  .zone-dot.good{color:#1a1a1a}
  .zone-dot.warn{color:#b3252c;background:#b3252c}
  .zone-dot:hover, .zone-dot.active{transform:translate(-50%,-50%) scale(1.3)}
  .zone-dot:hover::before, .zone-dot.active::before{inset:-10px;opacity:.7}

  /* Crosshair on active dot */
  .zone-dot.active::after{display:none}
  .zone-crosshair{
    position:absolute;pointer-events:none;z-index:2;
    width:60px;height:60px;transform:translate(-50%,-50%);
    opacity:0;transition:opacity .25s;
  }
  .zone-crosshair.show{opacity:1}
  .zone-crosshair::before, .zone-crosshair::after{
    content:"";position:absolute;background:currentColor;opacity:.4;
  }
  .zone-crosshair::before{left:50%;top:0;bottom:0;width:1px;transform:translateX(-50%)}
  .zone-crosshair::after{top:50%;left:0;right:0;height:1px;transform:translateY(-50%)}

  /* Detail panel */
  .bz-detail-col{
    padding:22px 22px 24px;border-left:1px solid currentColor;
    display:flex;flex-direction:column;gap:18px;
  }
  @media(max-width:980px){.bz-detail-col{border-left:0;border-top:1px solid currentColor}}
  .bz-detail-head{
    font-family:var(--mono);font-size:10px;letter-spacing:.18em;text-transform:uppercase;
    opacity:.55;
  }
  .bz-detail-name{
    font-family:var(--display);font-size:24px;line-height:1.05;font-weight:800;
    letter-spacing:-.015em;margin:0;
  }
  .bz-detail-status{
    display:inline-flex;align-items:center;gap:8px;
    padding:5px 10px;font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.14em;
    border:1px solid currentColor;align-self:flex-start;
  }
  .bz-detail-status::before{
    content:"";width:7px;height:7px;border-radius:50%;background:currentColor;
  }
  .bz-detail-status.optimal{color:#1a1a1a}
  .bz-detail-status.good{color:#4a4a4a}
  .bz-detail-status.warn{color:#b3252c}
  .bz-stats{
    display:flex;flex-direction:column;gap:0;border-top:1px dashed rgba(127,127,127,.3);
  }
  .bz-stat{
    display:grid;grid-template-columns:1fr auto;gap:12px;
    padding:10px 0;border-bottom:1px dashed rgba(127,127,127,.3);
  }
  .bz-stat .k{font-family:var(--mono);font-size:10px;letter-spacing:.16em;text-transform:uppercase;opacity:.6}
  .bz-stat .v{font-family:var(--display);font-size:14px;font-weight:700;letter-spacing:-.005em}
  .bz-note{
    font-family:var(--sans);font-size:12px;line-height:1.5;opacity:.78;
    border-left:2px solid var(--red);padding:4px 0 4px 12px;margin-top:auto;
  }

  /* PRICING */
  .pricing{padding:50px 0 90px;border-bottom:1px solid var(--ink);background:var(--bone)}
  .plans{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border:1px solid var(--ink)}
  @media(max-width:880px){.plans{grid-template-columns:1fr}}
  .plan{
    padding:36px 28px 32px;border-right:1px solid var(--ink);background:var(--paper);
    display:flex;flex-direction:column;gap:20px;position:relative;min-height:560px;
  }
  .plan:last-child{border-right:0}
  @media(max-width:880px){.plan{border-right:0;border-bottom:1px solid var(--ink)}.plan:last-child{border-bottom:0}}
  .plan.featured{background:var(--ink);color:var(--bone)}
  .plan.featured h3, .plan.featured .price, .plan.featured li{color:var(--bone)}
  .plan.featured .price-curr, .plan.featured .price-tag{color:var(--bone)}
  .plan.featured .tag{background:var(--red);color:var(--bone);border-color:var(--red)}
  .plan.featured .btn{background:var(--red);color:var(--bone);border-color:var(--red)}
  .plan.featured .btn:hover{background:var(--bone);color:var(--ink);border-color:var(--bone)}
  .plan.featured .mute{color:rgba(239,233,220,.7)}
  .plan.featured li::before{background:var(--red)}

  .plan .tag{
    position:absolute;top:-1px;right:-1px;
    padding:8px 14px;background:var(--paper);border:1px solid var(--ink);
    font-family:var(--mono);font-size:10px;text-transform:uppercase;letter-spacing:.18em;
  }
  .plan h3{font-family:var(--display);font-weight:800;font-size:30px;margin:0;line-height:1;letter-spacing:-.02em;text-transform:uppercase}
  .plan h3 em{font-style:normal;color:var(--red);font-weight:800}
  .plan .desc{font-size:14px;line-height:1.5;color:var(--ash-2);margin:0;min-height:42px}
  .plan.featured .desc{color:rgba(239,233,220,.7)}
  .plan .price{
    font-family:var(--display);font-size:50px;line-height:1;display:flex;align-items:baseline;gap:6px;font-weight:800;letter-spacing:-.02em;
  }
  .plan .price-curr{font-size:22px;color:var(--ash)}
  .plan .price-tag{font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.18em;color:var(--ash);margin-top:-6px}
  .plan ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:12px;flex:1}
  .plan li{
    position:relative;padding-left:22px;font-size:14px;line-height:1.45;
  }
  .plan li::before{
    content:"";position:absolute;left:0;top:.55em;width:10px;height:1px;background:var(--ink);
  }
  .plan .btn{margin-top:auto}

  /* FAQ */
  .faq{padding:60px 0 90px;border-bottom:1px solid var(--ink)}
  .faq-grid{display:grid;grid-template-columns:1fr;gap:0;border-top:1px solid var(--ink)}
  .q{
    border-bottom:1px solid var(--ink);padding:0;background:none;width:100%;text-align:left;
    color:inherit;font-family:inherit;cursor:pointer;border-left:0;border-right:0;border-top:0;
  }
  .q-head{display:grid;grid-template-columns:auto 1fr auto;gap:22px;align-items:center;padding:22px 6px}
  .q-num{font-family:var(--mono);font-size:11px;letter-spacing:.18em;color:var(--ash);min-width:30px}
  .q-t{font-family:var(--display);font-size:clamp(20px,2.6vw,28px);line-height:1.15;font-weight:700;letter-spacing:-.015em}
  .q-t em{font-style:normal;color:var(--red);font-weight:800}
  .q-x{
    width:26px;height:26px;border:1px solid var(--ink);border-radius:50%;
    display:flex;align-items:center;justify-content:center;font-family:var(--mono);
    font-size:14px;transition:transform .25s, background .2s, color .2s;
  }
  .q.open .q-x{background:var(--red);color:var(--bone);border-color:var(--red);transform:rotate(45deg)}
  .q-body{
    max-height:0;overflow:hidden;transition:max-height .35s ease;
    font-size:15px;line-height:1.6;color:var(--ink-2);
  }
  .q-body-inner{padding:0 6px 26px 78px;max-width:780px}
  .q.open .q-body{max-height:300px}

  /* CTA Final */
  .final{
    background:var(--ink);color:var(--bone);
    padding:90px 0 100px;position:relative;overflow:hidden;
  }
  .final::before{
    content:"";position:absolute;inset:0;
    background:radial-gradient(60% 80% at 80% 50%, rgba(196,48,37,.25), transparent 70%);
    pointer-events:none;
  }
  .final h2{
    font-family:var(--display);font-weight:800;
    font-size:clamp(48px,8vw,120px);line-height:.97;letter-spacing:-.03em;
    margin:0;max-width:1000px;text-wrap:balance;
  }
  .final h2 em{font-style:normal;color:var(--red);font-weight:800}
  .final p{font-size:18px;max-width:520px;color:rgba(239,233,220,.75);margin:28px 0 36px}
  .final .btn-primary{background:var(--bone);color:var(--ink);border-color:var(--bone)}
  .final .btn-primary:hover{background:var(--red);color:var(--bone);border-color:var(--red)}
  .final .btn-ghost{color:var(--bone);border-color:var(--bone)}
  .final .btn-ghost:hover{background:var(--bone);color:var(--ink)}
  .final-row{display:grid;grid-template-columns:1fr;gap:60px;align-items:end;position:relative;z-index:2}
  @media(max-width:880px){.final-row{grid-template-columns:1fr}}

  /* Footer */
  footer{background:var(--ink);color:var(--bone);border-top:1px solid #222;padding:54px 0 30px;font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.18em}
  footer .foot-top{
    display:grid;grid-template-columns:auto 1fr;gap:60px;align-items:start;padding-bottom:40px;border-bottom:1px solid rgba(247,241,227,.12);margin-bottom:24px;
  }
  @media(max-width:720px){footer .foot-top{grid-template-columns:1fr;gap:32px}}
  footer .foot-logo{
    width:200px;max-width:50vw;display:block;background:#fff;padding:16px;
  }
  footer .foot-logo-wrap{align-self:start}
  footer .foot-cols{
    display:grid;grid-template-columns:repeat(3,1fr);gap:40px;
  }
  @media(max-width:720px){footer .foot-cols{grid-template-columns:repeat(2,1fr);gap:28px}}
  footer .foot-col h5{
    margin:0 0 16px;font-family:var(--mono);font-size:10px;letter-spacing:.22em;color:rgba(247,241,227,.5);font-weight:500;
  }
  footer .foot-col ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px}
  footer .foot-col a{color:rgba(239,233,220,.85);text-decoration:none;font-family:var(--mono);font-size:11px;letter-spacing:.14em;text-transform:uppercase;transition:color .2s}
  footer .foot-col a:hover{color:var(--red)}
  footer .row{display:flex;justify-content:space-between;gap:24px;flex-wrap:wrap;align-items:center}
  footer a{color:rgba(239,233,220,.7);text-decoration:none}
  footer a:hover{color:var(--red)}

  /* Reveal anim */
  .reveal{opacity:0;transform:translateY(18px);transition:opacity .8s ease, transform .8s ease}
  .reveal.in{opacity:1;transform:none}

  /* Theme override */
  body.dark{
    --paper:#0e0e0e;
    --bone:#0e0e0e;
    --bone-2:#161616;
    --ink:#f1ebd9;
    --ink-2:#dcd3bc;
    --graphite:#bdbdbd;
    --ash:#a5a097;
    --ash-2:#878179;
    --light-gray:#3a3a3a;
    --rule:#f1ebd9;
  }
  body.dark .plan.featured{background:#161616;color:var(--ink)}
  body.dark .plan.featured h3, body.dark .plan.featured .price, body.dark .plan.featured li{color:var(--ink)}
  body.dark .plan.featured .desc{color:var(--ash)}
  body.dark .final{background:#161616}
  body.dark .qr-stage{background:#161616}
  body.dark .dest.active{background:#1a1a1a}

  /* ============= NEW SECTIONS ============= */

  /* PROBLEM */
  .problem{padding:50px 0 90px;border-bottom:1px solid currentColor;position:relative;overflow:hidden}
  .problem .lead{
    font-family:var(--display);font-weight:300;font-style:italic;
    font-size:clamp(28px,3.4vw,42px);line-height:1.2;letter-spacing:-.01em;
    max-width:880px;margin:50px 0 40px;text-wrap:balance;
  }
  .problem .lead em{font-style:normal;color:var(--red);font-weight:600}
  .pain-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:0;
    border-top:1px solid currentColor;border-bottom:1px solid currentColor;
  }
  @media(max-width:820px){.pain-grid{grid-template-columns:1fr}}
  .pain{
    padding:32px 28px;border-right:1px solid currentColor;
    display:flex;flex-direction:column;gap:14px;min-height:240px;
  }
  .pain:last-child{border-right:0}
  @media(max-width:820px){.pain{border-right:0;border-bottom:1px solid currentColor}.pain:last-child{border-bottom:0}}
  .pain .ic{
    width:42px;height:42px;border:1px solid currentColor;display:flex;align-items:center;justify-content:center;
    font-family:var(--mono);font-size:14px;opacity:.85;
  }
  .pain h4{font-family:var(--display);font-weight:700;font-size:22px;letter-spacing:-.01em;margin:0;line-height:1.15}
  .pain p{font-size:14px;line-height:1.55;opacity:.75;margin:0}
  .problem-kicker{
    margin-top:36px;font-family:var(--display);font-weight:800;font-size:clamp(34px,5vw,64px);
    line-height:1;letter-spacing:-.025em;
  }
  .problem-kicker em{font-style:normal;color:var(--red)}

  /* BENEFITS */
  .benefits{padding:50px 0 90px;border-bottom:1px solid currentColor;position:relative}
  .benefits-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:0;margin-top:30px;
    border:1px solid currentColor;
  }
  @media(max-width:880px){.benefits-grid{grid-template-columns:1fr}}
  .benefit{
    padding:38px 30px;border-right:1px solid currentColor;
    display:flex;flex-direction:column;gap:18px;min-height:300px;
    position:relative;
  }
  .benefit:last-child{border-right:0}
  @media(max-width:880px){.benefit{border-right:0;border-bottom:1px solid currentColor}.benefit:last-child{border-bottom:0}}
  .benefit .bn{
    font-family:var(--mono);font-size:11px;letter-spacing:.18em;text-transform:uppercase;opacity:.6;
  }
  .benefit h3{
    font-family:var(--display);font-weight:800;font-size:28px;line-height:1.1;
    letter-spacing:-.02em;margin:0;text-wrap:balance;
  }
  .benefit h3 em{font-style:normal;color:var(--red);font-weight:800}
  .benefit p{font-size:15px;line-height:1.55;opacity:.78;margin:0}
  .benefit .mark{
    margin-top:auto;font-family:var(--display);font-size:64px;line-height:1;font-weight:800;
    color:var(--red);letter-spacing:-.04em;opacity:.85;
  }

  /* Apply theme tokens generically — flips colors when a section sets [data-theme] */
  section[data-theme], header.hero[data-theme]{
    color:var(--theme-fg);
  }
  /* Each themed section paints its own background — this is the "scroll-changing" bg */
  section[data-theme]{
    background:var(--theme-bg);
    transition: background-color .6s cubic-bezier(.4,0,.2,1), color .6s cubic-bezier(.4,0,.2,1);
  }
  /* Inside dark themes, make link/border tokens behave */
  [data-theme="ink"] .sec-head, [data-theme="char"] .sec-head, [data-theme="blood"] .sec-head{border-color:rgba(247,241,227,.25)}
  [data-theme="ink"] .pain-grid, [data-theme="char"] .pain-grid{border-color:rgba(247,241,227,.25)}
  [data-theme="ink"] .pain, [data-theme="char"] .pain{border-color:rgba(247,241,227,.15)}
  [data-theme="ink"] .pain .ic, [data-theme="char"] .pain .ic{border-color:rgba(247,241,227,.4)}
  [data-theme="char"] .benefits-grid{border-color:rgba(247,241,227,.25)}
  [data-theme="char"] .benefit{border-right-color:rgba(247,241,227,.15);border-bottom-color:rgba(247,241,227,.15)}
  /* Final on blood theme keeps its red radial */
  [data-theme="blood"].final{background:var(--theme-bg)}
  [data-theme="blood"].final::before{
    background:radial-gradient(60% 80% at 80% 50%, rgba(255,180,170,.18), transparent 70%),
               radial-gradient(40% 60% at 10% 100%, rgba(0,0,0,.55), transparent 70%);
  }

</style>
</head>
<body>

<!-- Inline SVG QR helper. Pseudo-QR pattern; same DOM reused. -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <defs>
    <symbol id="qrSymbol" viewBox="0 0 33 33">
      <!-- 33x33 grid, cells 1x1 -->
      <g class="qr-cells" fill="currentColor"></g>
    </symbol>
  </defs>
</svg>

<!-- NAV -->
<nav class="top" id="topNav">
  <div class="wrap row">
    <a class="brand" href="#" aria-label="Dynamic Tattoos">
      <img class="brand-logo brand-light" src="{{ asset('images/designer/logo-nav-black.png') }}" alt="Dynamic Tattoos" />
      <img class="brand-logo brand-dark" src="{{ asset('images/designer/logo-nav-white.png') }}" alt="" aria-hidden="true" />
    </a>
    <ul>
      <li><a href="#concepto">Concepto</a></li>
      <li><a href="#demo">Demo</a></li>
      <li><a href="#tecnico">Técnico</a></li>
      <li><a href="#planes">Planes</a></li>
      <li><a href="#faq">FAQ</a></li>
      @auth
        <li><a href="{{ route('profile.index') }}">Mi cuenta</a></li>
      @else
        <li><a href="{{ route('login') }}">Iniciar sesión</a></li>
      @endauth
    </ul>
    @auth
      <a class="cta" href="{{ route('profile.index') }}">Mi panel</a>
    @else
      <a class="cta" href="{{ route('register') }}">Reservar QR</a>
    @endauth
  </div>
</nav>

<!-- Marquee -->
<div class="marquee">
  <div class="marquee-track">
    <span>Tinta inteligente</span>
    <span>Sin láser · sin arrepentimientos</span>
    <span>Infinitas historias por contar</span>
    <span>Tu piel · tu canal</span>
    <span>4 × 4 cm validado</span>
    <span>Tinta inteligente</span>
    <span>Sin láser · sin arrepentimientos</span>
    <span>Infinitas historias por contar</span>
    <span>Tu piel · tu canal</span>
    <span>4 × 4 cm validado</span>
  </div>
</div>

<!-- HERO -->
<header class="hero" data-theme-section="hero">
  <div class="hero-bg">
    <!-- VIDEO DE FONDO: Sube tu archivo a assets/hero-video.mp4 (y opcionalmente .webm)
         y descomenta las dos líneas <source> de abajo. Mientras tanto, se muestra
         un fondo animado en CSS como placeholder. -->
    <video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="">
      <!-- <source src="{{ asset('images/designer/hero-video.webm') }}" type="video/webm" /> -->
      <!-- <source src="{{ asset('images/designer/hero-video.mp4') }}" type="video/mp4" /> -->
    </video>
    <div class="hero-video-placeholder" aria-hidden="true"></div>
  </div>
  <div class="hero-overlay" id="heroOverlay"></div>

  <div class="wrap">
    <div class="hero-grid">
      <div>
        <div class="eyebrow">Tinta inteligente · Madrid · Lanzamiento 2026</div>
        <h1 class="head" style="font-weight: 700; width: 700px; height: 634.969px;">
          Tu historia cambia<br/>cada segundo.<br/>
          <span class="ev">Tu tatuaje también<span class="pdot"></span></span>
        </h1>
        <p class="hero-lede">
          Fusionamos lo permanente del arte corporal con la libertad absoluta del universo digital.
          <strong>Un solo tatuaje en tu piel. Infinitas historias por contar.</strong>
        </p>
        <div class="hero-ctas">
          <a class="btn btn-primary" href="#planes">Haz tu tatuaje dinámico <span class="arrow">→</span></a>
          <a class="btn btn-ghost" href="#demo">Ver cómo funciona</a>
        </div>

        <div class="hero-meta">
          <div class="m">
            <div class="num">4×4<span class="ruby">cm</span></div>
            <div class="lbl">Estándar validado</div>
          </div>
          <div class="m">
            <div class="num">∞</div>
            <div class="lbl">Cambios de destino</div>
          </div>
          <div class="m">
            <div class="num">0<span class="ruby">·</span>láser</div>
            <div class="lbl">Sin arrepentimientos</div>
          </div>
        </div>
      </div>

      <div class="hero-right">
        <div class="qr-stage" id="qrStage">
          <div class="qr-art">
            <img class="logo-img" src="{{ asset('images/designer/logo-hero-qr.png') }}" alt="Dynamic Tattoos" />
          </div>
        </div>
        <button class="video-cta" id="openDemoVideo" type="button">
          <span class="vc-play" aria-hidden="true">
            <svg viewBox="0 0 12 14" width="10" height="12" fill="currentColor"><polygon points="0,0 12,7 0,14"/></svg>
          </span>
          Ver video
        </button>
      </div>
    </div>
  </div>
  <div class="siderail">Est. 2026 · La revolución de la tinta inteligente</div>
</header>

<!-- PROBLEM -->
<section class="problem" id="problema" data-theme="ink">
  <div class="wrap">
    <div class="sec-head">
      <div class="idx">00 <span class="ruby">/</span> El problema</div>
      <h2>El mundo evoluciona.<br/>¿Por qué tu piel<br/>tendría que <em>quedarse&nbsp;atrás</em>?</h2>
    </div>
    <p class="lead">
      Hasta hoy, tatuarse significaba tomar una decisión <em>estática</em> para toda la vida.
      Un nombre. Una etapa. Una red social que hoy te define y mañana es solo un recuerdo.
    </p>
    <div class="pain-grid">
      <div class="pain">
        <div class="ic">01</div>
        <h4>Sesiones de láser interminables</h4>
        <p>Meses de dolor, irritación y promesas a medias. Caro, lento, y casi nunca completo.</p>
      </div>
      <div class="pain">
        <div class="ic">02</div>
        <h4>Un cover-up más grande y oscuro</h4>
        <p>Tapar con más tinta. Aceptar un diseño que no elegiste solo para esconder el anterior.</p>
      </div>
      <div class="pain">
        <div class="ic">03</div>
        <h4>Vivir con arrepentimiento</h4>
        <p>Mirarse al espejo cada mañana y recordar a alguien — o algo — que ya no eres.</p>
      </div>
    </div>
    <div class="problem-kicker">Eso se <em>terminó</em>.</div>
  </div>
</section>

<!-- DEMO -->
<section class="demo" id="demo" data-theme="bone">
  <div class="wrap">
    <div class="sec-head">
      <div class="idx">01 <span class="ruby">/</span> La solución</div>
      <h2>Un tatuaje.<br/>Mil <em>destinos</em>.</h2>
    </div>

    <div style="height:30px"></div>
    <p style="max-width:720px;font-size:18px;line-height:1.55;margin:0 0 8px;font-weight:500">
      Tu tatuaje no cambia. Lo que cambia es <strong>a dónde lleva</strong>.
    </p>
    <p style="max-width:720px;font-size:16px;line-height:1.6;opacity:.78;margin:0">
      Cuando alguien escanea tu QR con la cámara, lo enviamos a la URL que tú elijas <strong>en ese momento</strong>:
      tu portfolio, un video, tu Instagram, una landing privada. Y lo cambias las veces que quieras desde tu panel —
      en segundos, sin volver al estudio.
    </p>

    <div style="height:50px"></div>

    <div class="demo-grid">
      <div>
        <div class="eyebrow" style="color:inherit;opacity:.7;margin-bottom:18px">
          Pulsa una opción <span class="ruby">·</span> mira la pantalla
        </div>
        <div class="destinations" id="destinations">
          <button class="dest active" data-key="portfolio" data-url="portfolio.miestudio.com">
            <div class="n">01</div>
            <div class="t">Tu web profesional<small>Para clientes, marca personal, currículum visual</small></div>
            <div class="a">Activo →</div>
          </button>
          <button class="dest" data-key="instagram" data-url="instagram.com/aria.ink">
            <div class="n">02</div>
            <div class="t">Tu red social favorita<small>Instagram, TikTok, LinkedIn · siempre al día</small></div>
            <div class="a">Cambiar →</div>
          </button>
          <button class="dest" data-key="video" data-url="dyn.tt/u/aria/baile-2026">
            <div class="n">03</div>
            <div class="t">Un video privado<small>Una dedicatoria, un recuerdo, un mensaje</small></div>
            <div class="a">Cambiar →</div>
          </button>
          <button class="dest" data-key="profile" data-url="dyn.tt/aria">
            <div class="n">04</div>
            <div class="t">Tu página Dynamic<small>Galería propia · feed multimedia · enlaces</small></div>
            <div class="a">Cambiar →</div>
          </button>
        </div>
        <div class="demo-note">El QR tatuado siempre es el mismo · cambia solo el destino.</div>
      </div>

      <div>
        <div class="phone">
          <div class="screen">
            <div class="topbar">
              <span>9:41</span>
              <div class="icons">
                <span class="sig"><i></i><i></i><i></i><i></i></span>
                <span class="bat"><i></i></span>
              </div>
            </div>
            <div class="content" id="phoneFrame">
              <span class="frame-label" id="frameLabel">DESTINO ACTUAL</span>
              <div class="frame-title" id="frameTitle">Tu web <em>profesional</em></div>
              <div class="media no-img" id="frameMedia">
                <img class="frame-img" id="frameImg" alt="" style="display:none" />
                <div class="stripes"></div>
                <div class="scrim"></div>
                <div class="ph-icon" id="frameIcon">¶</div>
              </div>
              <div class="meta">
                <span id="frameUrl">portfolio.miestudio.com</span>
                <span class="pill">en vivo</span>
              </div>
              <div style="font-family:var(--mono);font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--ash)">Última actualización · hace 2 min</div>
            </div>
            <div class="home-indicator"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STEPS -->
<section class="steps" id="concepto" data-theme="paper">
  <div class="wrap">
    <div class="sec-head">
      <div class="idx">02 <span class="ruby">/</span> Cómo funciona</div>
      <h2>Tu vida en movimiento,<br/>directo en tu <em>piel</em>.</h2>
    </div>
    <div style="height:40px"></div>
    <div class="steps-grid">
      <div class="step">
        <div class="sn">0<em>1</em></div>
        <h3>Escanea el arte</h3>
        <p>Cualquiera puede acercar su smartphone a tu tatuaje. Diseñado bajo estándares artísticos estrictos: línea limpia, contraste pleno, lectura instantánea.</p>
        <div class="stag">1 segundo · cualquier cámara</div>
      </div>
      <div class="step">
        <div class="sn">0<em>2</em></div>
        <h3>Descubre la magia</h3>
        <p>Al instante, tu piel transmite lo que tú decidiste mostrar en ese preciso momento. Una foto. Un video. Una historia. Un secreto.</p>
        <div class="stag">Sin app · directo al móvil</div>
      </div>
      <div class="step">
        <div class="sn">0<em>3</em></div>
        <h3>Evoluciona cuando quieras</h3>
        <p>¿Cambiaste de canal? ¿Quieres mostrar el video de tu último viaje? Lo actualizas en segundos desde el panel. Sin dolor. Sin láser. Sin arrepentimientos.</p>
        <div class="stag">Panel · infinito</div>
      </div>
    </div>
  </div>
</section>

<!-- BENEFITS -->
<section class="benefits" id="beneficios" data-theme="char">
  <div class="wrap">
    <div class="sec-head">
      <div class="idx">03 <span class="ruby">/</span> Por qué cambia todo</div>
      <h2>Un tatuaje. <em>Tres libertades</em><br/>que nadie te había dado antes.</h2>
    </div>
    <div class="benefits-grid">
      <div class="benefit">
        <div class="bn">Beneficio 01</div>
        <h3>Libertad <em>absoluta</em></h3>
        <p>Transforma el significado de tu tatuaje las veces que quieras. Hoy tu portfolio, mañana tu nuevo proyecto, pasado un recuerdo privado. Tu mente es el único límite.</p>
        <div class="mark">∞</div>
      </div>
      <div class="benefit">
        <div class="bn">Beneficio 02</div>
        <h3>Redirección <em>permanente</em></h3>
        <p>Olvídate de los enlaces rotos. Si cambia tu Instagram, mudas tus videos o reformas tu web, tu tatuaje seguirá funcionando perfectamente. De por vida.</p>
        <div class="mark">∞→</div>
      </div>
      <div class="benefit">
        <div class="bn">Beneficio 03</div>
        <h3>Estética <em>impecable</em></h3>
        <p>Diseñado junto a tatuadores profesionales en Negro Tinta y Rojo Pulso. Una pieza de arte minimalista, tecnológica y profundamente atractiva.</p>
        <div class="mark">▲</div>
      </div>
    </div>
  </div>
</section>

<!-- SPECS -->
<section class="specs" id="tecnico" data-theme="bone">
  <div class="wrap">
    <div class="sec-head">
      <div class="idx">04 <span class="ruby">/</span> Técnico & seguridad</div>
      <h2>Validado por<br/><em>profesionales</em> del tatuaje.</h2>
    </div>
    <div style="height:30px"></div>
    <p style="max-width:680px;font-size:17px;line-height:1.55;opacity:.82;margin:0 0 30px">
      Sabemos que tu piel es sagrada. Por eso no dejamos nada al azar — dimensiones, módulo, contraste y zonas verificadas con tatuadores que trabajan línea fina todos los días.
    </p>
    <div class="specs-grid">
      <div class="spec-card">
        <div class="spec-row">
          <div class="k">Dimensión</div>
          <div class="v">4 × 4 cm <small>Estándar validado · pruebas en curso a 3 × 3 cm sin perder escaneabilidad</small></div>
        </div>
        <div class="spec-row">
          <div class="k">Módulo</div>
          <div class="v">≥ 1.2 mm <small>Tamaño mínimo de cada celda para tinta saturada y borde nítido</small></div>
        </div>
        <div class="spec-row">
          <div class="k">Contraste</div>
          <div class="v">Tinta negra plena <small>Sin sombreado · sin degradado · fondo libre de tatuajes previos</small></div>
        </div>
        <div class="spec-row">
          <div class="k">Corrección de error</div>
          <div class="v">Nivel H (30%) <small>Tolera retoques, cicatrización y envejecimiento de la piel</small></div>
        </div>
        <div class="spec-row">
          <div class="k">Garantía</div>
          <div class="v">URL bajo tu control <small>Mientras el QR esté bien tatuado, la redirección siempre es tuya</small></div>
        </div>
      </div>

      <div class="body-zone" id="bodyZone">
        <div class="bz-head">
          <h4>Mapa anatómico · Zonas validadas</h4>
          <div class="bz-toggle" id="bzToggle">
            <button class="on" data-view="front">Frente</button>
            <button data-view="back">Espalda</button>
          </div>
        </div>
        <div class="bz-body">
          <!-- LIST -->
          <div class="bz-list-col">
            <div class="bz-list-head">8 zonas · 4×4 cm</div>
            <div class="zone-list" id="zoneList">
              <button class="zone-li active" data-zone="forearm"><span class="dt"></span><span class="nm">Antebrazo interno</span><span class="tg">Óptimo</span></button>
              <button class="zone-li" data-zone="biceps"><span class="dt"></span><span class="nm">Bíceps exterior</span><span class="tg">Óptimo</span></button>
              <button class="zone-li" data-zone="calf"><span class="dt"></span><span class="nm">Pantorrilla</span><span class="tg">Óptimo</span></button>
              <button class="zone-li" data-zone="chest"><span class="dt"></span><span class="nm">Pectoral</span><span class="tg">Bueno</span></button>
              <button class="zone-li" data-zone="shoulder"><span class="dt"></span><span class="nm">Hombro · escápula</span><span class="tg">Óptimo</span></button>
              <button class="zone-li" data-zone="back"><span class="dt"></span><span class="nm">Espalda baja</span><span class="tg">Óptimo</span></button>
              <button class="zone-li warn" data-zone="wrist"><span class="dt"></span><span class="nm">Muñeca</span><span class="tg">Curvatura</span></button>
              <button class="zone-li warn" data-zone="ribs"><span class="dt"></span><span class="nm">Costillas</span><span class="tg">Movimiento</span></button>
            </div>
          </div>

          <!-- MAP -->
          <div class="bz-map-col">
            <span class="bz-corner-tr"></span>
            <span class="bz-corner-bl"></span>
            <div class="body-map" id="bodyMap">
              <!-- FRONT silhouette — anatomical reference image -->
              <img class="silhouette front-view" src="{{ asset('images/designer/body-silhouette.jpg') }}" alt="" aria-hidden="true" />
              <!-- BACK silhouette — same image (flipped for variation) -->
              <img class="silhouette back-view hidden" src="{{ asset('images/designer/body-silhouette.jpg') }}" alt="" aria-hidden="true" style="transform:scaleX(-1)" />

              <!-- Crosshair indicator -->
              <div class="zone-crosshair" id="zoneCrosshair"></div>

              <!-- DOTS — front view (positions calibrated to silhouette) -->
              <div class="zone-dot good front-view" data-view="front" style="left:39%;top:55%" data-zone="forearm"></div>
              <div class="zone-dot good front-view" data-view="front" style="left:33%;top:32%" data-zone="biceps"></div>
              <div class="zone-dot good front-view" data-view="front" style="left:43%;top:80%" data-zone="calf"></div>
              <div class="zone-dot good front-view" data-view="front" style="left:55%;top:28%" data-zone="chest"></div>
              <div class="zone-dot warn front-view" data-view="front" style="left:40%;top:64%" data-zone="wrist"></div>
              <div class="zone-dot warn front-view" data-view="front" style="left:58%;top:40%" data-zone="ribs"></div>

              <!-- DOTS — back view (silhouette is flipped, but positions stay centered on body) -->
              <div class="zone-dot good back-view hidden" data-view="back" style="left:47%;top:26%" data-zone="shoulder"></div>
              <div class="zone-dot good back-view hidden" data-view="back" style="left:50%;top:48%" data-zone="back"></div>
              <div class="zone-dot good back-view hidden" data-view="back" style="left:61%;top:55%" data-zone="forearm"></div>
              <div class="zone-dot good back-view hidden" data-view="back" style="left:67%;top:32%" data-zone="biceps"></div>
              <div class="zone-dot good back-view hidden" data-view="back" style="left:57%;top:80%" data-zone="calf"></div>
            </div>
          </div>

          <!-- DETAIL -->
          <div class="bz-detail-col" id="bzDetail">
            <div class="bz-detail-head">Zona seleccionada</div>
            <h5 class="bz-detail-name" id="bzName">Antebrazo interno</h5>
            <span class="bz-detail-status optimal" id="bzStatus">Óptimo</span>
            <div class="bz-stats">
              <div class="bz-stat"><span class="k">Curvatura</span><span class="v" id="bzCurv">Plana</span></div>
              <div class="bz-stat"><span class="k">Movimiento piel</span><span class="v" id="bzMove">Bajo</span></div>
              <div class="bz-stat"><span class="k">Cicatrización</span><span class="v" id="bzHeal">3-4 sem.</span></div>
              <div class="bz-stat"><span class="k">Visibilidad</span><span class="v" id="bzVis">Alta</span></div>
              <div class="bz-stat"><span class="k">Tinta · módulo</span><span class="v" id="bzInk">≥ 1.2 mm</span></div>
            </div>
            <div class="bz-note" id="bzNote">
              Una de las zonas más usadas para QR dinámico. Piel firme, lectura perfecta a cualquier ángulo.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="pricing" id="planes" data-theme="paper">
  <div class="wrap">
    <div class="sec-head">
      <div class="idx">05 <span class="ruby">/</span> Planes</div>
      <h2>Elige cómo quieres<br/><em>evolucionar</em>.</h2>
    </div>
    <div style="height:50px"></div>
    <div class="plans">
      <div class="plan">
        <span class="tag">Básico</span>
        <h3>Básico</h3>
        <p class="desc">Vincula tu QR a una imagen o URL externa. Cambia el destino cuando quieras.</p>
        <div>
          <div class="price"><span class="price-curr">€</span>4<small style="font-size:32px">.90</small></div>
          <div class="price-tag">/ mes · facturado anual</div>
        </div>
        <ul>
          <li>QR único + ID permanente</li>
          <li>Redirección ilimitada a URL externa</li>
          <li>Imagen estática alojada (1 archivo)</li>
          <li>Panel web de gestión</li>
          <li>Estadísticas básicas de escaneo</li>
        </ul>
        <a class="btn btn-ghost" href="{{ route('register') }}?plan=basic">Empezar básico <span class="arrow">→</span></a>
      </div>

      <div class="plan featured">
        <span class="tag">Más elegido</span>
        <h3>Premium</h3>
        <p class="desc mute">Aloja tus videos directamente en la plataforma. Más calidad, más control.</p>
        <div>
          <div class="price"><span class="price-curr">€</span>9<small style="font-size:32px">.90</small></div>
          <div class="price-tag">/ mes · facturado anual</div>
        </div>
        <ul>
          <li>Todo lo del plan Básico</li>
          <li>Hasta 5 videos alojados (Full HD)</li>
          <li>Reproductor sin marca</li>
          <li>Programación de cambios por fecha</li>
          <li>Soporte prioritario</li>
        </ul>
        <a class="btn btn-primary" href="{{ route('register') }}?plan=premium">Activar Premium <span class="arrow">→</span></a>
      </div>

      <div class="plan">
        <span class="tag">Top</span>
        <h3>Premium <em>Top</em></h3>
        <p class="desc">Una página de perfil completa, estilo feed, donde gestionas múltiples fotos y videos.</p>
        <div>
          <div class="price"><span class="price-curr">€</span>19<small style="font-size:32px">.90</small></div>
          <div class="price-tag">/ mes · facturado anual</div>
        </div>
        <ul>
          <li>Todo lo del plan Premium</li>
          <li>Página de perfil personalizada (feed)</li>
          <li>Galería ilimitada · video + foto</li>
          <li>Enlaces tipo Linktree integrados</li>
          <li>Dominio propio opcional</li>
          <li>Acceso anticipado a 3×3 cm</li>
        </ul>
        <a class="btn btn-ghost" href="{{ route('register') }}?plan=top">Reservar Top <span class="arrow">→</span></a>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq" id="faq" data-theme="bone">
  <div class="wrap">
    <div class="sec-head">
      <div class="idx">06 <span class="ruby">/</span> Dudas frecuentes</div>
      <h2>Antes de que la aguja<br/>toque la <em>piel</em>.</h2>
    </div>
    <div style="height:30px"></div>
    <div class="faq-grid" id="faqList">
      <button class="q open">
        <div class="q-head"><div class="q-num">01</div><div class="q-t">¿Y si la URL original deja de existir?</div><div class="q-x">+</div></div>
        <div class="q-body"><div class="q-body-inner">No pasa nada. El QR tatuado siempre apunta a tu identificador permanente en Dynamic Tattoos. Si la URL externa muere, entras al panel y rediriges a una nueva. El tatuaje sigue funcionando.</div></div>
      </button>
      <button class="q">
        <div class="q-head"><div class="q-num">02</div><div class="q-t">¿Cuánto dura un QR tatuado en la piel?</div><div class="q-x">+</div></div>
        <div class="q-body"><div class="q-body-inner">Con tinta de calidad y un buen tatuador, igual que cualquier tatuaje fino: décadas. Usamos corrección de error nivel H (30%) para tolerar retoques, cicatrización y envejecimiento de la piel.</div></div>
      </button>
      <button class="q">
        <div class="q-head"><div class="q-num">03</div><div class="q-t">¿Puedo cancelar la suscripción?</div><div class="q-x">+</div></div>
        <div class="q-body"><div class="q-body-inner">Sí, en cualquier momento. Tu QR seguirá funcionando en modo de redirección estática (la última URL guardada). Si quieres seguir cambiando el destino, basta con reactivar.</div></div>
      </button>
      <button class="q">
        <div class="q-head"><div class="q-num">04</div><div class="q-t">¿Funciona si lo tatúo en la muñeca?</div><div class="q-x">+</div></div>
        <div class="q-body"><div class="q-body-inner">Funciona, pero la curvatura puede afectar la lectura. Recomendamos zonas planas: antebrazo interno, bíceps exterior, pantorrilla o pectoral. Consulta nuestra guía de zonificación arriba.</div></div>
      </button>
      <button class="q">
        <div class="q-head"><div class="q-num">05</div><div class="q-t">¿Necesito ser tatuador profesional?</div><div class="q-x">+</div></div>
        <div class="q-body"><div class="q-body-inner">No, pero te recomendamos uno con experiencia en líneas finas. Enviamos guía técnica y plantilla a tu estudio. Si vives en Madrid, Barcelona o Valencia tenemos estudios partner certificados.</div></div>
      </button>
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="final" data-theme="blood">
    <div class="wrap final-row">
      <div>
      <div style="font-family:var(--mono);font-size:12px;letter-spacing:.22em;text-transform:uppercase;opacity:.65;display:flex;align-items:center;gap:14px"><span style="width:36px;height:1px;background:var(--red);display:inline-block"></span>El cierre</div>
      <h2 style="margin-top:18px">En Dynamic&nbsp;Tattoos,<br/>lo hemos conseguido <em>para ti</em>.</h2>
      <p>No te conformes con un lienzo estático. Es hora de que tu arte corporal lata al mismo ritmo que tus experiencias. <strong style="color:#fff;font-weight:600">Tu historia empieza aquí. Hazla dinámica.</strong></p>
      <div class="hero-ctas" style="margin-top:0">
        <a class="btn btn-primary" href="{{ route('register') }}">Quiero mi Dynamic Tattoo <span class="arrow">→</span></a>
        <a class="btn btn-ghost" href="#demo">Contáctanos</a>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="foot-top">
      <div class="foot-logo-wrap">
        <img class="foot-logo" src="{{ asset('images/designer/logo-hero-qr.png') }}" alt="Dynamic Tattoos" />
      </div>
      <div class="foot-cols">
        <div class="foot-col">
          <h5>Producto</h5>
          <ul>
            <li><a href="#demo">Demo en vivo</a></li>
            <li><a href="#concepto">Cómo funciona</a></li>
            <li><a href="#tecnico">Técnico</a></li>
            <li><a href="#planes">Planes</a></li>
          </ul>
        </div>
        <div class="foot-col">
          <h5>Soporte</h5>
          <ul>
            <li><a href="#faq">FAQ</a></li>
            <li><a href="#">Estudios partner</a></li>
            <li><a href="#">Guía técnica</a></li>
            <li><a href="#">Contacto</a></li>
          </ul>
        </div>
        <div class="foot-col">
          <h5>Legal</h5>
          <ul>
            <li><a href="#">Términos</a></li>
            <li><a href="#">Privacidad</a></li>
            <li><a href="#">Cookies</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="row">
      <div>© 2026 Dynamic Tattoos · Madrid</div>
      <div>Tatuajes que evolucionan</div>
    </div>
  </div>
</footer>

<!-- Tweaks mount -->
<div id="tweaks-root"></div>

<!-- Demo video popup -->
<div class="video-modal" id="videoModal" role="dialog" aria-modal="true" aria-label="Video Dynamic Tattoos">
  <button class="vm-close" id="videoModalClose" type="button" aria-label="Cerrar">✕</button>
  <div class="vm-stage" id="videoModalStage">
    <div class="vm-empty" id="videoModalEmpty">
      <div class="ic">▶</div>
      <h4>Aún no has subido el video demo</h4>
      <p>Abre Tweaks → Vídeo demo (popup) y sube tu .mp4. Se reproducirá aquí en su proporción original.</p>
    </div>
    <video class="vm-video" id="videoModalVideo" playsinline controls preload="metadata" style="display:none"></video>
  </div>
</div>

<script>
/* ============= QR pattern generator ============= */
/* A deterministic pseudo-QR (NOT a real working QR — visual logo).
   33×33 grid, three finder squares at the corners, alignment in the middle,
   pseudo-random data cells from a hashed seed. Reused everywhere. */
(function(){
  function seedRand(seed){
    let s = seed;
    return function(){
      s = (s * 1664525 + 1013904223) >>> 0;
      return (s >>> 8) / 16777216;
    };
  }
  function buildData(seed){
    const N = 33;
    const grid = Array.from({length:N},()=>Array(N).fill(0));
    // mark finder zones as off-limits
    function blockFinder(x,y){ for(let i=0;i<8;i++)for(let j=0;j<8;j++) grid[y+i] && (grid[y+i][x+j]=2); }
    blockFinder(0,0);blockFinder(N-8,0);blockFinder(0,N-8);
    // center logo zone
    for(let i=-3;i<=3;i++)for(let j=-3;j<=3;j++){
      const y=Math.floor(N/2)+i, x=Math.floor(N/2)+j;
      if(grid[y]&&grid[y][x]!==undefined) grid[y][x]=2;
    }
    // random pill cells
    const rng=seedRand(seed);
    for(let y=0;y<N;y++) for(let x=0;x<N;x++){
      if(grid[y][x]===2) continue;
      if(rng()<0.48) grid[y][x]=1;
    }
    return grid;
  }
  // Render: vertical pills (merge stacked cells into single rounded shape)
  function gridToShapes(grid, opts={}){
    const N = grid.length;
    let out = '';
    // finder eyes — outer ring + red center dot at the 3 corners
    const eye = (cx,cy)=>{
      out += `<circle class="qr-eye" cx="${cx}" cy="${cy}" r="3.4" fill="none" stroke="currentColor" stroke-width="1.1"/>`;
      out += `<circle class="qr-eye-dot" cx="${cx}" cy="${cy}" r="1.5" fill="${opts.redDot||'var(--red)'}"/>`;
    };
    eye(3.5,3.5); eye(N-4.5,3.5); eye(3.5,N-4.5);
    // pills — merge consecutive vertical cells per column
    for(let x=0;x<N;x++){
      let run = 0;
      for(let y=0;y<=N;y++){
        const v = (y<N) ? grid[y][x]===1 : false;
        if(v) run++;
        else {
          if(run>0){
            const yTop = y-run;
            const cx = x + .5;
            const cyTop = yTop + .5;
            const len = run-1;
            const r = .42;
            // rounded pill of width 2r and height len + 2r
            out += `<rect class="qr-cell" x="${cx-r}" y="${cyTop-r}" width="${r*2}" height="${len + r*2}" rx="${r}" ry="${r}"/>`;
          }
          run=0;
        }
      }
    }
    return out;
  }
  const seeds={hero:991,brand:7,final:331,brandMini:42};
  const heroPat = gridToShapes(buildData(seeds.hero));
  const brandPat = gridToShapes(buildData(seeds.brandMini));
  const finalPat = gridToShapes(buildData(seeds.final));
  document.querySelectorAll('#qrStage .qr-cells').forEach(g=>g.innerHTML=heroPat);
  document.querySelectorAll('.final .qr-cells, .qrbig .qr-cells').forEach(g=>g.innerHTML=finalPat);
})();

/* ============= Hero toggle (removed — clean logo stage) ============= */

/* ============= Hero video — IndexedDB-backed persistence ============= */
(function(){
  const v = document.querySelector('.hero-video');
  if(!v) return;
  const placeholder = document.querySelector('.hero-video-placeholder');
  // Force the correct stacking regardless of any cached/stale CSS
  if(placeholder){
    placeholder.style.position = 'absolute';
    placeholder.style.inset = '0';
    placeholder.style.zIndex = '1';
    placeholder.style.transition = 'opacity .6s ease';
  }
  v.style.zIndex = '2';

  function showVideo(){
    v.classList.add('ready');
    if(placeholder) placeholder.style.opacity = '0';
  }
  function hideVideo(){
    v.classList.remove('ready');
    if(placeholder) placeholder.style.opacity = '1';
  }
  v.addEventListener('canplay', showVideo);
  v.addEventListener('loadeddata', showVideo);
  v.addEventListener('error', ()=>{ hideVideo(); }, true);

  // ----- IndexedDB store for the video Blob -----
  // Survives reloads, new tabs, and browser restarts (until cache is cleared).
  const DB_NAME = 'dt-hero';
  const STORE = 'videos';
  const KEY = 'hero';

  function openDB(){
    return new Promise((resolve, reject)=>{
      const req = indexedDB.open(DB_NAME, 1);
      req.onupgradeneeded = ()=>{
        const db = req.result;
        if(!db.objectStoreNames.contains(STORE)) db.createObjectStore(STORE);
      };
      req.onsuccess = ()=>resolve(req.result);
      req.onerror = ()=>reject(req.error);
    });
  }
  async function saveBlob(blob, name){
    const db = await openDB();
    return new Promise((resolve, reject)=>{
      const tx = db.transaction(STORE, 'readwrite');
      tx.objectStore(STORE).put({blob, name, ts: Date.now()}, KEY);
      tx.oncomplete = ()=>resolve();
      tx.onerror = ()=>reject(tx.error);
    });
  }
  async function loadBlob(){
    const db = await openDB();
    return new Promise((resolve)=>{
      const tx = db.transaction(STORE, 'readonly');
      const req = tx.objectStore(STORE).get(KEY);
      req.onsuccess = ()=>resolve(req.result || null);
      req.onerror = ()=>resolve(null);
    });
  }
  async function clearBlob(){
    const db = await openDB();
    return new Promise((resolve)=>{
      const tx = db.transaction(STORE, 'readwrite');
      tx.objectStore(STORE).delete(KEY);
      tx.oncomplete = ()=>resolve();
      tx.onerror = ()=>resolve();
    });
  }

  // Expose helpers
  window.__heroVideoShow = showVideo;
  window.__heroVideoHide = hideVideo;
  window.__heroVideoSave = async (file)=>{
    await saveBlob(file, file.name);
    const url = URL.createObjectURL(file);
    v.src = url;
    v.load();
    v.play().catch(()=>{});
    return file.name;
  };
  window.__heroVideoLoadStored = async ()=>{
    try{
      const rec = await loadBlob();
      if(rec && rec.blob){
        const url = URL.createObjectURL(rec.blob);
        v.src = url;
        v.load();
        v.play().catch(()=>{});
        return rec.name || 'video guardado';
      }
    }catch(_){}
    return null;
  };
  window.__heroVideoClear = async ()=>{
    await clearBlob();
    v.removeAttribute('src');
    v.load();
    hideVideo();
  };

  // ----- Generic IDB blob storage for other assets (demo video + destination images) -----
  async function saveBlobAt(key, blob, name){
    const db = await openDB();
    return new Promise((resolve, reject)=>{
      const tx = db.transaction(STORE, 'readwrite');
      tx.objectStore(STORE).put({blob, name, ts: Date.now()}, key);
      tx.oncomplete = ()=>resolve();
      tx.onerror = ()=>reject(tx.error);
    });
  }
  async function loadBlobAt(key){
    const db = await openDB();
    return new Promise((resolve)=>{
      const tx = db.transaction(STORE, 'readonly');
      const req = tx.objectStore(STORE).get(key);
      req.onsuccess = ()=>resolve(req.result || null);
      req.onerror = ()=>resolve(null);
    });
  }
  async function clearBlobAt(key){
    const db = await openDB();
    return new Promise((resolve)=>{
      const tx = db.transaction(STORE, 'readwrite');
      tx.objectStore(STORE).delete(key);
      tx.oncomplete = ()=>resolve();
      tx.onerror = ()=>resolve();
    });
  }
  window.__dtStore = { save: saveBlobAt, load: loadBlobAt, clear: clearBlobAt };

  // Auto-restore on load
  window.__heroVideoLoadStored().then(name=>{
    if(name) window.dispatchEvent(new CustomEvent('hero-video-restored', {detail:{name}}));
  });
})();

/* ============= Demo video popup ============= */
(function(){
  const modal = document.getElementById('videoModal');
  const stage = document.getElementById('videoModalStage');
  const empty = document.getElementById('videoModalEmpty');
  const video = document.getElementById('videoModalVideo');
  const openBtn = document.getElementById('openDemoVideo');
  const closeBtn = document.getElementById('videoModalClose');
  if(!modal || !video) return;

  let currentObjectUrl = null;
  let hasVideo = false;

  function setSrc(url, name){
    if(currentObjectUrl){ try{ URL.revokeObjectURL(currentObjectUrl); }catch(_){} currentObjectUrl = null; }
    if(url && url.startsWith('blob:')) currentObjectUrl = url;
    video.src = url;
    video.load();
    video.style.display = 'block';
    empty.style.display = 'none';
    hasVideo = true;
  }
  function clearSrc(){
    if(currentObjectUrl){ try{ URL.revokeObjectURL(currentObjectUrl); }catch(_){} currentObjectUrl = null; }
    try{ video.pause(); }catch(_){}
    video.removeAttribute('src');
    video.load();
    video.style.display = 'none';
    empty.style.display = 'flex';
    hasVideo = false;
  }

  function open(){
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    if(hasVideo){ video.currentTime = 0; video.play().catch(()=>{}); }
  }
  function close(){
    modal.classList.remove('open');
    document.body.style.overflow = '';
    try{ video.pause(); }catch(_){}
  }
  openBtn && openBtn.addEventListener('click', open);
  closeBtn && closeBtn.addEventListener('click', close);
  modal.addEventListener('click', (e)=>{ if(e.target === modal) close(); });
  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && modal.classList.contains('open')) close(); });

  window.__demoVideoSave = async (file)=>{
    if(window.__dtStore) await window.__dtStore.save('demo', file, file.name);
    const url = URL.createObjectURL(file);
    setSrc(url, file.name);
    return file.name;
  };
  window.__demoVideoLoadStored = async ()=>{
    try{
      if(!window.__dtStore) return null;
      const rec = await window.__dtStore.load('demo');
      if(rec && rec.blob){
        const url = URL.createObjectURL(rec.blob);
        setSrc(url, rec.name);
        return rec.name || 'video guardado';
      }
    }catch(_){}
    return null;
  };
  window.__demoVideoClear = async ()=>{
    if(window.__dtStore) await window.__dtStore.clear('demo');
    clearSrc();
  };

  // Auto-restore on load
  window.__demoVideoLoadStored().then(name=>{
    if(name) window.dispatchEvent(new CustomEvent('demo-video-restored', {detail:{name}}));
  });
})();

/* ============= Destination images: persist uploads via IDB ============= */
(function(){
  const KEYS = ['portfolio','instagram','video','profile'];
  const urlMap = {};
  function applyAll(){
    window.__destImages = Object.assign({}, urlMap);
    if(window.__refreshDestImage) window.__refreshDestImage();
  }
  async function restore(){
    if(!window.__dtStore) return {};
    const names = {};
    for(const k of KEYS){
      try{
        const rec = await window.__dtStore.load('destimg-'+k);
        if(rec && rec.blob){
          urlMap[k] = URL.createObjectURL(rec.blob);
          names[k] = rec.name || 'imagen';
        }
      }catch(_){}
    }
    applyAll();
    return names;
  }
  window.__destImageSave = async (key, file)=>{
    if(window.__dtStore) await window.__dtStore.save('destimg-'+key, file, file.name);
    if(urlMap[key]){ try{ URL.revokeObjectURL(urlMap[key]); }catch(_){} }
    urlMap[key] = URL.createObjectURL(file);
    applyAll();
    return file.name;
  };
  window.__destImageClear = async (key)=>{
    if(window.__dtStore) await window.__dtStore.clear('destimg-'+key);
    if(urlMap[key]){ try{ URL.revokeObjectURL(urlMap[key]); }catch(_){} delete urlMap[key]; }
    applyAll();
  };
  window.__destImageRestoreAll = restore;
  // Run on next tick to ensure dest activation has registered the refresh hook
  setTimeout(()=>{
    restore().then(names=>{
      if(Object.keys(names).length) window.dispatchEvent(new CustomEvent('dest-images-restored',{detail:{names}}));
    });
  }, 50);
})();

/* ============= Upload-your-own hero video ============= */
window.__pickHeroVideo = function(){
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'video/*';
  input.onchange = (e)=>{
    const file = e.target.files && e.target.files[0];
    if(!file) return;
    const url = URL.createObjectURL(file);
    const v = document.querySelector('.hero-video');
    if(v){
      v.src = url;
      v.load();
      v.play().catch(()=>{});
      try{ sessionStorage.setItem('dt_hero_video', url); }catch(_){}
    }
    // Visual confirmation toast
    const t = document.createElement('div');
    t.textContent = '✓  Video cargado · ' + file.name;
    t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#0c0c0c;color:#f7f1e3;padding:12px 20px;font-family:var(--mono);font-size:11px;letter-spacing:.18em;text-transform:uppercase;z-index:9999;border:1px solid #b3252c;box-shadow:0 12px 32px -8px rgba(0,0,0,.5)';
    document.body.appendChild(t);
    setTimeout(()=>t.remove(), 3200);
  };
  input.click();
};

/* ============= Nav: glass goes dark when over dark sections + scrolled state ============= */
(function(){
  const nav = document.getElementById('topNav');
  if(!nav) return;
  // Sections that should darken the nav: hero + any [data-theme="ink"|"char"|"blood"]
  const darkSel = '.hero, [data-theme="ink"], [data-theme="char"], [data-theme="blood"]';
  const darkSecs = Array.from(document.querySelectorAll(darkSel));
  function check(){
    const navMid = nav.getBoundingClientRect().bottom - 1;
    let onDark = false;
    for(const s of darkSecs){
      const r = s.getBoundingClientRect();
      if(r.top <= navMid && r.bottom > navMid){ onDark = true; break; }
    }
    nav.classList.toggle('on-dark', onDark);
    // Scrolled state — nav becomes glass once user has moved past ~24px
    nav.classList.toggle('scrolled', window.scrollY > 24);
  }
  check();
  window.addEventListener('scroll', check, {passive:true});
  window.addEventListener('resize', check);
})();

/* ============= Demo destinations -> phone frame ============= */
(function(){
  const dests = document.querySelectorAll('.dest');
  // Default stock-like images per destination (inline SVG so it works offline)
  const fallback = {
    portfolio:'data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#e8e1d2"/><stop offset="1" stop-color="#bbb1a0"/></linearGradient></defs><rect width="400" height="400" fill="url(#g)"/><rect x="40" y="60" width="320" height="22" rx="3" fill="#1a1a1a"/><rect x="40" y="96" width="180" height="10" rx="2" fill="#1a1a1a" opacity=".7"/><rect x="40" y="130" width="150" height="150" fill="#1a1a1a"/><rect x="210" y="130" width="150" height="150" fill="#b3252c"/><rect x="40" y="300" width="320" height="8" rx="2" fill="#1a1a1a" opacity=".5"/><rect x="40" y="318" width="260" height="8" rx="2" fill="#1a1a1a" opacity=".4"/><rect x="40" y="336" width="220" height="8" rx="2" fill="#1a1a1a" opacity=".3"/></svg>'),
    instagram:'data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><defs><linearGradient id="ig" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#feda77"/><stop offset=".5" stop-color="#f58529"/><stop offset="1" stop-color="#dd2a7b"/></linearGradient></defs><rect width="400" height="400" fill="url(#ig)"/><rect x="100" y="100" width="200" height="200" fill="none" stroke="#fff" stroke-width="16" rx="50"/><circle cx="200" cy="200" r="50" fill="none" stroke="#fff" stroke-width="16"/><circle cx="275" cy="125" r="12" fill="#fff"/></svg>'),
    video:'data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><defs><radialGradient id="v" cx="50%" cy="40%"><stop offset="0" stop-color="#3a1f1f"/><stop offset="1" stop-color="#080404"/></radialGradient></defs><rect width="400" height="400" fill="url(#v)"/><circle cx="200" cy="200" r="68" fill="#b3252c"/><polygon points="180,168 180,232 240,200" fill="#fff"/><rect x="40" y="340" width="320" height="4" fill="#fff" opacity=".25"/><rect x="40" y="340" width="120" height="4" fill="#b3252c"/></svg>'),
    profile:'data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><rect width="400" height="400" fill="#141414"/><circle cx="200" cy="86" r="40" fill="#b3252c"/><rect x="60" y="160" width="95" height="95" fill="#e8e1d2"/><rect x="160" y="160" width="95" height="95" fill="#999999"/><rect x="260" y="160" width="95" height="95" fill="#b3252c"/><rect x="60" y="260" width="95" height="95" fill="#b3252c" opacity=".5"/><rect x="160" y="260" width="95" height="95" fill="#e8e1d2" opacity=".6"/><rect x="260" y="260" width="95" height="95" fill="#444444"/></svg>'),
  };
  window.__destImages = window.__destImages || {};
  function imgFor(key){
    return (window.__destImages && window.__destImages[key]) || fallback[key];
  }
  const map = {
    portfolio:{title:'Tu web <em>profesional</em>', icon:'¶', url:'portfolio.miestudio.com', label:'DESTINO ACTUAL · WEB'},
    instagram:{title:'Tu red social <em>favorita</em>', icon:'@', url:'instagram.com/aria.ink', label:'DESTINO ACTUAL · RED SOCIAL'},
    video:{title:'Un video <em>privado</em>', icon:'▶', url:'dyn.tt/u/aria/baile-2026', label:'DESTINO ACTUAL · VIDEO'},
    profile:{title:'Tu página <em>Dynamic</em>', icon:'❍', url:'dyn.tt/aria', label:'DESTINO ACTUAL · PERFIL'},
  };
  const frameImg = document.getElementById('frameImg');
  const frameMedia = document.getElementById('frameMedia');
  const frameIcon = document.getElementById('frameIcon');

  function applyImage(key){
    const src = imgFor(key);
    if(!frameImg || !frameMedia) return;
    if(src){
      frameImg.onload = ()=>{ frameMedia.classList.remove('no-img'); if(frameIcon) frameIcon.style.opacity = '0'; };
      frameImg.onerror = ()=>{ frameImg.style.display='none'; frameMedia.classList.add('no-img'); if(frameIcon) frameIcon.style.opacity = '.95'; };
      frameImg.style.display = 'block';
      frameImg.src = src;
    } else {
      frameImg.removeAttribute('src');
      frameImg.style.display = 'none';
      frameMedia.classList.add('no-img');
      if(frameIcon) frameIcon.style.opacity = '.95';
    }
  }

  let currentKey = 'portfolio';
  function activate(key){
    currentKey = key;
    dests.forEach(x=>x.classList.toggle('active', x.dataset.key===key));
    const m = map[key];
    if(!m) return;
    document.getElementById('frameTitle').innerHTML = m.title;
    document.getElementById('frameIcon').textContent = m.icon;
    document.getElementById('frameUrl').textContent = m.url;
    document.getElementById('frameLabel').textContent = m.label;
    applyImage(key);
  }
  window.__activateDest = activate;
  window.__refreshDestImage = ()=>applyImage(currentKey);
  dests.forEach(d=>{
    d.addEventListener('click',()=>activate(d.dataset.key));
  });
  activate('portfolio');
})();

/* ============= Body zones — interactive map with details ============= */
(function(){
  const zoneData = {
    forearm:{name:'Antebrazo interno', status:'optimal', statusLabel:'Óptimo', curv:'Plana', move:'Bajo', heal:'3-4 sem.', vis:'Alta', ink:'≥ 1.2 mm', note:'Una de las zonas más usadas para QR dinámico. Piel firme, lectura perfecta a cualquier ángulo.'},
    biceps:{name:'Bíceps exterior', status:'optimal', statusLabel:'Óptimo', curv:'Leve', move:'Bajo', heal:'3-4 sem.', vis:'Media', ink:'≥ 1.2 mm', note:'Superficie amplia, contraste sólido. Ideal para QR de 4×4 cm.'},
    calf:{name:'Pantorrilla', status:'optimal', statusLabel:'Óptimo', curv:'Leve', move:'Bajo', heal:'4-5 sem.', vis:'Media', ink:'≥ 1.2 mm', note:'Zona estable con buen retorno cromático. Recomendado en parte exterior.'},
    chest:{name:'Pectoral', status:'good', statusLabel:'Bueno', curv:'Leve', move:'Medio', heal:'4-5 sem.', vis:'Privada', ink:'≥ 1.3 mm', note:'Visibilidad bajo demanda. Requiere placado en zona alta para evitar distorsión.'},
    shoulder:{name:'Hombro · escápula', status:'optimal', statusLabel:'Óptimo', curv:'Leve', move:'Bajo', heal:'3-4 sem.', vis:'Alta', ink:'≥ 1.2 mm', note:'Superficie plana sobre el omóplato. Excelente para escaneo lateral.'},
    back:{name:'Espalda baja', status:'optimal', statusLabel:'Óptimo', curv:'Plana', move:'Bajo', heal:'4 sem.', vis:'Privada', ink:'≥ 1.2 mm', note:'Lienzo amplio y estable. Ideal si buscas un QR discreto pero perfecto.'},
    wrist:{name:'Muñeca', status:'warn', statusLabel:'Supervisar', curv:'Alta', move:'Alto', heal:'5-6 sem.', vis:'Alta', ink:'≥ 1.4 mm', note:'La curvatura puede distorsionar la lectura. Aceptable solo si se valida con plantilla previa.'},
    ribs:{name:'Costillas', status:'warn', statusLabel:'Supervisar', curv:'Media', move:'Alto', heal:'5-6 sem.', vis:'Privada', ink:'≥ 1.4 mm', note:'Piel fina y mucho movimiento respiratorio. Recomendado solo a tatuadores experimentados.'},
  };

  const detailEls = {
    name: document.getElementById('bzName'),
    status: document.getElementById('bzStatus'),
    curv: document.getElementById('bzCurv'),
    move: document.getElementById('bzMove'),
    heal: document.getElementById('bzHeal'),
    vis: document.getElementById('bzVis'),
    ink: document.getElementById('bzInk'),
    note: document.getElementById('bzNote'),
  };
  const crosshair = document.getElementById('zoneCrosshair');
  const bodyMap = document.getElementById('bodyMap');

  function updateDetail(zone){
    const d = zoneData[zone];
    if(!d || !detailEls.name) return;
    detailEls.name.textContent = d.name;
    detailEls.status.textContent = d.statusLabel;
    detailEls.status.className = 'bz-detail-status ' + d.status;
    detailEls.curv.textContent = d.curv;
    detailEls.move.textContent = d.move;
    detailEls.heal.textContent = d.heal;
    detailEls.vis.textContent = d.vis;
    detailEls.ink.textContent = d.ink;
    detailEls.note.textContent = d.note;
  }

  function moveCrosshair(zone){
    if(!crosshair || !bodyMap) return;
    // Find first visible dot for this zone
    const dot = bodyMap.querySelector(`.zone-dot[data-zone="${zone}"]:not(.hidden)`);
    if(!dot){ crosshair.classList.remove('show'); return; }
    crosshair.style.left = dot.style.left;
    crosshair.style.top = dot.style.top;
    crosshair.style.color = dot.classList.contains('warn') ? '#b3252c' : '#1a1a1a';
    crosshair.classList.add('show');
  }

  function activate(zone){
    document.querySelectorAll('.zone-li').forEach(i=>i.classList.toggle('active', i.dataset.zone===zone));
    document.querySelectorAll('.zone-dot').forEach(d=>d.classList.toggle('active', d.dataset.zone===zone));
    updateDetail(zone);
    moveCrosshair(zone);
  }

  document.querySelectorAll('.zone-li').forEach(i=>i.addEventListener('click', ()=>{
    // Auto-switch view if zone not visible in current view
    const dot = document.querySelector(`.zone-dot[data-zone="${i.dataset.zone}"]:not(.hidden)`);
    if(!dot){
      const anyDot = document.querySelector(`.zone-dot[data-zone="${i.dataset.zone}"]`);
      if(anyDot){
        const targetView = anyDot.dataset.view;
        switchView(targetView);
      }
    }
    activate(i.dataset.zone);
  }));
  document.querySelectorAll('.zone-dot').forEach(d=>d.addEventListener('click', ()=>activate(d.dataset.zone)));

  // Front/back toggle
  function switchView(view){
    document.querySelectorAll('.bz-toggle button').forEach(b=>b.classList.toggle('on', b.dataset.view===view));
    document.querySelectorAll('.silhouette').forEach(s=>s.classList.toggle('hidden', !s.classList.contains(view+'-view')));
    document.querySelectorAll('.zone-dot').forEach(d=>d.classList.toggle('hidden', d.dataset.view !== view));
    // Refresh crosshair for current active zone
    const active = document.querySelector('.zone-li.active');
    if(active) moveCrosshair(active.dataset.zone);
  }
  document.querySelectorAll('.bz-toggle button').forEach(b=>b.addEventListener('click', ()=>switchView(b.dataset.view)));

  // Initial state
  activate('forearm');
})();

/* ============= FAQ accordion ============= */
(function(){
  const qs = document.querySelectorAll('.q');
  qs.forEach(q=>{
    q.addEventListener('click',()=>{
      const wasOpen = q.classList.contains('open');
      qs.forEach(x=>x.classList.remove('open'));
      if(!wasOpen) q.classList.add('open');
    });
  });
})();

/* ============= Reveal on scroll ============= */
(function(){
  const els = document.querySelectorAll('.hero-grid > div, .sec-head, .steps-grid, .specs-grid, .plans, .faq-grid, .final-row');
  els.forEach(e=>e.classList.add('reveal'));
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
  },{threshold:.12});
  els.forEach(e=>io.observe(e));
})();

/* ============= Smooth scroll for anchors ============= */
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click',(e)=>{
    const id=a.getAttribute('href').slice(1);
    const el=document.getElementById(id);
    if(el){ e.preventDefault(); window.scrollTo({top:el.getBoundingClientRect().top+window.scrollY-60,behavior:'smooth'}); }
  });
});
</script>

<!-- Tweaks -->
<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>
<script type="text/babel" src="tweaks-panel.jsx"></script>
@verbatim
<script type="text/babel">
const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "accent": "#b3252c",
  "theme": "paper",
  "marqueeOn": true,
  "grainOn": true,
  "heroOverlay": 0.8,
  "heroOverlayColor": "#0a0707"
}/*EDITMODE-END*/;

function App(){
  const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);
  const [videoName, setVideoName] = React.useState(null);
  const [demoVideoName, setDemoVideoName] = React.useState(null);
  const [destNames, setDestNames] = React.useState({});

  // Restore name on mount (IndexedDB → video)
  React.useEffect(()=>{
    const onRestored = (e)=>setVideoName(e.detail?.name || 'video guardado');
    const onDemoRestored = (e)=>setDemoVideoName(e.detail?.name || 'video guardado');
    const onDestRestored = (e)=>setDestNames(e.detail?.names || {});
    window.addEventListener('hero-video-restored', onRestored);
    window.addEventListener('demo-video-restored', onDemoRestored);
    window.addEventListener('dest-images-restored', onDestRestored);
    return ()=>{
      window.removeEventListener('hero-video-restored', onRestored);
      window.removeEventListener('demo-video-restored', onDemoRestored);
      window.removeEventListener('dest-images-restored', onDestRestored);
    };
  },[]);

  React.useEffect(()=>{
    document.documentElement.style.setProperty('--red', t.accent);
    document.documentElement.style.setProperty('--red-deep', t.accent);
    document.body.classList.toggle('dark', t.theme==='dark');
    document.querySelector('.marquee').style.display = t.marqueeOn ? '' : 'none';
    document.body.style.setProperty('--grain', t.grainOn?'.35':'0');
    document.documentElement.style.setProperty('--hero-overlay', t.heroOverlay);
    document.documentElement.style.setProperty('--hero-overlay-color', t.heroOverlayColor);
    const grainStyle = document.getElementById('grainStyle') || (()=>{
      const s=document.createElement('style');s.id='grainStyle';document.head.appendChild(s);return s;
    })();
    grainStyle.textContent = `body::before{opacity:${t.grainOn?.35:0} !important}`;
  },[t]);

  const pickVideo = ()=>{
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'video/*';
    input.onchange = async (e)=>{
      const file = e.target.files && e.target.files[0];
      if(!file) return;
      setVideoName('Guardando…');
      try{
        const name = await window.__heroVideoSave(file);
        setVideoName(name);
        if(t.heroOverlay >= 0.95){ setTweak('heroOverlay', 0.5); }
      }catch(err){
        setVideoName('✗ Error al guardar');
      }
    };
    input.click();
  };

  const removeVideo = async ()=>{
    await window.__heroVideoClear();
    setVideoName(null);
  };

  const pickDemoVideo = ()=>{
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'video/*';
    input.onchange = async (e)=>{
      const file = e.target.files && e.target.files[0];
      if(!file) return;
      setDemoVideoName('Guardando…');
      try{
        const name = await window.__demoVideoSave(file);
        setDemoVideoName(name);
      }catch(err){
        setDemoVideoName('✗ Error al guardar');
      }
    };
    input.click();
  };
  const removeDemoVideo = async ()=>{
    await window.__demoVideoClear();
    setDemoVideoName(null);
  };

  const pickDestImage = (key)=>{
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = async (e)=>{
      const file = e.target.files && e.target.files[0];
      if(!file) return;
      try{
        const name = await window.__destImageSave(key, file);
        setDestNames(n=>({...n,[key]:name}));
        if(window.__activateDest){
          // Switch active to the destination we just set, so the user sees the change
          window.__activateDest(key);
        }
      }catch(err){ /* ignore */ }
    };
    input.click();
  };
  const clearDestImage = async (key)=>{
    await window.__destImageClear(key);
    setDestNames(n=>{ const c={...n}; delete c[key]; return c; });
  };

  const shortName = (s)=> s && s.length>22 ? s.slice(0,22)+'…' : s;

  const destLabels = {
    portfolio:'Web profesional',
    instagram:'Red social',
    video:'Video privado',
    profile:'Página Dynamic',
  };

  return (
    <TweaksPanel title="Tweaks">
      <TweakSection title="Video del hero (fondo)">
        <TweakButton label={videoName ? `Reemplazar (${shortName(videoName)})` : 'Subir video de fondo'} onClick={pickVideo} />
        {videoName && <TweakButton label="Quitar video" onClick={removeVideo} />}
        <div style={{fontFamily:'JetBrains Mono,monospace',fontSize:10,opacity:.6,lineHeight:1.5,padding:'4px 0'}}>
          MP4 / WebM · se guarda en este navegador.
        </div>
      </TweakSection>

      <TweakSection title="Video demo (popup)">
        <TweakButton label={demoVideoName ? `Reemplazar (${shortName(demoVideoName)})` : 'Subir video demo'} onClick={pickDemoVideo} />
        {demoVideoName && <TweakButton label="Quitar video demo" onClick={removeDemoVideo} />}
        <div style={{fontFamily:'JetBrains Mono,monospace',fontSize:10,opacity:.6,lineHeight:1.5,padding:'4px 0'}}>
          Se reproduce al pulsar “Ver video”. Mantiene su proporción original.
        </div>
      </TweakSection>

      <TweakSection title="Imágenes del mockup (sección 01)">
        {Object.keys(destLabels).map(key=>(
          <div key={key} style={{display:'flex',flexDirection:'column',gap:6,padding:'6px 0',borderBottom:'1px dashed rgba(127,127,127,.18)'}}>
            <div style={{fontFamily:'JetBrains Mono,monospace',fontSize:10,letterSpacing:'.14em',textTransform:'uppercase',opacity:.7}}>{destLabels[key]}</div>
            <div style={{display:'flex',gap:6,flexWrap:'wrap'}}>
              <TweakButton label={destNames[key] ? `Reemplazar` : 'Subir imagen'} onClick={()=>pickDestImage(key)} />
              {destNames[key] && <TweakButton label="Quitar" onClick={()=>clearDestImage(key)} />}
            </div>
            {destNames[key] && <div style={{fontFamily:'JetBrains Mono,monospace',fontSize:9,opacity:.5}}>{shortName(destNames[key])}</div>}
          </div>
        ))}
      </TweakSection>

      <TweakSection title="Capa sobre el video">
        <TweakColor label="Color" value={t.heroOverlayColor} onChange={v=>setTweak('heroOverlayColor',v)}
          options={['#0a0707','#000000','#1a0a0a','#3a0d10','#0c1620','#b3252c']} />
        <TweakSlider label="Opacidad" min={0} max={0.95} step={0.02}
          value={t.heroOverlay} onChange={v=>setTweak('heroOverlay',v)} />
      </TweakSection>

      <TweakSection title="Color & tono">
        <TweakColor label="Acento" value={t.accent} onChange={v=>setTweak('accent',v)}
          options={['#b3252c','#8a1c22','#d63c44','#1a1a1a','#4a4a4a']} />
        <TweakRadio label="Tema base" value={t.theme} onChange={v=>setTweak('theme',v)}
          options={[{value:'paper',label:'Papel'},{value:'dark',label:'Oscuro'}]} />
      </TweakSection>
      <TweakSection title="Detalles">
        <TweakToggle label="Marquee superior" value={t.marqueeOn} onChange={v=>setTweak('marqueeOn',v)} />
        <TweakToggle label="Grano de papel" value={t.grainOn} onChange={v=>setTweak('grainOn',v)} />
      </TweakSection>
    </TweaksPanel>
  );
}

const root = ReactDOM.createRoot(document.getElementById('tweaks-root'));
root.render(<App />);
</script>
@endverbatim

</body>
</html>
