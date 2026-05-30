<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="robots" content="noindex, nofollow" />
<title>Mi cuenta — Dynamic Tattoos</title>
<link rel="icon" type="image/png" href="{{ asset('images/designer/dynamic-tattoos-icon.png') }}" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">

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
    background:var(--paper);
    color:var(--ink);
    font-family:var(--sans);
    font-size:16px;
    line-height:1.5;
    -webkit-font-smoothing:antialiased;
    overflow-x:hidden;
    padding-top:72px;
  }
  /* Grain overlay (matches landing) */
  body::before{
    content:"";
    position:fixed;inset:0;
    pointer-events:none;
    z-index:1000;
    opacity:.28;
    mix-blend-mode:multiply;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='240'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 .35 0'/></filter><rect width='100%25' height='100%25' filter='url(%23n)'/></svg>");
  }

  .mono{font-family:var(--mono);font-weight:400}
  .upper{text-transform:uppercase;letter-spacing:.14em}
  .display{font-family:var(--display);font-weight:800;letter-spacing:-.02em}
  .ruby{color:var(--red)}

  .wrap{max-width:1360px;margin:0 auto;padding:0 40px;position:relative}
  @media (max-width:720px){.wrap{padding:0 22px}}

  hr.rule{border:0;border-top:1px solid var(--rule);margin:0}
  hr.thin{border:0;border-top:1px solid rgba(26,26,26,.14);margin:0}

  /* ============= NAV (clone from landing) ============= */
  nav.top{
    position:fixed;top:0;left:0;right:0;z-index:50;
    background:#ffffff;
    border-bottom:1px solid rgba(26,26,26,.12);
    transition:background-color .35s, border-color .35s, color .35s, backdrop-filter .35s;
  }
  nav.top.scrolled{
    background:rgba(255,255,255,.7);
    backdrop-filter:blur(18px) saturate(160%);
    -webkit-backdrop-filter:blur(18px) saturate(160%);
  }
  nav.top .row{
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 0;gap:24px;
  }
  .brand{display:flex;align-items:center;gap:0;text-decoration:none;color:inherit}
  .brand img{height:46px;width:auto;display:block}
  nav ul{list-style:none;display:flex;gap:28px;margin:0;padding:0}
  nav ul a{
    color:#1a1a1a;text-decoration:none;font-size:13px;
    text-transform:uppercase;letter-spacing:.12em;
    position:relative;padding:6px 0;
    transition:color .2s;
  }
  nav ul a:hover{color:var(--red)}
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
  nav .cta:hover{background:#fff;color:#0c0c0c !important;border-color:#1a1a1a}
  nav .cta::after{content:"→";font-weight:400;transition:transform .15s}
  nav .cta:hover::after{transform:translateX(3px)}
  @media (max-width:880px){nav ul{display:none}}

  /* ============= Marquee (clone) ============= */
  .marquee{
    background:var(--ink);color:#f5f5f5;
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

  /* ============= Topbar (lang/currency/logout) ============= */
  .topbar{
    background:var(--paper-2);
    border-bottom:1px solid rgba(26,26,26,.10);
  }
  .topbar .row{
    display:flex;align-items:center;justify-content:flex-end;gap:.5rem;
    padding:10px 0;flex-wrap:wrap;
  }
  .topbar-btn{
    display:inline-flex;align-items:center;gap:.45rem;
    background:#fff;border:1px solid rgba(26,26,26,.18);
    padding:7px 12px;cursor:pointer;
    font-family:var(--mono);font-size:11px;color:var(--ink);
    text-transform:uppercase;letter-spacing:.10em;
    transition:border-color .2s, background .2s, color .2s;
  }
  .topbar-btn:hover{border-color:var(--ink);background:var(--ink);color:#fff}
  .topbar-btn .flag{font-size:14px;line-height:1}
  .popover{
    position:absolute;top:calc(100% + 6px);right:0;z-index:30;
    min-width:220px;background:#fff;
    border:1px solid var(--ink);
    box-shadow:0 18px 40px -16px rgba(0,0,0,.25);
    padding:6px;
  }
  .popover-item{
    display:flex;align-items:center;gap:.6rem;
    padding:.6rem .75rem;cursor:pointer;
    font-size:.85rem;color:var(--ink);
    transition:background .15s, color .15s;
  }
  .popover-item:hover{background:var(--paper-2)}
  .popover-item.active{background:var(--ink);color:#fff}
  .popover-item.active svg{color:#fff}
  .logout-btn{
    display:inline-flex;align-items:center;gap:.5rem;
    background:#fff;color:var(--red);
    border:1px solid var(--red);
    padding:7px 14px;cursor:pointer;
    font-family:var(--mono);font-size:11px;
    text-transform:uppercase;letter-spacing:.12em;
    transition:.2s;
  }
  .logout-btn:hover{background:var(--red);color:#fff}

  /* ============= Account hero ============= */
  .acct-hero{
    background:var(--ink);
    color:#f1ebd9;
    position:relative;
    overflow:hidden;
  }
  .acct-hero::after{
    content:"";position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(900px 360px at 88% -10%, rgba(179,37,44,.45), transparent 60%);
  }
  .acct-hero .wrap{padding-top:60px;padding-bottom:60px;position:relative;z-index:1}
  .acct-eyebrow{
    font-family:var(--mono);font-size:11px;
    text-transform:uppercase;letter-spacing:.22em;
    color:rgba(241,235,217,.6);
    display:inline-flex;align-items:center;gap:14px;margin-bottom:24px;
  }
  .acct-eyebrow::before{content:"";width:48px;height:1px;background:var(--red)}
  .acct-hero-grid{
    display:grid;grid-template-columns:auto 1fr auto;gap:32px;align-items:center;
    flex-wrap:wrap;
  }
  @media (max-width:880px){
    .acct-hero-grid{grid-template-columns:1fr;gap:24px}
  }
  .avatar-wrap{position:relative;width:120px;height:120px;flex-shrink:0}
  .avatar{
    width:120px;height:120px;display:grid;place-items:center;
    background:linear-gradient(135deg,#b3252c,#7a0000);
    color:#fff;font-family:var(--display);font-weight:800;font-size:42px;letter-spacing:-.02em;
    border:1px solid rgba(241,235,217,.18);
    box-shadow:0 20px 40px -16px rgba(179,37,44,.55);
    overflow:hidden;
  }
  .avatar img{width:100%;height:100%;object-fit:cover}
  .avatar-edit{
    position:absolute;right:-6px;bottom:-6px;
    width:36px;height:36px;
    background:#f1ebd9;color:var(--ink);
    border:1px solid var(--ink);
    display:grid;place-items:center;cursor:pointer;
    transition:.2s;
  }
  .avatar-edit:hover{background:var(--red);color:#fff;border-color:var(--red)}
  .acct-name{
    font-family:var(--display);font-weight:800;
    font-size:clamp(34px, 4.5vw, 52px);
    line-height:1;letter-spacing:-.02em;color:#f1ebd9;
    margin:0;
    text-wrap:pretty;
  }
  .acct-meta{
    display:flex;flex-wrap:wrap;gap:8px 24px;margin-top:14px;
    font-family:var(--mono);font-size:11px;
    color:rgba(241,235,217,.7);
    text-transform:uppercase;letter-spacing:.12em;
  }
  .acct-meta span{display:inline-flex;align-items:center;gap:.45rem}
  .acct-meta svg{width:13px;height:13px;opacity:.8}
  .badge-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
  .badge{
    display:inline-flex;align-items:center;gap:.35rem;
    font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.14em;
    padding:5px 10px;border:1px solid currentColor;
    background:transparent;
  }
  .badge.solid{background:currentColor}
  .badge.solid span{color:#fff}
  .badge-ok{color:var(--ok)}
  .badge-warn{color:var(--warn)}
  .badge-info{color:var(--ink)}
  .badge-red{color:var(--red)}
  .badge-mute{color:var(--ash)}
  .badge-hero-info{color:#f1ebd9;border-color:rgba(241,235,217,.5)}
  .badge-hero-ok{color:#9be3a8;border-color:#9be3a8}

  .acct-actions{display:flex;flex-wrap:wrap;gap:10px;justify-content:flex-end}
  @media (max-width:880px){.acct-actions{justify-content:flex-start}}

  /* ============= Buttons ============= */
  .btn{
    display:inline-flex;align-items:center;gap:8px;
    font-family:var(--mono);font-size:11px;font-weight:500;
    text-transform:uppercase;letter-spacing:.14em;
    padding:12px 20px;cursor:pointer;border:1px solid;text-decoration:none;
    transition:background .2s ease, color .2s ease, border-color .2s ease, transform .15s;
  }
  .btn svg{width:14px;height:14px}
  .btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
  .btn-primary:hover{background:#fff;color:var(--ink);border-color:var(--ink)}
  .btn-secondary{background:transparent;color:var(--ink);border-color:var(--ink)}
  .btn-secondary:hover{background:var(--ink);color:#fff}
  .btn-ghost{background:transparent;color:#f1ebd9;border-color:rgba(241,235,217,.4)}
  .btn-ghost:hover{background:#f1ebd9;color:var(--ink);border-color:#f1ebd9}
  .btn-danger{background:var(--red);color:#fff;border-color:var(--red)}
  .btn-danger:hover{background:var(--red-deep);border-color:var(--red-deep)}
  .btn-danger-outline{background:transparent;color:var(--red);border-color:var(--red)}
  .btn-danger-outline:hover{background:var(--red);color:#fff}
  .btn-sm{padding:8px 14px;font-size:10px}
  .btn-xs{padding:6px 10px;font-size:10px;letter-spacing:.10em}

  /* ============= Stats row in hero ============= */
  .stats-row{
    display:grid;grid-template-columns:repeat(4, 1fr);gap:0;
    margin-top:48px;
    border-top:1px solid rgba(241,235,217,.18);
    border-bottom:1px solid rgba(241,235,217,.18);
  }
  @media (max-width:880px){.stats-row{grid-template-columns:repeat(2, 1fr)}}
  .stat{
    padding:24px 22px;border-right:1px solid rgba(241,235,217,.18);
  }
  .stats-row .stat:last-child{border-right:0}
  @media (max-width:880px){
    .stat:nth-child(2n){border-right:0}
    .stat:nth-child(1),.stat:nth-child(2){border-bottom:1px solid rgba(241,235,217,.18)}
  }
  .stat-label{
    font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.18em;
    color:rgba(241,235,217,.6);margin:0 0 8px;
  }
  .stat-value{
    font-family:var(--display);font-weight:700;font-size:34px;
    color:#f1ebd9;margin:0;line-height:1;letter-spacing:-.01em;
  }
  .stat-sub{
    font-family:var(--mono);font-size:11px;color:rgba(241,235,217,.55);
    margin-top:8px;
  }
  .stat-track{
    width:100%;height:3px;background:rgba(241,235,217,.15);
    margin-top:14px;overflow:hidden;
  }
  .stat-bar{height:100%;background:var(--red);transition:width .6s ease}
  .stat-link{
    margin-top:10px;background:none;border:0;padding:0;cursor:pointer;
    font-family:var(--mono);font-size:10px;color:var(--red);
    text-transform:uppercase;letter-spacing:.14em;
    text-decoration:underline;text-underline-offset:3px;
    text-decoration-color:rgba(179,37,44,.4);
  }
  .stat-link:hover{text-decoration-color:var(--red)}

  /* ============= Section main ============= */
  main.acct-main{padding:64px 0 120px}

  /* ============= Tab nav ============= */
  .tabs-wrap{
    border-top:1px solid var(--ink);
    border-bottom:1px solid var(--ink);
    background:var(--paper);
    margin-bottom:48px;
    position:sticky;top:72px;z-index:30;
  }
  .tabs-bar{
    display:flex;flex-wrap:nowrap;gap:0;
    overflow:hidden;
  }
  .tab-btn{
    background:transparent;border:0;border-right:1px solid rgba(26,26,26,.12);
    padding:18px 20px;cursor:pointer;text-decoration:none;
    display:inline-flex;align-items:center;gap:10px;
    font-family:var(--mono);font-size:11px;font-weight:500;color:var(--ash);
    text-transform:uppercase;letter-spacing:.14em;
    transition:color .2s, background .2s;
    position:relative;flex-shrink:0;
  }
  .tab-btn:last-child{border-right:0}
  .tab-btn svg{width:14px;height:14px}
  .tab-btn:hover{color:var(--ink);background:var(--paper-2)}
  .tab-btn.active{color:var(--ink);background:var(--paper-2)}
  .tab-btn.active::after{
    content:"";position:absolute;left:0;right:0;bottom:-1px;height:3px;background:var(--red);
  }
  .tab-btn.danger{color:var(--red)}
  .tab-btn.danger.active{color:var(--red)}
  .tab-btn.danger.active::after{background:var(--red)}
  .tab-btn.studio{color:var(--ink);background:var(--ink);color:#fff !important}
  .tab-btn.studio:hover{background:var(--red);color:#fff !important}
  .tab-btn .premium-tag{
    margin-left:6px;font-family:var(--mono);font-size:9px;
    color:#fff;background:var(--red);padding:2px 6px;
    letter-spacing:.14em;
  }
  .tab-btn .ext-arrow{margin-left:6px;opacity:.5}
  /* Mobile toggle hidden by default, surfaces under 720 */
  .nav-mobile-toggle{display:none}
  @media (max-width:720px){
    .tabs-wrap{position:relative;top:auto}
    .tabs-bar{display:none;flex-direction:column;border-top:1px solid rgba(26,26,26,.12)}
    .tabs-bar.is-open{display:flex}
    .tab-btn{border-right:0;border-bottom:1px solid rgba(26,26,26,.10);justify-content:flex-start}
    .nav-mobile-toggle{
      display:flex;align-items:center;justify-content:space-between;width:100%;
      padding:18px 22px;background:var(--paper);border:0;cursor:pointer;
      font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;
    }
    .nav-mobile-current{display:inline-flex;align-items:center;gap:10px}
    .nav-mobile-burger{width:18px;height:14px;position:relative;display:inline-block}
    .nav-mobile-burger span{position:absolute;left:0;right:0;height:2px;background:var(--ink);transition:.2s}
    .nav-mobile-burger span:nth-child(1){top:0}
    .nav-mobile-burger span:nth-child(2){top:6px}
    .nav-mobile-burger span:nth-child(3){top:12px}
    .nav-mobile-burger.is-open span:nth-child(1){top:6px;transform:rotate(45deg)}
    .nav-mobile-burger.is-open span:nth-child(2){opacity:0}
    .nav-mobile-burger.is-open span:nth-child(3){top:6px;transform:rotate(-45deg)}
  }

  /* ============= Cards ============= */
  .card{
    background:#fff;
    border:1px solid var(--ink);
    padding:36px;
    position:relative;
  }
  @media (max-width:720px){.card{padding:24px}}
  .card + .card{margin-top:24px}
  .card-head{
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:24px;flex-wrap:wrap;margin-bottom:28px;
  }
  .card-head .title-block{flex:1 1 280px;min-width:0}
  .card-eyebrow{
    font-family:var(--mono);font-size:11px;
    text-transform:uppercase;letter-spacing:.18em;
    color:var(--red);
    display:inline-flex;align-items:center;gap:10px;margin-bottom:10px;
  }
  .card-eyebrow::before{content:"";width:28px;height:1px;background:var(--red)}
  .card h2{
    font-family:var(--display);font-weight:700;
    font-size:30px;line-height:1.05;letter-spacing:-.02em;
    margin:0 0 8px;color:var(--ink);
  }
  .card h3{
    font-family:var(--display);font-weight:700;
    font-size:20px;letter-spacing:-.01em;color:var(--ink);
    margin:0 0 6px;
  }
  .card-sub{
    font-size:14px;color:var(--ash);margin:0;max-width:62ch;
    text-wrap:pretty;
  }

  /* ============= Form fields ============= */
  .form-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:24px;
  }
  @media (max-width:680px){.form-grid{grid-template-columns:1fr}}
  .field{display:flex;flex-direction:column;gap:8px}
  .field.full{grid-column:1 / -1}
  .label{
    font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.16em;
    color:var(--ink);font-weight:500;
  }
  .input{
    background:transparent;
    border:0;border-bottom:1px solid var(--ink);
    padding:10px 0 10px;
    font-family:var(--sans);font-size:15px;color:var(--ink);
    outline:none;width:100%;
    transition:border-color .2s, background .2s;
  }
  .input:focus{border-bottom-color:var(--red)}
  .input::placeholder{color:var(--ash-2)}
  select.input{
    appearance:none;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path fill='none' stroke='%231a1a1a' stroke-width='1.5' d='M1 1l4 4 4-4'/></svg>");
    background-repeat:no-repeat;
    background-position:right 0 center;
    padding-right:18px;
  }
  .input-wrap{position:relative}
  .input-wrap .right-badge{
    position:absolute;right:0;top:50%;transform:translateY(-50%);
  }
  .phone-row{display:flex;gap:8px;align-items:flex-end;margin-bottom:8px}
  .phone-row .input{flex:1}
  .phone-row select.input{flex:0 0 110px;padding-right:18px}
  .add-link{
    margin-top:8px;background:none;border:0;padding:0;cursor:pointer;
    font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;
    color:var(--red);display:inline-flex;align-items:center;gap:6px;
  }
  .add-link:hover{color:var(--ink)}
  .icon-btn{
    width:36px;height:36px;display:grid;place-items:center;
    background:transparent;border:1px solid var(--ink);cursor:pointer;
    color:var(--ink);transition:.2s;
  }
  .icon-btn:hover{background:var(--ink);color:#fff}
  .icon-btn.danger{border-color:var(--red);color:var(--red)}
  .icon-btn.danger:hover{background:var(--red);color:#fff}

  .form-actions{
    display:flex;justify-content:flex-end;gap:10px;margin-top:28px;
    padding-top:24px;border-top:1px solid rgba(26,26,26,.10);
  }

  /* ============= Toggle ============= */
  .toggle{
    display:inline-block;width:44px;height:24px;
    background:var(--paper-3);border:1px solid var(--ink);
    cursor:pointer;position:relative;transition:.2s;
  }
  .toggle::after{
    content:"";position:absolute;top:1px;left:1px;
    width:20px;height:20px;background:var(--ink);transition:.2s;
  }
  .toggle.on{background:var(--red);border-color:var(--red)}
  .toggle.on::after{left:21px;background:#fff}
  .toggle-row{display:inline-flex;align-items:center;gap:12px;cursor:pointer}
  .toggle-row .lbl{font-size:14px;color:var(--ink);font-weight:500}

  /* ============= Empty state (corp opcional) ============= */
  .empty-state{
    text-align:center;padding:48px 24px;
    border:1px dashed rgba(26,26,26,.25);background:var(--paper-2);
  }
  .empty-state .ic{
    display:inline-flex;align-items:center;justify-content:center;
    width:52px;height:52px;border:1px solid var(--ink);margin-bottom:14px;
  }
  .empty-state h4{
    font-family:var(--display);font-weight:700;font-size:18px;margin:0 0 4px;
  }
  .empty-state p{
    font-family:var(--mono);font-size:11px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.12em;
    margin:0;max-width:440px;margin-left:auto;margin-right:auto;
  }

  /* ============= Password strength ============= */
  .pw-meter{display:flex;gap:4px;margin-top:10px}
  .pw-meter span{
    height:3px;flex:1;background:var(--paper-3);transition:background .3s;
  }
  .pw-label{
    font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.14em;
    margin-top:8px;
  }

  /* ============= Sessions list ============= */
  .session-list{display:flex;flex-direction:column;gap:0;border-top:1px solid rgba(26,26,26,.12)}
  .session-row{
    display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:18px;
    padding:18px 4px;border-bottom:1px solid rgba(26,26,26,.12);
  }
  .session-icon{
    width:44px;height:44px;border:1px solid var(--ink);
    display:grid;place-items:center;background:#fff;color:var(--ink);
  }
  .session-icon svg{width:18px;height:18px}
  .session-info .device{
    font-family:var(--sans);font-weight:600;font-size:15px;color:var(--ink);
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
  }
  .session-info .meta{
    font-family:var(--mono);font-size:11px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.10em;margin-top:4px;
  }

  /* ============= Notifications ============= */
  .notif-channel-legend{
    display:inline-flex;align-items:center;gap:6px;
    font-family:var(--mono);font-size:10px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.14em;
    padding:5px 10px;border:1px solid rgba(26,26,26,.20);
  }
  .notif-channel-legend svg{width:13px;height:13px}
  .notif-list{display:flex;flex-direction:column;border-top:1px solid rgba(26,26,26,.12)}
  .notif-row{
    display:flex;align-items:center;gap:24px;flex-wrap:wrap;
    padding:18px 4px;border-bottom:1px solid rgba(26,26,26,.12);
  }
  .notif-info{flex:1 1 280px;min-width:0}
  .notif-title{
    font-family:var(--sans);font-weight:600;font-size:15px;
    color:var(--ink);margin:0 0 4px;
  }
  .notif-desc{font-size:13px;color:var(--ash);margin:0}
  .notif-channels{display:flex;gap:8px}
  .notif-chip{
    display:inline-flex;align-items:center;gap:6px;
    font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.14em;
    padding:8px 12px;background:#fff;border:1px solid rgba(26,26,26,.20);
    color:var(--ash);cursor:pointer;transition:.2s;
  }
  .notif-chip svg{width:13px;height:13px}
  .notif-chip:hover{border-color:var(--ink);color:var(--ink)}
  .notif-chip.is-on{background:var(--ink);color:#fff;border-color:var(--ink)}
  .notif-chip.is-on .notif-dot{background:var(--red)}
  .notif-dot{width:6px;height:6px;border-radius:50%;background:var(--ash-2)}

  /* ============= Storage ============= */
  .storage-stats{
    display:grid;grid-template-columns:repeat(3, 1fr);gap:0;
    border-top:1px solid var(--ink);border-bottom:1px solid var(--ink);
    margin:0 0 28px;
  }
  @media (max-width:680px){.storage-stats{grid-template-columns:1fr}}
  .storage-stat{
    display:flex;align-items:center;gap:14px;
    padding:20px 22px;border-right:1px solid rgba(26,26,26,.12);
  }
  .storage-stats .storage-stat:last-child{border-right:0}
  @media (max-width:680px){
    .storage-stat{border-right:0;border-bottom:1px solid rgba(26,26,26,.12)}
    .storage-stats .storage-stat:last-child{border-bottom:0}
  }
  .storage-stat-icon{
    width:36px;height:36px;display:grid;place-items:center;
    background:var(--ink);color:#fff;
  }
  .storage-stat-icon svg{width:16px;height:16px}
  .storage-stat-label{
    font-family:var(--mono);font-size:10px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.14em;margin:0 0 2px;
  }
  .storage-stat-value{
    font-family:var(--display);font-weight:700;font-size:22px;letter-spacing:-.01em;
    color:var(--ink);margin:0;
  }
  .storage-progress{margin-bottom:28px}
  .storage-progress-head{
    display:flex;justify-content:space-between;align-items:baseline;
    margin-bottom:10px;flex-wrap:wrap;gap:8px;
  }
  .storage-progress-title{
    font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;
    color:var(--ink);
  }
  .storage-progress-pct{
    font-family:var(--mono);font-size:11px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.12em;
  }
  .storage-track{
    height:6px;background:var(--paper-3);border:1px solid var(--ink);overflow:hidden;
  }
  .storage-bar{height:100%;background:var(--red);transition:width .6s ease}

  .divider{height:1px;background:rgba(26,26,26,.12);margin:32px 0}

  .pack-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
  @media (max-width:720px){.pack-grid{grid-template-columns:1fr}}
  .pack-card{
    position:relative;padding:22px 20px 24px;
    background:#fff;border:1px solid var(--ink);
    cursor:pointer;transition:.2s;
  }
  .pack-card:hover{transform:translateY(-2px);box-shadow:8px 8px 0 0 var(--ink)}
  .pack-card.is-selected{background:var(--ink);color:#f1ebd9}
  .pack-card.is-selected .pack-tier{color:#f1ebd9}
  .pack-card.is-selected .pack-price-suffix{color:rgba(241,235,217,.6)}
  .pack-card.is-featured{border-width:2px;border-color:var(--red)}
  .pack-tier{
    font-family:var(--mono);font-size:10px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.18em;
  }
  .pack-card.is-featured .pack-tier{color:var(--red)}
  .pack-size{
    font-family:var(--display);font-weight:800;font-size:42px;letter-spacing:-.02em;
    margin:8px 0 4px;line-height:1;
  }
  .pack-size .pack-unit{font-size:18px;font-weight:600;margin-left:2px}
  .pack-price{
    font-family:var(--mono);font-size:14px;color:inherit;margin:0;
    display:flex;align-items:baseline;gap:6px;
  }
  .pack-price-suffix{font-size:11px;color:var(--ash);text-transform:uppercase;letter-spacing:.12em}
  .pack-check{
    position:absolute;top:14px;right:14px;
    width:22px;height:22px;border:1px solid currentColor;
    display:grid;place-items:center;opacity:0;
  }
  .pack-card.is-selected .pack-check{opacity:1;background:var(--red);border-color:var(--red);color:#fff}
  .pack-check svg{width:12px;height:12px}

  /* ============= Billing ============= */
  .billing-summary{display:grid;grid-template-columns:1.4fr 1fr;gap:24px;margin-bottom:32px}
  @media (max-width:880px){.billing-summary{grid-template-columns:1fr}}
  .billing-plan-card{
    background:var(--ink);color:#f1ebd9;padding:28px;
    position:relative;overflow:hidden;
  }
  .billing-plan-card::after{
    content:"";position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(500px 200px at 100% 0%, rgba(179,37,44,.4), transparent 60%);
  }
  .billing-plan-card > *{position:relative}
  .billing-plan-head{
    display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;
  }
  .billing-plan-tier{
    font-family:var(--mono);font-size:10px;
    color:rgba(241,235,217,.6);
    text-transform:uppercase;letter-spacing:.18em;
  }
  .billing-plan-name{
    font-family:var(--display);font-weight:800;font-size:48px;letter-spacing:-.02em;
    line-height:1;color:#fff;
  }
  .billing-plan-price{margin-top:6px;display:flex;align-items:baseline;gap:6px}
  .billing-plan-price .amount{font-family:var(--display);font-size:24px;font-weight:700;color:#fff}
  .billing-plan-price .period{font-family:var(--mono);font-size:11px;color:rgba(241,235,217,.6);text-transform:uppercase;letter-spacing:.14em}
  .billing-plan-features{margin-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:8px 16px}
  @media (max-width:520px){.billing-plan-features{grid-template-columns:1fr}}
  .billing-feature{display:flex;align-items:center;gap:8px;font-size:13px;color:#f1ebd9}
  .billing-feature svg{width:14px;height:14px;color:var(--red);flex-shrink:0}
  .billing-side{display:flex;flex-direction:column;gap:12px}
  .billing-side-card{
    display:flex;align-items:center;gap:14px;
    background:#fff;border:1px solid var(--ink);padding:18px;
  }
  .billing-side-icon{
    width:36px;height:36px;display:grid;place-items:center;
    background:var(--paper-2);border:1px solid var(--ink);
  }
  .billing-side-icon svg{width:16px;height:16px}
  .billing-side-label{
    font-family:var(--mono);font-size:10px;color:var(--ash);
    text-transform:uppercase;letter-spacing:.14em;margin:0 0 2px;
  }
  .billing-side-value{
    font-family:var(--display);font-weight:600;font-size:16px;color:var(--ink);
    margin:0;
  }
  .billing-side-sub{font-family:var(--mono);font-size:11px;color:var(--ash);margin:4px 0 0;text-transform:uppercase;letter-spacing:.10em}
  .ic-action{
    display:inline-flex;align-items:center;gap:6px;
    font-family:var(--mono);font-size:10px;font-weight:500;color:var(--ink);
    text-transform:uppercase;letter-spacing:.14em;
    background:transparent;border:1px solid var(--ink);
    padding:7px 10px;cursor:pointer;transition:.2s;
  }
  .ic-action:hover{background:var(--ink);color:#fff}

  .invoices-head{
    display:flex;justify-content:space-between;align-items:flex-end;gap:16px;
    margin-bottom:14px;flex-wrap:wrap;
  }
  .invoices-title{
    font-family:var(--display);font-weight:700;font-size:18px;color:var(--ink);margin:0;
  }
  .invoices-sub{font-size:13px;color:var(--ash);margin:4px 0 0}
  .invoice-list{
    display:flex;flex-direction:column;
    border-top:1px solid var(--ink);
  }
  .invoice-row{
    display:grid;
    grid-template-columns:130px 1fr 100px 110px auto;
    align-items:center;gap:16px;
    padding:16px 0;
    border-bottom:1px solid rgba(26,26,26,.12);
  }
  @media (max-width:680px){
    .invoice-row{grid-template-columns:1fr 1fr;grid-auto-rows:auto;gap:8px}
    .invoice-row .ic-desc{grid-column:1 / -1;order:-1}
  }
  .ic-date{font-family:var(--mono);font-size:11px;color:var(--ash);text-transform:uppercase;letter-spacing:.10em}
  .ic-desc{font-size:14px;color:var(--ink);font-weight:500}
  .ic-amount{font-family:var(--display);font-weight:700;font-size:16px;color:var(--ink);text-align:right}

  /* ============= Team ============= */
  .team-meta{
    display:flex;gap:24px;flex-wrap:wrap;
    padding:16px 20px;
    background:var(--paper-2);
    border:1px solid rgba(26,26,26,.12);
    margin-bottom:24px;
  }
  .team-meta-item{display:inline-flex;align-items:center;gap:10px;font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.10em;color:var(--ash)}
  .team-meta-item strong{font-family:var(--display);color:var(--ink);font-weight:700;font-size:18px;letter-spacing:-.01em}
  .team-meta-icon{width:28px;height:28px;display:grid;place-items:center;border:1px solid var(--ink);background:#fff}
  .team-meta-icon svg{width:13px;height:13px}
  .team-list{display:flex;flex-direction:column;border-top:1px solid var(--ink)}
  .team-card{
    display:grid;
    grid-template-columns:auto 1fr auto auto;
    gap:16px;align-items:center;
    padding:16px 0;border-bottom:1px solid rgba(26,26,26,.12);
  }
  @media (max-width:680px){
    .team-card{grid-template-columns:auto 1fr;gap:12px}
    .team-card .tc-role,.team-card .tc-actions{grid-column:1 / -1;justify-self:start}
    .team-card .tc-actions{display:flex;gap:8px}
  }
  .tc-avatar{
    width:44px;height:44px;display:grid;place-items:center;
    font-family:var(--display);font-weight:700;font-size:16px;
    background:var(--ink);color:#f1ebd9;border:1px solid var(--ink);
  }
  .tc-avatar.role-Editor{background:var(--red);color:#fff;border-color:var(--red)}
  .tc-avatar.role-Visor{background:#fff;color:var(--ink)}
  .tc-name{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
  .tc-name > span:first-child{font-family:var(--sans);font-weight:600;font-size:15px;color:var(--ink)}
  .tc-you-tag{
    font-family:var(--mono);font-size:9px;color:var(--red);
    text-transform:uppercase;letter-spacing:.14em;
    padding:2px 6px;border:1px solid var(--red);
  }
  .tc-meta{font-family:var(--mono);font-size:11px;color:var(--ash);text-transform:uppercase;letter-spacing:.10em;margin-top:4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .tc-meta-sep{width:4px;height:4px;background:var(--ash-2);display:inline-block}
  .tc-role{
    font-family:var(--mono);font-size:10px;text-transform:uppercase;letter-spacing:.14em;
    padding:5px 10px;border:1px solid var(--ink);color:var(--ink);background:transparent;
  }
  .tc-role.role-Editor{color:var(--red);border-color:var(--red)}
  .tc-role.role-Visor{color:var(--ash);border-color:var(--ash)}
  .tc-actions{display:flex;gap:6px}
  .tc-icon-btn{
    width:32px;height:32px;display:grid;place-items:center;background:transparent;
    border:1px solid var(--ink);cursor:pointer;color:var(--ink);transition:.2s;
  }
  .tc-icon-btn:hover{background:var(--ink);color:#fff}
  .tc-icon-btn.danger{border-color:var(--red);color:var(--red)}
  .tc-icon-btn.danger:hover{background:var(--red);color:#fff}
  .tc-icon-btn svg{width:14px;height:14px}

  /* ============= Activity ============= */
  .activity-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}
  .activity-filter{
    background:transparent;border:1px solid rgba(26,26,26,.20);
    font-family:var(--mono);font-size:10px;
    text-transform:uppercase;letter-spacing:.14em;color:var(--ash);
    padding:8px 14px;cursor:pointer;transition:.2s;
  }
  .activity-filter:hover{border-color:var(--ink);color:var(--ink)}
  .activity-filter.is-active{background:var(--ink);color:#fff;border-color:var(--ink)}
  .activity-group{margin-bottom:32px}
  .activity-group:last-child{margin-bottom:0}
  .activity-group-label{
    font-family:var(--mono);font-size:11px;
    text-transform:uppercase;letter-spacing:.18em;color:var(--red);
    margin:0 0 14px;padding-bottom:10px;border-bottom:1px solid rgba(26,26,26,.12);
  }
  .activity-timeline{display:flex;flex-direction:column;gap:0}
  .activity-item{
    display:flex;align-items:flex-start;gap:16px;padding:14px 0;
    border-bottom:1px solid rgba(26,26,26,.10);
  }
  .activity-item:last-child{border-bottom:0}
  .activity-icon{
    width:36px;height:36px;display:grid;place-items:center;flex-shrink:0;
    background:var(--paper-2);
    border:1px solid var(--act-color, var(--ink));
    color:var(--act-color, var(--ink));
  }
  .activity-icon svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2}
  .activity-card{
    flex:1;display:flex;justify-content:space-between;align-items:center;
    gap:16px;flex-wrap:wrap;
  }
  .activity-title{font-family:var(--sans);font-weight:600;font-size:15px;color:var(--ink);margin:0 0 2px}
  .activity-detail{font-size:13px;color:var(--ash);margin:0}
  .activity-time{font-family:var(--mono);font-size:11px;color:var(--ash);text-transform:uppercase;letter-spacing:.10em;flex-shrink:0}

  /* ============= Danger ============= */
  .danger-panel{
    background:#fff;border:1px solid var(--red);padding:36px;
    position:relative;
  }
  @media (max-width:720px){.danger-panel{padding:24px}}
  .danger-panel::before{
    content:"";position:absolute;left:0;top:0;bottom:0;width:6px;background:var(--red);
  }
  .danger-head{display:flex;align-items:flex-start;gap:18px;margin-bottom:28px;padding-left:18px}
  .danger-head-icon{
    width:44px;height:44px;display:grid;place-items:center;
    background:var(--red);color:#fff;flex-shrink:0;
  }
  .danger-head-icon svg{width:20px;height:20px}
  .danger-head h2{
    font-family:var(--display);font-weight:700;font-size:24px;
    color:var(--red);margin:0 0 4px;letter-spacing:-.01em;
  }
  .danger-head p{font-size:14px;color:var(--ash);margin:0;max-width:62ch}
  .danger-list{display:flex;flex-direction:column;border-top:1px solid rgba(26,26,26,.12);margin-left:18px}
  .danger-row{
    display:flex;align-items:center;justify-content:space-between;
    gap:24px;flex-wrap:wrap;
    padding:22px 0;border-bottom:1px solid rgba(26,26,26,.12);
  }
  .danger-row-info{display:flex;align-items:flex-start;gap:14px;flex:1 1 320px;min-width:0}
  .danger-row-icon{
    width:36px;height:36px;display:grid;place-items:center;
    background:var(--paper-2);border:1px solid var(--ink);color:var(--ink);flex-shrink:0;
  }
  .danger-row-icon svg{width:16px;height:16px}
  .danger-row.is-critical .danger-row-icon{background:var(--red);color:#fff;border-color:var(--red)}
  .danger-row-title{font-family:var(--display);font-weight:700;font-size:16px;color:var(--ink);margin:0 0 4px}
  .danger-row.is-critical .danger-row-title{color:var(--red)}
  .danger-row-desc{font-size:13px;color:var(--ash);margin:0;max-width:54ch}

  /* ============= Toast ============= */
  .toast{
    position:fixed;bottom:24px;right:24px;z-index:80;
    background:var(--ink);color:#f1ebd9;
    border-left:4px solid var(--red);
    padding:14px 22px;display:flex;align-items:center;gap:14px;
    box-shadow:0 18px 40px -16px rgba(0,0,0,.4);
    max-width:380px;
  }
  .toast-icon{
    width:32px;height:32px;display:grid;place-items:center;
    background:var(--red);color:#fff;flex-shrink:0;
  }
  .toast-icon svg{width:14px;height:14px}
  .toast-title{font-family:var(--display);font-weight:700;font-size:14px;color:#fff;margin:0}
  .toast-msg{font-family:var(--mono);font-size:11px;color:rgba(241,235,217,.7);text-transform:uppercase;letter-spacing:.10em;margin:2px 0 0}

  /* ============= Modal ============= */
  .modal-backdrop{
    position:fixed;inset:0;z-index:90;
    background:rgba(12,12,12,.7);backdrop-filter:blur(4px);
    display:flex;align-items:center;justify-content:center;
    padding:24px;
  }
  .modal{
    background:#fff;border:1px solid var(--ink);max-width:480px;width:100%;
    padding:36px;position:relative;
  }
  .modal h3{
    font-family:var(--display);font-weight:700;font-size:24px;color:var(--ink);
    margin:0 0 6px;letter-spacing:-.01em;
  }
  .modal p{font-size:14px;color:var(--ash);margin:0 0 20px}
  .modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}
  .modal-pack-option{
    display:flex;justify-content:space-between;align-items:center;gap:12px;
    padding:14px 16px;border:1px solid rgba(26,26,26,.20);
    cursor:pointer;transition:.2s;background:#fff;margin-bottom:8px;
  }
  .modal-pack-option:hover{border-color:var(--red)}
  .modal-pack-option.is-selected{border-color:var(--red);background:#fff7f7}

  .fade-enter{animation:fadeIn .3s ease}
  @keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

  /* ============= Footer (matches landing) ============= */
  footer{
    background:var(--ink);color:#f1ebd9;
    padding:80px 0 32px;margin-top:80px;
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
    display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px;
    padding-top:24px;
    font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;
    color:rgba(241,235,217,.5);
  }
</style>
</head>
<body x-data="profilePanel()">

<!-- NAV (clone from landing) -->
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
      <li><a href="{{ route('home') }}#faq">FAQ</a></li>
    </ul>
    <a class="cta" href="{{ route('qr.create') }}">Abrir QR Studio</a>
  </div>
</nav>

<!-- Marquee -->
<div class="marquee">
  <div class="marquee-track">
    <span>Panel de cuenta</span>
    <span>QR dinámicos · sin renovar tinta</span>
    <span>Tu piel · tu canal</span>
    <span>Sesión segura</span>
    <span>Cambia el destino cuando quieras</span>
    <span>Panel de cuenta</span>
    <span>QR dinámicos · sin renovar tinta</span>
    <span>Tu piel · tu canal</span>
    <span>Sesión segura</span>
    <span>Cambia el destino cuando quieras</span>
  </div>
</div>

<!-- Top bar (lang/currency/logout) -->
<div class="topbar">
  <div class="wrap row">
    <!-- Language -->
    <div style="position:relative" x-data="{ open:false }" @click.outside="open=false">
      <button type="button" class="topbar-btn" @click="open=!open" :title="t('language')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"></path></svg>
        <span class="flag" x-text="languages[prefs.language].flag">🇪🇸</span>
        <span x-text="languages[prefs.language].code">ES</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:10px;height:10px;opacity:.6"><polyline points="6 9 12 15 18 9"></polyline></svg>
      </button>
      <div x-show="open" x-transition class="popover" style="display:none">
        <template x-for="(lang, code) in languages" :key="code">
          <div class="popover-item" :class="{ active: prefs.language === code }" @click="prefs.language = code; open=false; notify(t('languageChanged'), lang.label)">
            <span class="flag" x-text="lang.flag"></span>
            <span style="flex:1" x-text="lang.label"></span>
            <svg x-show="prefs.language === code" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
        </template>
      </div>
    </div>

    <!-- Currency -->
    <div style="position:relative" x-data="{ open:false }" @click.outside="open=false">
      <button type="button" class="topbar-btn" @click="open=!open" :title="t('currency')">
        <span style="font-weight:700" x-text="currencies[prefs.currency].symbol">€</span>
        <span x-text="prefs.currency">EUR</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:10px;height:10px;opacity:.6"><polyline points="6 9 12 15 18 9"></polyline></svg>
      </button>
      <div x-show="open" x-transition class="popover" style="display:none">
        <template x-for="(cur, code) in currencies" :key="code">
          <div class="popover-item" :class="{ active: prefs.currency === code }" @click="prefs.currency = code; open=false; notify(t('currencyChanged'), cur.label)">
            <span style="font-weight:700;width:1.2rem;text-align:center" x-text="cur.symbol"></span>
            <span style="flex:1" x-text="cur.label"></span>
            <svg x-show="prefs.currency === code" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
        </template>
      </div>
    </div>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}">@csrf
      <button type="submit" class="logout-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        <span x-text="t('logout')">Cerrar sesión</span>
      </button>
    </form>
  </div>
</div>

<!-- ============= HERO ============= -->
<section class="acct-hero">
  <div class="wrap">
    <p class="acct-eyebrow">Panel · Mi cuenta</p>

    <div class="acct-hero-grid">
      <!-- Avatar -->
      <div class="avatar-wrap">
        <div class="avatar">
          <template x-if="!user.avatar"><span x-text="initials">MN</span></template>
          <template x-if="user.avatar"><img :src="user.avatar" alt=""/></template>
        </div>
        <label class="avatar-edit" title="Cambiar foto de perfil">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
          <input type="file" accept="image/*" @change="onAvatar($event)" style="display:none"/>
        </label>
      </div>

      <!-- Identity block -->
      <div>
        <h1 class="acct-name" x-text="user.firstName + ' ' + user.lastName">Miguel Novoa García</h1>
        <div class="acct-meta">
          <span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span x-text="user.email">geometrymike20@gmail.com</span>
          </span>
          <span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            Madrid · España
          </span>
          <span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Miembro desde mar 2024
          </span>
        </div>
      </div>

      <!-- Actions -->
      <div class="acct-actions"></div>
    </div>
  </div>
</section>

<!-- ============= TABS ============= -->
<div class="tabs-wrap" @click.outside="navOpen = false">
  <div class="wrap">
    <button type="button" class="nav-mobile-toggle" @click="navOpen = !navOpen" :aria-expanded="navOpen">
      <span class="nav-mobile-current">
        <span x-html="(nav.find(i => i.id === tab) || nav[0]).icon"></span>
        <span x-text="(nav.find(i => i.id === tab) || nav[0]).label">Información general</span>
      </span>
      <span class="nav-mobile-burger" :class="{ 'is-open': navOpen }" aria-hidden="true">
        <span></span><span></span><span></span>
      </span>
    </button>

    <nav class="tabs-bar" :class="{ 'is-open': navOpen }">
      <template x-for="item in nav" :key="item.id">
        <a :href="item.href || '#'"
           class="tab-btn"
           :class="item.href ? 'studio' : { active: tab === item.id, danger: item.id === 'danger' }"
           @click="if (!item.href) { $event.preventDefault(); tab = item.id; navOpen = false; window.scrollTo({top:0,behavior:'smooth'}); }">
          <span x-html="item.icon"></span>
          <span x-text="item.label"></span>
          <span x-show="item.premium" class="premium-tag">Premium</span>
          <svg x-show="item.href" class="ext-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px"><path d="M7 17L17 7"></path><path d="M7 7h10v10"></path></svg>
        </a>
      </template>
    </nav>
  </div>
</div>

<!-- ============= MAIN ============= -->
<main class="acct-main">
  <div class="wrap">

    <!-- ===== GENERAL ===== -->
    <section x-show="tab === 'general'" class="fade-enter" x-cloak>

      <!-- Personal -->
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">01 · Identidad</p>
            <h2>Información personal</h2>
            <p class="card-sub">Gestiona los datos básicos de tu cuenta. Estos datos son privados y solo se usan para tu perfil.</p>
          </div>
        </div>

        <div class="form-grid">
          <div class="field">
            <label class="label">Nombre</label>
            <input type="text" class="input" x-model="user.firstName" placeholder="Tu nombre"/>
          </div>
          <div class="field">
            <label class="label">Apellidos</label>
            <input type="text" class="input" x-model="user.lastName" placeholder="Tus apellidos"/>
          </div>
          <div class="field">
            <label class="label">Fecha de nacimiento</label>
            <input type="date" class="input" x-model="user.birthdate"/>
          </div>
          <div class="field">
            <label class="label">Género</label>
            <select class="input" x-model="user.gender">
              <option value="">Prefiero no decirlo</option>
              <option value="m">Masculino</option>
              <option value="f">Femenino</option>
              <option value="o">Otro</option>
            </select>
          </div>
          <div class="field full">
            <label class="label">Correo electrónico <span style="color:var(--red)">*</span></label>
            <div class="input-wrap">
              <input type="email" class="input" x-model="user.email" style="padding-right:120px"/>
              <span class="right-badge badge badge-ok" x-show="user.email && user.email === user.verifiedEmail">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:11px;height:11px"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Verificado
              </span>
              <button type="button" class="right-badge btn btn-secondary btn-xs"
                      x-show="user.email !== user.verifiedEmail"
                      @click="notify('Verificación enviada', 'Revisa tu bandeja de entrada')">
                Verificar
              </button>
            </div>
          </div>
          <div class="field full">
            <label class="label">Teléfono</label>
            <template x-for="(phone, idx) in user.phones" :key="idx">
              <div class="phone-row">
                <select class="input" x-model="phone.code">
                  <option value="+34">+34 ES</option>
                  <option value="+1">+1 US</option>
                  <option value="+44">+44 UK</option>
                  <option value="+52">+52 MX</option>
                  <option value="+54">+54 AR</option>
                </select>
                <input type="tel" class="input" x-model="phone.number" placeholder="600 000 000"/>
                <button class="icon-btn danger" @click="user.phones.splice(idx,1)" title="Eliminar" type="button">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6M10 11v6M14 11v6"></path></svg>
                </button>
              </div>
            </template>
            <button class="add-link" @click="user.phones.push({code:'+34',number:''})" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Agregar teléfono
            </button>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn-secondary">Cancelar</button>
          <button class="btn btn-primary" @click="save('Información personal')">Guardar cambios</button>
        </div>
      </div>

      <!-- Corporate -->
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">02 · Profesional</p>
            <h2>Información corporativa</h2>
            <p class="card-sub">Solo si eres profesional o emites facturas a nombre de tu estudio o empresa.</p>
          </div>
          <label class="toggle-row">
            <span class="lbl">Soy profesional</span>
            <span class="toggle" :class="{ on: company.isProfessional }" @click="company.isProfessional = !company.isProfessional"></span>
          </label>
        </div>

        <div x-show="!company.isProfessional" class="empty-state fade-enter" x-cloak>
          <div class="ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          </div>
          <h4>Cuenta personal</h4>
          <p>Activa el interruptor si más adelante quieres facturar como profesional.</p>
        </div>

        <div x-show="company.isProfessional" class="fade-enter" x-cloak>
          <div class="form-grid">
            <div class="field">
              <label class="label">Nombre de la empresa</label>
              <input type="text" class="input" x-model="company.name" placeholder="Black Ink Studio"/>
            </div>
            <div class="field">
              <label class="label">Categoría</label>
              <select class="input" x-model="company.category">
                <option value="">Selecciona una categoría</option>
                <option>Estudio de tatuajes</option>
                <option>Tatuador autónomo</option>
                <option>Piercing &amp; modificación</option>
                <option>Academia / formación</option>
                <option>Otros</option>
              </select>
            </div>
            <div class="field">
              <label class="label">Email corporativo</label>
              <input type="email" class="input" x-model="company.email" placeholder="contacto@empresa.com"/>
            </div>
            <div class="field">
              <label class="label">Sitio web</label>
              <input type="url" class="input" x-model="company.website" placeholder="https://..."/>
            </div>
            <div class="field full">
              <label class="label">Dirección fiscal</label>
              <input type="text" class="input" x-model="company.address" placeholder="Calle, número, ciudad, provincia, código postal, país"/>
            </div>
            <div class="field">
              <label class="label">CIF / NIF</label>
              <input type="text" class="input" x-model="company.taxId" placeholder="B12345678"/>
            </div>
            <div class="field">
              <label class="label">VAT</label>
              <input type="text" class="input" x-model="company.vat" placeholder="ESB12345678"/>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn btn-secondary">Cancelar</button>
            <button class="btn btn-primary" @click="save('Datos de empresa')">Guardar cambios</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== SECURITY ===== -->
    <section x-show="tab === 'security'" class="fade-enter" x-cloak>
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">01 · Acceso</p>
            <h2>Cambiar contraseña</h2>
            <p class="card-sub">Usa al menos 12 caracteres con letras, números y símbolos.</p>
          </div>
        </div>
        <div class="form-grid" style="max-width:680px">
          <div class="field full">
            <label class="label">Contraseña actual</label>
            <input type="password" class="input" placeholder="••••••••••"/>
          </div>
          <div class="field">
            <label class="label">Nueva contraseña</label>
            <input type="password" class="input" x-model="security.newPassword" placeholder="••••••••••"/>
            <div class="pw-meter">
              <span :style="`background:${pwScoreColor(0)}`"></span>
              <span :style="`background:${pwScoreColor(1)}`"></span>
              <span :style="`background:${pwScoreColor(2)}`"></span>
              <span :style="`background:${pwScoreColor(3)}`"></span>
            </div>
            <p class="pw-label" :style="`color:${pwScoreColor(0)}`" x-text="pwLabel">Introduce una contraseña</p>
          </div>
          <div class="field">
            <label class="label">Confirmar contraseña</label>
            <input type="password" class="input" placeholder="••••••••••"/>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" @click="save('Contraseña')">Actualizar contraseña</button>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">02 · Dispositivos</p>
            <h2>Sesiones activas</h2>
            <p class="card-sub">Dispositivos conectados a tu cuenta actualmente.</p>
          </div>
        </div>
        <div class="session-list">
          <template x-for="s in sessions" :key="s.id">
            <div class="session-row">
              <span class="session-icon" x-html="s.icon"></span>
              <div class="session-info">
                <div class="device">
                  <span x-text="s.device"></span>
                  <span x-show="s.current" class="badge badge-ok">Esta sesión</span>
                </div>
                <p class="meta"><span x-text="s.location"></span> · <span x-text="s.ip"></span> · <span x-text="s.lastActive"></span></p>
              </div>
              <button x-show="!s.current" class="btn btn-danger-outline btn-xs">Revocar</button>
            </div>
          </template>
        </div>
        <div class="form-actions" style="border-top:0;padding-top:0;margin-top:18px">
          <button class="btn btn-secondary btn-sm">Cerrar todas las demás sesiones</button>
        </div>
      </div>
    </section>

    <!-- ===== NOTIFICATIONS ===== -->
    <section x-show="tab === 'notifications'" class="fade-enter" x-cloak>
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">Canales</p>
            <h2>Notificaciones</h2>
            <p class="card-sub">Elige cómo y cuándo quieres recibir avisos.</p>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <span class="notif-channel-legend">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              Email
            </span>
            <span class="notif-channel-legend">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"></path></svg>
              Push
            </span>
          </div>
        </div>

        <div class="notif-list">
          <template x-for="(n, key) in notifications" :key="key">
            <div class="notif-row">
              <div class="notif-info">
                <p class="notif-title" x-text="n.title"></p>
                <p class="notif-desc" x-text="n.desc"></p>
              </div>
              <div class="notif-channels">
                <button type="button" class="notif-chip" :class="{ 'is-on': n.email }" @click="n.email = !n.email">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  Email
                  <span class="notif-dot"></span>
                </button>
                <button type="button" class="notif-chip" :class="{ 'is-on': n.push }" @click="n.push = !n.push">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"></path></svg>
                  Push
                  <span class="notif-dot"></span>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- ===== STORAGE / BILLING ===== -->
    <section x-show="tab === 'storage'" class="fade-enter" x-cloak>
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">01 · Espacio</p>
            <h2>Almacenamiento</h2>
            <p class="card-sub">Gestiona el espacio usado por tus QR, recursos y exportaciones.</p>
          </div>
          <span class="badge"
                :class="storage.percent < 70 ? 'badge-ok' : (storage.percent < 90 ? 'badge-warn' : 'badge-red')"
                x-text="storage.percent < 70 ? 'Espacio saludable' : (storage.percent < 90 ? 'Acercándote al límite' : 'Casi sin espacio')">
            Espacio saludable
          </span>
        </div>

        <div class="storage-stats">
          <div class="storage-stat">
            <span class="storage-stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"></path></svg>
            </span>
            <div>
              <p class="storage-stat-label">Usado</p>
              <p class="storage-stat-value" x-text="storage.usedLabel">0.04 GB</p>
            </div>
          </div>
          <div class="storage-stat">
            <span class="storage-stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.5 14.5A6.5 6.5 0 0114 21H7a5 5 0 01-1-9.9A7 7 0 0119 9a5.5 5.5 0 011.5 5.5z"></path></svg>
            </span>
            <div>
              <p class="storage-stat-label">Disponible</p>
              <p class="storage-stat-value" x-text="storage.totalLabel">0.1 GB</p>
            </div>
          </div>
          <div class="storage-stat">
            <span class="storage-stat-icon">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></svg>
            </span>
            <div>
              <p class="storage-stat-label">QR generados</p>
              <p class="storage-stat-value">47</p>
            </div>
          </div>
        </div>

        <div class="storage-progress">
          <div class="storage-progress-head">
            <span class="storage-progress-title">Uso del plan</span>
            <span class="storage-progress-pct"><span x-text="storage.percent">40</span>% usado · <span x-text="storage.usedLabel">0.04 GB</span> de <span x-text="storage.totalLabel">0.1 GB</span></span>
          </div>
          <div class="storage-track"><div class="storage-bar" :style="`width:${storage.percent}%`"></div></div>
        </div>

        <div class="divider"></div>

        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:18px">
          <div>
            <h3>Ampliar espacio</h3>
            <p class="card-sub">Pago único · sin renovación automática.</p>
          </div>
          <span class="mono upper" style="font-size:10px;color:var(--ash);letter-spacing:.16em">Selecciona un paquete</span>
        </div>

        <div class="pack-grid">
          <template x-for="(pack, idx) in storagePacks" :key="pack.size">
            <div class="pack-card"
                 :class="{ 'is-selected': storage.selectedPack === pack.size, 'is-featured': idx === 1 }"
                 @click="storage.selectedPack = pack.size">
              <span class="pack-tier" x-text="pack.label"></span>
              <p class="pack-size"><span x-text="pack.size"></span><span class="pack-unit">MB</span></p>
              <p class="pack-price">
                <span x-text="formatPrice(pack.price)"></span>
                <span class="pack-price-suffix">/ pago único</span>
              </p>
              <span class="pack-check">
                <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
              </span>
            </div>
          </template>
        </div>

        <div class="form-actions" style="border-top:0;padding-top:24px">
          <button class="btn btn-secondary">Cancelar</button>
          <button class="btn btn-primary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 5h13M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path></svg>
            Comprar ampliación
          </button>
        </div>
      </div>

      <!-- Billing -->
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">02 · Suscripción</p>
            <h2>Facturación y plan</h2>
            <p class="card-sub">Gestiona tu suscripción, método de pago y facturas.</p>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="btn btn-secondary btn-sm">Cancelar plan</button>
            <button class="btn btn-primary btn-sm">Cambiar de plan</button>
          </div>
        </div>

        <div class="billing-summary">
          <div class="billing-plan-card">
            <div class="billing-plan-head">
              <span class="billing-plan-tier">Plan actual</span>
              <span class="badge badge-hero-ok">Activo</span>
            </div>
            <div class="billing-plan-name" x-text="billing.plan">Pro</div>
            <div class="billing-plan-price">
              <span class="amount" x-text="formatPrice(billing.price)">9.99 €</span>
              <span class="period">/ <span x-text="billing.period">mensual</span></span>
            </div>
            <div class="billing-plan-features">
              <template x-for="feature in billing.features" :key="feature">
                <div class="billing-feature">
                  <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                  <span x-text="feature"></span>
                </div>
              </template>
            </div>
          </div>

          <div class="billing-side">
            <div class="billing-side-card">
              <span class="billing-side-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              </span>
              <div style="flex:1;min-width:0">
                <p class="billing-side-label">Próximo cobro</p>
                <p class="billing-side-value" x-text="billing.nextBilling">01 jun 2026</p>
                <p class="billing-side-sub"><span x-text="formatPrice(billing.price)">9.99 €</span> · renovación auto.</p>
              </div>
            </div>
            <div class="billing-side-card">
              <span class="billing-side-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
              </span>
              <div style="flex:1;min-width:0">
                <p class="billing-side-label">Método de pago</p>
                <p class="billing-side-value"><span x-text="billing.paymentMethod.brand">Visa</span> •••• <span x-text="billing.paymentMethod.last4">4242</span></p>
                <p class="billing-side-sub">Caduca <span x-text="billing.paymentMethod.expires">12/27</span></p>
              </div>
              <button class="ic-action" type="button">Editar</button>
            </div>
          </div>
        </div>

        <div class="invoices-head">
          <div>
            <p class="invoices-title">Historial de facturas</p>
            <p class="invoices-sub">Descarga tus recibos en PDF cuando quieras.</p>
          </div>
          <button class="ic-action" type="button">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"></path></svg>
            Exportar todo
          </button>
        </div>

        <div class="invoice-list">
          <template x-for="inv in invoices" :key="inv.id">
            <div class="invoice-row">
              <span class="ic-date" x-text="inv.date"></span>
              <span class="ic-desc" x-text="inv.desc"></span>
              <span class="ic-amount" x-text="formatPrice(inv.amount)"></span>
              <span class="badge" :class="inv.status === 'Pagado' ? 'badge-ok' : 'badge-warn'" x-text="inv.status"></span>
              <button class="ic-action" type="button">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"></path></svg>
                PDF
              </button>
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- ===== TEAM ===== -->
    <section x-show="tab === 'team'" class="fade-enter" x-cloak>
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">Colaboración</p>
            <h2>Grupo y miembros</h2>
            <p class="card-sub">Invita personas a colaborar en tus proyectos.</p>
          </div>
          <button class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Invitar miembro
          </button>
        </div>

        <div class="team-meta">
          <div class="team-meta-item">
            <span class="team-meta-icon">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm10 4l2 2 4-4"></path></svg>
            </span>
            <span><strong x-text="team.length">3</strong> de 5 miembros</span>
          </div>
          <div class="team-meta-item">
            <span class="team-meta-icon">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle></svg>
            </span>
            <span><strong x-text="team.filter(m => m.online).length">2</strong> en línea</span>
          </div>
          <div class="team-meta-item">
            <span class="team-meta-icon">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"></path></svg>
            </span>
            <span>Plan <strong x-text="billing.plan">Pro</strong></span>
          </div>
        </div>

        <div class="team-list">
          <template x-for="m in team" :key="m.id">
            <div class="team-card">
              <span class="tc-avatar" :class="'role-' + m.role" x-text="m.initials"></span>
              <div class="tc-info">
                <div class="tc-name">
                  <span x-text="m.name"></span>
                  <span class="tc-you-tag" x-show="m.role === 'Propietario'">Tú</span>
                </div>
                <div class="tc-meta">
                  <span x-text="m.email"></span>
                  <span class="tc-meta-sep"></span>
                  <span x-text="m.lastActive"></span>
                </div>
              </div>
              <span class="tc-role" :class="'role-' + m.role" x-text="m.role"></span>
              <div class="tc-actions">
                <button class="tc-icon-btn" type="button" title="Editar permisos" x-show="m.role !== 'Propietario'">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11.8 15.6 8 16.4l.8-3.8 9.8-7.2z"></path></svg>
                </button>
                <button class="tc-icon-btn danger" type="button" title="Eliminar miembro" x-show="m.role !== 'Propietario'">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"></path></svg>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- ===== ACTIVITY ===== -->
    <section x-show="tab === 'activity'" class="fade-enter" x-cloak>
      <div class="card">
        <div class="card-head">
          <div class="title-block">
            <p class="card-eyebrow">Registro</p>
            <h2>Historial de actividad</h2>
            <p class="card-sub">Eventos recientes en tu cuenta.</p>
          </div>
          <button class="ic-action" type="button">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"></path></svg>
            Exportar registro
          </button>
        </div>

        <div class="activity-filters">
          <button class="activity-filter" :class="{ 'is-active': activityFilter === 'all' }" @click="activityFilter = 'all'">Todo</button>
          <button class="activity-filter" :class="{ 'is-active': activityFilter === 'qr' }" @click="activityFilter = 'qr'">QR</button>
          <button class="activity-filter" :class="{ 'is-active': activityFilter === 'security' }" @click="activityFilter = 'security'">Seguridad</button>
          <button class="activity-filter" :class="{ 'is-active': activityFilter === 'billing' }" @click="activityFilter = 'billing'">Facturación</button>
          <button class="activity-filter" :class="{ 'is-active': activityFilter === 'account' }" @click="activityFilter = 'account'">Cuenta</button>
        </div>

        <template x-for="(items, groupLabel) in groupedActivity" :key="groupLabel">
          <div class="activity-group">
            <p class="activity-group-label" x-text="groupLabel"></p>
            <div class="activity-timeline">
              <template x-for="a in items" :key="a.id">
                <div class="activity-item">
                  <span class="activity-icon" :style="`--act-color:${activityIcons[a.type].color}`">
                    <svg viewBox="0 0 24 24" x-html="activityIcons[a.type].svg"></svg>
                  </span>
                  <div class="activity-card">
                    <div>
                      <p class="activity-title" x-text="a.title"></p>
                      <p class="activity-detail" x-text="a.detail"></p>
                    </div>
                    <span class="activity-time" x-text="a.time"></span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </template>

        <div x-show="filteredActivity.length === 0" style="text-align:center;padding:40px 0;color:var(--ash);font-size:14px">
          No hay eventos en esta categoría.
        </div>
      </div>
    </section>

    <!-- ===== DANGER ===== -->
    <section x-show="tab === 'danger'" class="fade-enter" x-cloak>
      <div class="danger-panel">
        <div class="danger-head">
          <span class="danger-head-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
          </span>
          <div>
            <h2>Acciones avanzadas</h2>
            <p>Estas acciones son sensibles o irreversibles. Asegúrate de tener una copia de tus datos antes de continuar.</p>
          </div>
        </div>

        <div class="danger-list">
          <div class="danger-row">
            <div class="danger-row-info">
              <span class="danger-row-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"></path></svg>
              </span>
              <div>
                <p class="danger-row-title">Exportar mis datos</p>
                <p class="danger-row-desc">Descarga una copia de tu información personal, QR y facturas en formato JSON.</p>
              </div>
            </div>
            <button class="btn btn-secondary btn-sm">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"></path></svg>
              Exportar
            </button>
          </div>

          <div class="danger-row">
            <div class="danger-row-info">
              <span class="danger-row-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.36 6.64A9 9 0 0120.77 15M12 3v9M3.23 15A9 9 0 015.64 6.64"></path></svg>
              </span>
              <div>
                <p class="danger-row-title">Desactivar cuenta</p>
                <p class="danger-row-desc">Tu cuenta quedará oculta para otros usuarios. Podrás reactivarla iniciando sesión de nuevo.</p>
              </div>
            </div>
            <button class="btn btn-secondary btn-sm">Desactivar</button>
          </div>

          <div class="danger-row is-critical">
            <div class="danger-row-info">
              <span class="danger-row-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
              </span>
              <div>
                <p class="danger-row-title">Eliminar cuenta permanentemente</p>
                <p class="danger-row-desc">Todos tus QR, configuración, equipo y datos serán borrados sin posibilidad de recuperación.</p>
              </div>
            </div>
            <button @click="confirmDelete = true" class="btn btn-danger" type="button">
              <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"></path></svg>
              Eliminar cuenta
            </button>
          </div>
        </div>
      </div>
    </section>

  </div>
</main>

<!-- ============= Toast ============= -->
<div x-show="toast.show" x-transition.opacity class="toast" style="display:none">
  <span class="toast-icon">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
  </span>
  <div>
    <p class="toast-title" x-text="toast.title"></p>
    <p class="toast-msg" x-text="toast.msg"></p>
  </div>
</div>

<!-- ============= Modal: Upgrade storage ============= -->
<div x-show="openUpgrade" x-transition.opacity class="modal-backdrop" @click.self="openUpgrade = false" style="display:none">
  <div class="modal">
    <h3>Ampliar almacenamiento</h3>
    <p>Selecciona el paquete que mejor se adapte a ti.</p>
    <div>
      <template x-for="pack in storagePacks" :key="pack.size">
        <label class="modal-pack-option" :class="{ 'is-selected': storage.selectedPack === pack.size }">
          <span style="display:flex;align-items:center;gap:12px">
            <input type="radio" name="upgrade-pack" :value="pack.size" x-model.number="storage.selectedPack" style="accent-color:var(--red)"/>
            <span>
              <span style="font-family:var(--display);font-weight:700;font-size:16px;color:var(--ink)"><span x-text="pack.size"></span> MB</span>
              <span class="mono upper" style="display:block;font-size:10px;color:var(--ash);margin-top:2px;letter-spacing:.14em" x-text="pack.label"></span>
            </span>
          </span>
          <span style="font-family:var(--display);font-weight:700;font-size:18px;color:var(--ink)" x-text="formatPrice(pack.price)"></span>
        </label>
      </template>
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary btn-sm" @click="openUpgrade = false">Cancelar</button>
      <button class="btn btn-primary btn-sm" @click="openUpgrade = false; notify('Ampliación añadida', 'Tu cuenta tiene más espacio.')">Confirmar compra</button>
    </div>
  </div>
</div>

<!-- ============= Modal: Confirm delete ============= -->
<div x-show="confirmDelete" x-transition.opacity class="modal-backdrop" @click.self="confirmDelete = false" style="display:none">
  <div class="modal" style="border-color:var(--red)">
    <h3 style="color:var(--red)">¿Eliminar cuenta?</h3>
    <p>Esta acción es <strong>irreversible</strong>. Para confirmar, escribe <strong>ELIMINAR</strong> a continuación.</p>
    <input type="text" class="input" placeholder="Escribe ELIMINAR" x-model="deleteConfirmText"/>
    <div class="modal-actions">
      <button class="btn btn-secondary btn-sm" @click="confirmDelete = false; deleteConfirmText = ''">Cancelar</button>
      <button class="btn btn-danger btn-sm"
              :disabled="deleteConfirmText !== 'ELIMINAR'"
              :style="deleteConfirmText !== 'ELIMINAR' ? 'opacity:.4;cursor:not-allowed' : ''"
              @click="if(deleteConfirmText === 'ELIMINAR'){ confirmDelete=false; deleteConfirmText=''; notify('Solicitud enviada','Recibirás un email para confirmar.'); }">
        Eliminar definitivamente
      </button>
    </div>
  </div>
</div>

<!-- ============= Footer (matches landing) ============= -->
<footer>
  <div class="wrap">
    <div class="foot-top">
      <div>
        <img class="foot-logo" src="{{ asset('images/designer/logo-hero-qr.png') }}" alt="Dynamic Tattoos" />
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
          <h5>Legal</h5>
          <ul>
            <li><a href="#">Términos</a></li>
            <li><a href="#">Privacidad</a></li>
            <li><a href="#">Cookies</a></li>
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

<style>[x-cloak]{display:none !important}</style>

<script>
  // Nav scrolled state
  (function(){
    const nav = document.getElementById('topNav');
    const onScroll = () => {
      if(window.scrollY > 12) nav.classList.add('scrolled');
      else nav.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive:true });
    onScroll();
  })();

  function profilePanel() {
    return {
      tab: 'general',
      navOpen: false,
      openUpgrade: false,
      confirmDelete: false,
      deleteConfirmText: '',
      toast: { show:false, title:'', msg:'' },

      user: {
        firstName: @json(auth()->user()?->first_name ?? explode(' ', (string) auth()->user()?->name)[0] ?? 'Usuario'),
        lastName: @json(auth()->user()?->last_name ?? trim(substr((string) auth()->user()?->name, strlen(explode(' ', (string) auth()->user()?->name)[0] ?? '')))),
        email: @json(auth()->user()?->email ?? ''),
        verifiedEmail: @json(auth()->user()?->email_verified_at ? auth()->user()?->email : ''),
        birthdate: '1995-08-14',
        gender: 'm',
        avatar: null,
        phones: [{ code:'+34', number:'612 345 678' }],
      },
      company: {
        isProfessional: false,
        name: 'Black Ink Studio',
        category: 'Estudio de tatuajes',
        email: 'hola@blackink.es',
        website: 'https://blackink.es',
        address: 'Calle Mayor 12, 28013 Madrid, España',
        taxId: 'B87654321',
        vat: 'ESB87654321',
      },
      prefs: { language:'es', timezone:'Europe/Madrid', currency:'EUR' },

      languages: {
        es: { code:'ES', label:'Español',    flag:'🇪🇸' },
        en: { code:'EN', label:'English',    flag:'🇬🇧' },
        pt: { code:'PT', label:'Português',  flag:'🇵🇹' },
        fr: { code:'FR', label:'Français',   flag:'🇫🇷' },
        de: { code:'DE', label:'Deutsch',    flag:'🇩🇪' },
      },
      currencies: {
        EUR: { symbol:'€', label:'Euro',          rate:1.00,  position:'after',  decimals:2 },
        USD: { symbol:'$', label:'US Dollar',     rate:1.08,  position:'before', decimals:2 },
        GBP: { symbol:'£', label:'Libra esterlina', rate:0.85, position:'before', decimals:2 },
        MXN: { symbol:'$', label:'Peso mexicano', rate:18.50, position:'before', decimals:2 },
      },
      i18n: {
        es: {
          openStudio:'Abrir QR Studio', openStudioDesc:'Crea, gestiona y personaliza tus códigos QR',
          logout:'Cerrar sesión', language:'Idioma', currency:'Moneda',
          languageChanged:'Idioma actualizado', currencyChanged:'Moneda actualizada',
          share:'Compartir', editProfile:'Editar perfil',
          qrGenerated:'QR generados', totalScans:'Escaneos totales',
          teamMembers:'Miembros del equipo', storage:'Almacenamiento',
          upgradeStorage:'Ampliar +100 MB',
          nav_general:'Información general', nav_security:'Seguridad y contraseña',
          nav_notifications:'Notificaciones', nav_storage:'Suscripción y pagos',
          nav_team:'Equipo', nav_activity:'Actividad', nav_danger:'Acciones avanzadas',
          nav_links:'Tarjeta de links', nav_studio:'Abrir QR Studio',
        },
        en: {
          openStudio:'Open QR Studio', openStudioDesc:'Create, manage and customize your QR codes',
          logout:'Sign out', language:'Language', currency:'Currency',
          languageChanged:'Language updated', currencyChanged:'Currency updated',
          share:'Share', editProfile:'Edit profile',
          qrGenerated:'QR codes generated', totalScans:'Total scans',
          teamMembers:'Team members', storage:'Storage', upgradeStorage:'Add +100 MB',
          nav_general:'General info', nav_security:'Security & password',
          nav_notifications:'Notifications', nav_storage:'Subscription & billing',
          nav_team:'Team', nav_activity:'Activity', nav_danger:'Danger zone',
          nav_links:'Link card', nav_studio:'Open QR Studio',
        },
        pt: {
          openStudio:'Abrir QR Studio', openStudioDesc:'Crie, gerencie e personalize seus códigos QR',
          logout:'Sair', language:'Idioma', currency:'Moeda',
          languageChanged:'Idioma atualizado', currencyChanged:'Moeda atualizada',
          share:'Partilhar', editProfile:'Editar perfil',
          qrGenerated:'QR gerados', totalScans:'Verificações totais',
          teamMembers:'Membros da equipa', storage:'Armazenamento',
          upgradeStorage:'Adicionar +100 MB',
          nav_general:'Informação geral', nav_security:'Segurança e palavra-passe',
          nav_notifications:'Notificações', nav_storage:'Subscrição e pagamentos',
          nav_team:'Equipa', nav_activity:'Atividade', nav_danger:'Zona de perigo',
          nav_links:'Cartão de links', nav_studio:'Abrir QR Studio',
        },
        fr: {
          openStudio:'Ouvrir QR Studio', openStudioDesc:'Créez, gérez et personnalisez vos codes QR',
          logout:'Déconnexion', language:'Langue', currency:'Devise',
          languageChanged:'Langue mise à jour', currencyChanged:'Devise mise à jour',
          share:'Partager', editProfile:'Modifier le profil',
          qrGenerated:'QR générés', totalScans:'Scans totaux',
          teamMembers:"Membres de l'équipe", storage:'Stockage',
          upgradeStorage:'Ajouter +100 Mo',
          nav_general:'Informations générales', nav_security:'Sécurité et mot de passe',
          nav_notifications:'Notifications', nav_storage:'Abonnement et paiements',
          nav_team:'Équipe', nav_activity:'Activité', nav_danger:'Zone dangereuse',
          nav_links:'Carte de liens', nav_studio:'Ouvrir QR Studio',
        },
        de: {
          openStudio:'QR Studio öffnen', openStudioDesc:'QR-Codes erstellen, verwalten und anpassen',
          logout:'Abmelden', language:'Sprache', currency:'Währung',
          languageChanged:'Sprache aktualisiert', currencyChanged:'Währung aktualisiert',
          share:'Teilen', editProfile:'Profil bearbeiten',
          qrGenerated:'Erzeugte QR', totalScans:'Gesamtscans',
          teamMembers:'Teammitglieder', storage:'Speicher', upgradeStorage:'+100 MB hinzufügen',
          nav_general:'Allgemeine Infos', nav_security:'Sicherheit und Passwort',
          nav_notifications:'Benachrichtigungen', nav_storage:'Abo und Zahlungen',
          nav_team:'Team', nav_activity:'Aktivität', nav_danger:'Gefahrenzone',
          nav_links:'Link-Karte', nav_studio:'QR Studio öffnen',
        },
      },

      security: { twoFA:true, newPassword:'' },
      storage: {
        used:0.04, total:0.1, selectedPack:100,
        get usedLabel(){ return this.used.toFixed(2)+' GB' },
        get totalLabel(){ return this.total.toFixed(1)+' GB' },
        get percent(){ return Math.min(100, Math.round((this.used/this.total)*100)) },
      },
      billing: {
        plan:'Pro', price:9.99, period:'mensual', nextBilling:'01 jun 2026',
        paymentMethod:{ brand:'Visa', last4:'4242', expires:'12/27' },
        features:['QR ilimitados','Estadísticas avanzadas','Marca personalizada','Soporte prioritario'],
      },
      storagePacks: [
        { size:100,  price:0.99, label:'Básico' },
        { size:500,  price:3.99, label:'Recomendado' },
        { size:1000, price:6.99, label:'Pro' },
      ],
      invoices: [
        { id:1, date:'01 may 2026', desc:'Plan Pro · Mensual', amount:9.99, status:'Pagado' },
        { id:2, date:'01 abr 2026', desc:'Plan Pro · Mensual', amount:9.99, status:'Pagado' },
        { id:3, date:'15 mar 2026', desc:'Ampliación 500 MB',  amount:3.99, status:'Pagado' },
        { id:4, date:'01 mar 2026', desc:'Plan Pro · Mensual', amount:9.99, status:'Pagado' },
      ],
      sessions: [
        { id:1, current:true,  device:'Chrome · Windows 11', location:'Madrid, ES', ip:'83.52.10.4', lastActive:'Ahora',
          icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>' },
        { id:2, current:false, device:'Safari · iPhone 15',   location:'Madrid, ES', ip:'83.52.10.4', lastActive:'hace 2 h',
          icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>' },
        { id:3, current:false, device:'Firefox · macOS',      location:'Barcelona, ES', ip:'95.122.0.7', lastActive:'hace 3 días',
          icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M2 20h20"/></svg>' },
      ],
      notifications: {
        qr_created:    { title:'QR generado',          desc:'Cuando se crea un nuevo código QR.',     email:true,  push:true  },
        qr_scanned:    { title:'Escaneo de QR',        desc:'Notifícame cuando alguien escanee un QR.', email:false, push:true  },
        billing:       { title:'Facturación',          desc:'Recibos, renovaciones y avisos de pago.', email:true,  push:false },
        security:      { title:'Alertas de seguridad', desc:'Inicios de sesión sospechosos o cambios.', email:true,  push:true  },
        product:       { title:'Novedades del producto', desc:'Nuevas funciones y actualizaciones.',  email:true,  push:false },
        marketing:     { title:'Marketing',            desc:'Ofertas, descuentos y promociones.',      email:false, push:false },
      },
      team: [
        { id:1, name:'Miguel Novoa',  email:'geometrymike20@gmail.com', initials:'MN', role:'Propietario', online:true,  lastActive:'En línea' },
        { id:2, name:'Laura Pérez',   email:'laura@blackink.es',        initials:'LP', role:'Editor',      online:true,  lastActive:'hace 12 min' },
        { id:3, name:'David Romero',  email:'david@blackink.es',        initials:'DR', role:'Visor',       online:false, lastActive:'hace 3 días' },
      ],
      activity: [
        { id:1, type:'qr',       title:'Nuevo QR generado',     detail:'d-tattoo.com/sesion-mayo · personalizado', time:'hace 12 min', date:'Hoy' },
        { id:2, type:'security', title:'Inicio de sesión',      detail:'Chrome · Windows 11 · Madrid, ES',         time:'hace 1 h',    date:'Hoy' },
        { id:3, type:'billing',  title:'Plan actualizado a Pro', detail:'Facturación €9.99/mes',                   time:'hace 1 día',  date:'Esta semana' },
        { id:4, type:'security', title:'Contraseña modificada', detail:'Cambio realizado desde ajustes',           time:'hace 4 días', date:'Esta semana' },
        { id:5, type:'account',  title:'Cuenta creada',         detail:'Bienvenido a dtattoos',                    time:'14 mar 2024', date:'Más antiguo' },
      ],
      activityIcons: {
        qr:       { color:'#b3252c', svg:'<path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z"/>' },
        security: { color:'#1a7a3e', svg:'<path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.5 1-3 3-3s3 1.5 3 3-1 2.5-3 3v2m0 3h.01M5 13a7 7 0 1014 0H5z"/>' },
        billing:  { color:'#1a1a1a', svg:'<rect x="2" y="5" width="20" height="14" rx="2" stroke-linejoin="round"/><line x1="2" y1="10" x2="22" y2="10" stroke-linecap="round"/>' },
        account:  { color:'#6b6b6b', svg:'<path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm10-3v6m3-3h-6"/>' },
      },
      activityFilter:'all',
      get filteredActivity(){
        if(this.activityFilter === 'all') return this.activity;
        return this.activity.filter(a => a.type === this.activityFilter);
      },
      get groupedActivity(){
        const groups = {};
        this.filteredActivity.forEach(a => { (groups[a.date] = groups[a.date] || []).push(a); });
        return groups;
      },

      navItems: [
        { id:'general',       icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0113 0"/></svg>' },
        { id:'security',      icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>' },
        { id:'storage',       icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>' },
        { id:'danger',        icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' },
        { id:'studio',        href:'/perfil/qr-studio', icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14h1v1h-1zM14 20h3v1h-3zM20 17h1v4M17 20h3"/></svg>' },
      ],
      get nav(){
        return this.navItems.map(it => ({ ...it, label: this.t('nav_' + it.id) }));
      },

      t(key){
        const dict = this.i18n[this.prefs.language] || this.i18n.es;
        return dict[key] ?? this.i18n.es[key] ?? key;
      },
      formatPrice(amount){
        const cur = this.currencies[this.prefs.currency] || this.currencies.EUR;
        const value = (Number(amount) * cur.rate).toFixed(cur.decimals);
        return cur.position === 'before' ? `${cur.symbol}${value}` : `${value} ${cur.symbol}`;
      },

      get initials(){
        return ((this.user.firstName?.[0] ?? '') + (this.user.lastName?.[0] ?? '')).toUpperCase();
      },
      pwScore(){
        const p = this.security.newPassword || '';
        let s = 0;
        if (p.length >= 8) s++;
        if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++;
        if (/\d/.test(p)) s++;
        if (/[^A-Za-z0-9]/.test(p) && p.length >= 12) s++;
        return s;
      },
      get pwLabel(){
        return ['Introduce una contraseña','Débil','Aceptable','Buena','Excelente'][this.pwScore()];
      },
      pwScoreColor(idx){
        const score = this.pwScore();
        if (idx >= score) return '#ebebeb';
        return ['#b3252c','#b27300','#1a1a1a','#1a7a3e'][score-1] || '#b3252c';
      },

      onAvatar(e){
        const file = e.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => this.user.avatar = ev.target.result;
        reader.readAsDataURL(file);
      },

      save(what){
        this.notify('Cambios guardados', what + ' se actualizó correctamente.');
      },
      notify(title, msg){
        this.toast = { show:true, title, msg };
        clearTimeout(this._t);
        this._t = setTimeout(() => this.toast.show = false, 2800);
      },
    }
  }
</script>
</body>
</html>
