<!DOCTYPE html>
<html lang="es" class="bg-[#0b0d12]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mi cuenta · dtattoos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { color-scheme: dark; }
        body {
            background:
                radial-gradient(1200px 600px at 80% -10%, rgba(180,0,0,0.28), transparent 60%),
                radial-gradient(900px 500px at -10% 20%, rgba(220,0,0,0.14), transparent 60%),
                #0b0d12;
        }
        .glass {
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
        }
        .glass-strong {
            background: linear-gradient(180deg, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.025) 100%);
            border: 1px solid rgba(255,255,255,0.10);
            backdrop-filter: blur(10px);
        }
        .input {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.10);
            transition: border-color .18s ease, box-shadow .18s ease;
            color: #e2e8f0;
        }
        .input::placeholder { color: #64748b; }
        .input:focus { border-color: #ff3333; box-shadow: 0 0 0 4px rgba(180,0,0,0.22); outline: none; }
        .btn-primary {
            background: linear-gradient(135deg, #b30000, #ff1a1a);
            box-shadow: 0 10px 30px -10px rgba(180,0,0,0.70);
            transition: all .18s ease;
        }
        .btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 14px 36px -10px rgba(180,0,0,0.85); }
        .btn-secondary {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            transition: all .18s ease;
            color: #e2e8f0;
        }
        .btn-secondary:hover:not(:disabled) { background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.20); }
        .btn-danger {
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.40);
            color: #fca5a5;
            transition: all .18s ease;
        }
        .btn-danger:hover { background: rgba(220, 38, 38, 0.22); border-color: rgba(220, 38, 38, 0.70); color: #fff; }
        .nav-item {
            display: flex; align-items: center; gap: .75rem;
            padding: .65rem .85rem;
            border-radius: .65rem;
            color: #94a3b8;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s ease;
            border: 1px solid transparent;
        }
        .nav-item:hover { background: rgba(255,255,255,0.04); color: #e2e8f0; }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(180,0,0,0.22), rgba(180,0,0,0.06));
            color: #fff;
            border-color: rgba(255,51,51,0.35);
            box-shadow: 0 4px 14px -6px rgba(180,0,0,0.55);
        }
        .nav-item .dot { width: 6px; height: 6px; border-radius: 9999px; background: #ef4444; opacity: 0; transition: opacity .15s; }
        .nav-item.active .dot { opacity: 1; }
        .label { display:block; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-bottom: .4rem; }
        .field { margin-bottom: 1rem; }
        .toggle {
            position: relative; width: 42px; height: 24px; border-radius: 9999px;
            background: rgba(255,255,255,0.10); transition: background .18s; cursor: pointer; flex-shrink: 0;
        }
        .toggle::after {
            content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 9999px;
            background: #f8fafc; transition: transform .2s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        }
        .toggle.on { background: linear-gradient(135deg, #b30000, #ff1a1a); }
        .toggle.on::after { transform: translateX(18px); }

        /* ======= NOTIFICACIONES ======= */
        .notif-channel-legend {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .3rem .65rem; border-radius: 9999px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            color: #94a3b8; font-size: .68rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .08em;
        }
        .notif-list { display: flex; flex-direction: column; gap: .5rem; }
        .notif-row {
            display: flex; align-items: center; justify-content: space-between; gap: 1.25rem;
            padding: .95rem 1.1rem;
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.012));
            border: 1px solid rgba(255,255,255,0.04);
            transition: background .2s ease, border-color .2s ease, transform .2s ease;
        }
        .notif-row:hover {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            border-color: rgba(255,255,255,0.08);
        }
        .notif-info { flex: 1; min-width: 0; }
        .notif-title { font-size: .9rem; font-weight: 600; color: #f1f5f9; line-height: 1.3; }
        .notif-desc { font-size: .78rem; color: #94a3b8; margin-top: .2rem; line-height: 1.45; }
        .notif-channels { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; }
        .notif-chip {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .42rem .7rem .42rem .65rem;
            border-radius: 9999px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            color: #64748b;
            font-size: .7rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
            cursor: pointer; user-select: none;
            transition: all .18s ease;
        }
        .notif-chip:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.10); color: #cbd5e1; }
        .notif-chip .notif-dot {
            width: 7px; height: 7px; border-radius: 9999px;
            background: rgba(255,255,255,0.18);
            transition: background .18s ease, box-shadow .18s ease;
            margin-left: .15rem;
        }
        .notif-chip.is-on {
            background: linear-gradient(135deg, rgba(179,0,0,0.18), rgba(255,26,26,0.14));
            border-color: rgba(255,26,26,0.45);
            color: #fecaca;
            box-shadow: 0 4px 14px -6px rgba(255,26,26,0.45);
        }
        .notif-chip.is-on .notif-dot {
            background: #ff4d4d;
            box-shadow: 0 0 0 3px rgba(255,77,77,0.18);
        }
        .notif-chip-label { line-height: 1; }
        @media (max-width: 640px) {
            .notif-row { flex-direction: column; align-items: flex-start; gap: .85rem; }
            .notif-channels { width: 100%; justify-content: flex-end; }
        }
        .progress-track { height: 8px; background: rgba(255,255,255,0.06); border-radius: 9999px; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #b30000, #ff1a1a); border-radius: 9999px; transition: width .4s ease; }
        .badge {
            display: inline-flex; align-items: center; gap: .35rem; padding: .15rem .55rem;
            border-radius: 9999px; font-size: .7rem; font-weight: 600; line-height: 1.4;
        }
        .badge-success { background: rgba(16,185,129,0.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.30); }
        .badge-warning { background: rgba(245,158,11,0.12); color: #fcd34d; border: 1px solid rgba(245,158,11,0.30); }
        .badge-info    { background: rgba(59,130,246,0.12); color: #93c5fd; border: 1px solid rgba(59,130,246,0.30); }
        .badge-danger  { background: rgba(220,38,38,0.12); color: #fca5a5; border: 1px solid rgba(220,38,38,0.30); }
        select.input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .9rem center; padding-right: 2.4rem; color-scheme: dark; }
        select.input option { background-color: #11141b; color: #e2e8f0; padding: .5rem; }
        select.input option:checked,
        select.input option:hover { background: linear-gradient(0deg, rgba(180,0,0,0.35), rgba(180,0,0,0.35)), #11141b; color: #fff; }
        select.input option:disabled { color: #475569; }
        .divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.10), transparent); margin: 1.5rem 0; }
        /* Espaciado vertical entre tarjetas (fallback por si Tailwind no lo trae) */
        .space-y-6 > * + * { margin-top: 1.5rem; }
        .space-y-4 > * + * { margin-top: 1rem; }
        .space-y-3 > * + * { margin-top: .75rem; }
        .space-y-2 > * + * { margin-top: .5rem; }

        /* Fallbacks de utilidades Tailwind que no están en el bundle compilado */
        .relative { position: relative; }
        .absolute { position: absolute; }
        .pr-24 { padding-right: 6rem; }
        .pr-12 { padding-right: 3rem; }
        .right-2 { right: .5rem; }
        .right-3 { right: .75rem; }
        .top-1\/2 { top: 50%; }
        .-translate-y-1\/2 { transform: translateY(-50%); }

        /* Grid responsive a partir de sm (640px) */
        @media (min-width: 640px) {
            .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sm\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .sm\:col-span-2 { grid-column: span 2 / span 2; }
        }

        /* ======= ALMACENAMIENTO ======= */
        .storage-stats {
            display: grid; gap: .75rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        @media (max-width: 640px) { .storage-stats { grid-template-columns: 1fr; } }
        .storage-stat {
            position: relative;
            padding: 1rem 1.1rem;
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.012));
            border: 1px solid rgba(255,255,255,0.05);
            display: flex; align-items: center; gap: .9rem;
        }
        .storage-stat-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: grid; place-items: center;
            background: rgba(255,26,26,0.10);
            color: #ff6b6b;
            border: 1px solid rgba(255,26,26,0.18);
            flex-shrink: 0;
        }
        .storage-stat-label { font-size: .68rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
        .storage-stat-value { font-size: 1.35rem; font-weight: 700; color: #f8fafc; line-height: 1.15; margin-top: .1rem; }

        .storage-progress-card {
            margin-top: 1.25rem;
            padding: 1rem 1.1rem;
            border-radius: 14px;
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .storage-progress-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .55rem; gap: 1rem; flex-wrap: wrap; }
        .storage-progress-title { font-size: .8rem; color: #cbd5e1; font-weight: 600; }
        .storage-progress-pct { font-size: .8rem; font-weight: 700; color: #fecaca; }
        .storage-track { height: 10px; background: rgba(255,255,255,0.05); border-radius: 9999px; overflow: hidden; position: relative; }
        .storage-bar { height: 100%; background: linear-gradient(90deg, #b30000, #ff1a1a, #ff5252); border-radius: 9999px; transition: width .5s ease; box-shadow: 0 0 14px -2px rgba(255,26,26,0.5); }

        .pack-grid {
            display: grid; gap: 1rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 1rem;
        }
        @media (max-width: 768px) { .pack-grid { grid-template-columns: 1fr; } }
        .pack-card {
            position: relative;
            padding: 1.25rem 1.25rem 1.1rem;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.06);
            cursor: pointer;
            transition: transform .2s ease, border-color .2s ease, background .2s ease, box-shadow .2s ease;
            overflow: hidden;
        }
        .pack-card:hover {
            border-color: rgba(255,255,255,0.14);
            transform: translateY(-2px);
        }
        .pack-card.is-selected {
            border-color: rgba(255,77,77,0.6);
            background: linear-gradient(180deg, rgba(255,26,26,0.10), rgba(255,26,26,0.02));
            box-shadow: 0 10px 30px -12px rgba(255,26,26,0.45), inset 0 0 0 1px rgba(255,77,77,0.15);
        }
        .pack-card.is-featured::before {
            content: 'Más popular';
            position: absolute; top: 10px; right: 10px;
            font-size: .6rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
            padding: .25rem .55rem; border-radius: 9999px;
            background: linear-gradient(135deg, #b30000, #ff1a1a);
            color: #fff;
            box-shadow: 0 4px 12px -4px rgba(255,26,26,0.6);
        }
        .pack-tier {
            display: inline-block;
            font-size: .65rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: .35rem;
        }
        .pack-card.is-selected .pack-tier { color: #fecaca; }
        .pack-size { font-size: 2rem; font-weight: 700; color: #f8fafc; line-height: 1; }
        .pack-size .pack-unit { font-size: .9rem; color: #94a3b8; font-weight: 500; margin-left: .25rem; }
        .pack-price { font-size: 1.1rem; font-weight: 700; color: #ff6b6b; margin-top: .65rem; }
        .pack-price .pack-price-suffix { font-size: .7rem; font-weight: 500; color: #94a3b8; margin-left: .25rem; }
        .pack-check {
            position: absolute; bottom: 12px; right: 12px;
            width: 22px; height: 22px; border-radius: 9999px;
            display: grid; place-items: center;
            background: rgba(255,77,77,0.15);
            border: 1px solid rgba(255,77,77,0.5);
            color: #ff6b6b;
            opacity: 0; transform: scale(.6); transition: all .2s ease;
        }
        .pack-card.is-selected .pack-check { opacity: 1; transform: scale(1); }

        /* ======= FACTURACIÓN ======= */
        .billing-summary {
            display: grid; gap: 1rem;
            grid-template-columns: 1.4fr 1fr;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) { .billing-summary { grid-template-columns: 1fr; } }
        .billing-plan-card {
            position: relative; overflow: hidden;
            padding: 1.4rem 1.5rem;
            border-radius: 16px;
            background:
                radial-gradient(120% 140% at 100% 0%, rgba(255,26,26,0.18), transparent 55%),
                linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.012));
            border: 1px solid rgba(255,77,77,0.25);
        }
        .billing-plan-card::after {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(60% 80% at 90% 110%, rgba(255,26,26,0.12), transparent 70%);
        }
        .billing-plan-head { display: flex; align-items: center; gap: .65rem; margin-bottom: .35rem; position: relative; }
        .billing-plan-tier { font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #fecaca; }
        .billing-plan-name { font-size: 1.7rem; font-weight: 700; color: #f8fafc; line-height: 1.1; position: relative; }
        .billing-plan-price { display: flex; align-items: baseline; gap: .35rem; margin-top: .65rem; position: relative; }
        .billing-plan-price .amount { font-size: 1.55rem; font-weight: 700; color: #ff6b6b; }
        .billing-plan-price .period { font-size: .8rem; color: #94a3b8; }
        .billing-plan-features { margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: .4rem .9rem; position: relative; }
        @media (max-width: 480px) { .billing-plan-features { grid-template-columns: 1fr; } }
        .billing-feature { display: flex; align-items: center; gap: .45rem; font-size: .8rem; color: #cbd5e1; }
        .billing-feature svg { width: 14px; height: 14px; color: #4ade80; flex-shrink: 0; }

        .billing-side { display: flex; flex-direction: column; gap: .75rem; justify-content: space-between; }
        .billing-side-card {
            padding: .95rem 1.1rem;
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.05);
            display: flex; align-items: center; gap: .85rem;
        }
        .billing-side-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: grid; place-items: center;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            color: #cbd5e1; flex-shrink: 0;
        }
        .billing-side-label { font-size: .65rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
        .billing-side-value { font-size: .9rem; font-weight: 600; color: #f1f5f9; margin-top: .15rem; line-height: 1.25; }
        .billing-side-sub { font-size: .7rem; color: #64748b; margin-top: .1rem; }

        .invoices-head {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            margin-bottom: .85rem; flex-wrap: wrap;
        }
        .invoices-title { font-size: .95rem; font-weight: 600; color: #f1f5f9; }
        .invoices-sub { font-size: .75rem; color: #94a3b8; margin-top: .15rem; }

        .invoice-list { display: flex; flex-direction: column; gap: .5rem; }
        .invoice-row {
            display: grid;
            grid-template-columns: 110px 1fr auto auto auto;
            align-items: center; gap: 1rem;
            padding: .85rem 1.1rem;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.008));
            border: 1px solid rgba(255,255,255,0.04);
            transition: background .2s ease, border-color .2s ease;
        }
        .invoice-row:hover { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.08); }
        @media (max-width: 640px) {
            .invoice-row { grid-template-columns: 1fr auto; grid-template-areas: 'date amount' 'desc desc' 'status action'; gap: .5rem .75rem; }
            .invoice-row .ic-date   { grid-area: date; }
            .invoice-row .ic-desc   { grid-area: desc; }
            .invoice-row .ic-amount { grid-area: amount; text-align: right; }
            .invoice-row .ic-status { grid-area: status; }
            .invoice-row .ic-action { grid-area: action; justify-self: end; }
        }
        .ic-date { font-size: .78rem; color: #94a3b8; font-weight: 500; }
        .ic-desc { font-size: .85rem; color: #f1f5f9; font-weight: 500; }
        .ic-amount { font-size: .9rem; font-weight: 700; color: #f8fafc; }
        .ic-action {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .4rem .7rem; border-radius: 8px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            color: #cbd5e1; font-size: .72rem; font-weight: 600;
            cursor: pointer; transition: all .18s ease;
        }
        .ic-action:hover { background: rgba(255,26,26,0.12); border-color: rgba(255,26,26,0.4); color: #fecaca; }

        /* ======= EQUIPO ======= */
        .team-meta { display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; margin-bottom: 1.25rem; padding-bottom: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .team-meta-item { display: flex; align-items: center; gap: .55rem; font-size: .8rem; color: #cbd5e1; }
        .team-meta-item strong { color: #f8fafc; font-weight: 700; font-size: .9rem; }
        .team-meta-icon { width: 28px; height: 28px; border-radius: 8px; display: grid; place-items: center; background: rgba(255,26,26,0.1); border: 1px solid rgba(255,26,26,0.18); color: #ff6b6b; }

        .team-list { display: flex; flex-direction: column; gap: .55rem; }
        .team-card {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: center; gap: 1rem;
            padding: .9rem 1.1rem;
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.008));
            border: 1px solid rgba(255,255,255,0.04);
            transition: background .2s ease, border-color .2s ease;
        }
        .team-card:hover { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.08); }
        @media (max-width: 640px) {
            .team-card { grid-template-columns: auto 1fr; grid-template-areas: 'avatar info' 'role role' 'actions actions'; gap: .65rem .85rem; }
            .tc-avatar { grid-area: avatar; }
            .tc-info { grid-area: info; }
            .tc-role { grid-area: role; justify-self: start; }
            .tc-actions { grid-area: actions; justify-self: end; }
        }

        .tc-avatar { position: relative; width: 42px; height: 42px; border-radius: 9999px; display: grid; place-items: center; color: #fff; font-size: .8rem; font-weight: 700; flex-shrink: 0; box-shadow: 0 4px 14px -6px rgba(0,0,0,0.6); }
        .tc-avatar.role-Propietario { background: linear-gradient(135deg, #b30000, #ff1a1a); }
        .tc-avatar.role-Editor      { background: linear-gradient(135deg, #6d28d9, #8b5cf6); }
        .tc-avatar.role-Visor       { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        .tc-status-dot { position: absolute; bottom: -1px; right: -1px; width: 11px; height: 11px; border-radius: 9999px; border: 2px solid #11141b; }
        .tc-status-dot.is-online  { background: #4ade80; box-shadow: 0 0 0 2px rgba(74,222,128,0.25); }
        .tc-status-dot.is-offline { background: #475569; }

        .tc-info { min-width: 0; }
        .tc-name { display: flex; align-items: center; gap: .5rem; font-size: .9rem; font-weight: 600; color: #f1f5f9; }
        .tc-you-tag { font-size: .6rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #fecaca; background: rgba(255,26,26,0.12); border: 1px solid rgba(255,26,26,0.3); padding: .12rem .4rem; border-radius: 6px; }
        .tc-meta { display: flex; align-items: center; gap: .5rem; font-size: .75rem; color: #94a3b8; margin-top: .15rem; }
        .tc-meta-sep { width: 3px; height: 3px; border-radius: 9999px; background: #475569; }

        .role-pill {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .3rem .65rem; border-radius: 9999px;
            font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
            border: 1px solid transparent;
        }
        .role-pill.role-Propietario { background: rgba(255,26,26,0.10); color: #fecaca; border-color: rgba(255,26,26,0.35); }
        .role-pill.role-Editor      { background: rgba(139,92,246,0.10); color: #ddd6fe; border-color: rgba(139,92,246,0.35); }
        .role-pill.role-Visor       { background: rgba(59,130,246,0.10); color: #bfdbfe; border-color: rgba(59,130,246,0.35); }

        .tc-actions { display: flex; align-items: center; gap: .35rem; }
        .tc-icon-btn {
            width: 32px; height: 32px; border-radius: 8px;
            display: grid; place-items: center;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            color: #94a3b8; cursor: pointer;
            transition: all .18s ease;
        }
        .tc-icon-btn:hover { background: rgba(255,255,255,0.06); color: #f1f5f9; border-color: rgba(255,255,255,0.12); }
        .tc-icon-btn.danger:hover { background: rgba(255,26,26,0.12); color: #fecaca; border-color: rgba(255,26,26,0.4); }

        /* ======= ACTIVIDAD ======= */
        .activity-filters { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; margin-bottom: 1.25rem; padding-bottom: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .activity-filter {
            padding: .4rem .85rem; border-radius: 9999px;
            font-size: .72rem; font-weight: 600; letter-spacing: .03em;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
            color: #94a3b8; cursor: pointer; transition: all .18s ease;
        }
        .activity-filter:hover { color: #f1f5f9; border-color: rgba(255,255,255,0.12); background: rgba(255,255,255,0.05); }
        .activity-filter.is-active {
            background: linear-gradient(135deg, rgba(179,0,0,0.22), rgba(255,26,26,0.16));
            border-color: rgba(255,77,77,0.45); color: #fecaca;
            box-shadow: 0 4px 14px -6px rgba(255,26,26,0.4);
        }

        .activity-group { margin-bottom: 1.5rem; }
        .activity-group:last-child { margin-bottom: 0; }
        .activity-group-label {
            font-size: .68rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
            color: #64748b; margin-bottom: .65rem; padding-left: .25rem;
        }

        .activity-timeline { position: relative; padding-left: 2.25rem; }
        .activity-timeline::before {
            content: ''; position: absolute; left: 18px; top: 14px; bottom: 14px;
            width: 1px; background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.02));
        }
        .activity-item { position: relative; margin-bottom: .55rem; }
        .activity-item:last-child { margin-bottom: 0; }

        .activity-icon {
            position: absolute; left: -2.25rem; top: 50%; transform: translateY(-50%);
            width: 36px; height: 36px; border-radius: 10px;
            display: grid; place-items: center;
            background-color: #11141b;
            background-image: linear-gradient(var(--act-bg, rgba(255,255,255,0.04)), var(--act-bg, rgba(255,255,255,0.04)));
            border: 1px solid var(--act-border, rgba(255,255,255,0.08));
            color: var(--act-color, #cbd5e1);
        }
        .activity-icon svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; }

        .activity-card {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: .85rem 1.1rem;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.008));
            border: 1px solid rgba(255,255,255,0.04);
            transition: background .2s ease, border-color .2s ease, transform .2s ease;
        }
        .activity-card:hover { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.08); transform: translateX(2px); }
        .activity-content { min-width: 0; flex: 1; }
        .activity-title { font-size: .88rem; font-weight: 600; color: #f1f5f9; line-height: 1.3; }
        .activity-detail { font-size: .76rem; color: #94a3b8; margin-top: .2rem; line-height: 1.4; }
        .activity-time {
            font-size: .68rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
            color: #64748b; white-space: nowrap; flex-shrink: 0;
            padding: .25rem .55rem; border-radius: 9999px;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
        }
        @media (max-width: 480px) {
            .activity-card { flex-direction: column; align-items: flex-start; gap: .55rem; }
            .activity-time { align-self: flex-start; }
        }

        /* ======= ACCIONES AVANZADAS ======= */
        .danger-panel {
            position: relative; overflow: hidden;
            border-radius: 16px;
            padding: 1.75rem 1.75rem 1.5rem;
            background:
                radial-gradient(120% 90% at 0% 0%, rgba(255,26,26,0.10), transparent 60%),
                linear-gradient(180deg, rgba(20,8,10,0.6), rgba(11,13,18,0.4));
            border: 1px solid rgba(255,77,77,0.22);
        }
        .danger-panel::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, #ff1a1a 20%, #b30000 50%, #ff1a1a 80%, transparent);
            opacity: .55;
        }
        .danger-head { display: flex; align-items: flex-start; gap: .85rem; margin-bottom: 1.5rem; }
        .danger-head-icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: grid; place-items: center; flex-shrink: 0;
            background: rgba(255,26,26,0.12);
            border: 1px solid rgba(255,26,26,0.35);
            color: #ff6b6b;
        }
        .danger-head h2 { font-size: 1.15rem; font-weight: 700; color: #fecaca; line-height: 1.2; }
        .danger-head p { font-size: .82rem; color: #94a3b8; margin-top: .2rem; line-height: 1.45; }

        .danger-list { display: flex; flex-direction: column; gap: .65rem; }
        .danger-row {
            display: flex; align-items: center; justify-content: space-between; gap: 1.25rem;
            padding: 1rem 1.15rem;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.008));
            border: 1px solid rgba(255,255,255,0.05);
            transition: background .2s ease, border-color .2s ease;
        }
        .danger-row:hover { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.10); }
        .danger-row.is-critical {
            background:
                radial-gradient(120% 100% at 100% 50%, rgba(255,26,26,0.10), transparent 60%),
                linear-gradient(180deg, rgba(255,26,26,0.06), rgba(255,26,26,0.02));
            border-color: rgba(255,77,77,0.45);
        }
        .danger-row.is-critical:hover { border-color: rgba(255,77,77,0.65); box-shadow: 0 6px 20px -8px rgba(255,26,26,0.4); }

        .danger-row-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: grid; place-items: center; flex-shrink: 0;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            color: #94a3b8;
        }
        .danger-row.is-critical .danger-row-icon {
            background: rgba(255,26,26,0.15);
            border-color: rgba(255,77,77,0.5);
            color: #ff6b6b;
        }
        .danger-row-info { flex: 1; min-width: 0; display: flex; gap: .85rem; align-items: center; }
        .danger-row-text { min-width: 0; }
        .danger-row-title { font-size: .9rem; font-weight: 600; color: #f1f5f9; line-height: 1.3; }
        .danger-row.is-critical .danger-row-title { color: #fecaca; }
        .danger-row-desc { font-size: .77rem; color: #94a3b8; margin-top: .2rem; line-height: 1.45; }

        .btn-danger-outline {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .55rem 1rem; border-radius: 10px;
            background: rgba(255,26,26,0.1);
            border: 1px solid rgba(255,77,77,0.5);
            color: #fecaca; font-size: .8rem; font-weight: 600;
            cursor: pointer; transition: all .18s ease; white-space: nowrap;
        }
        .btn-danger-outline:hover {
            background: linear-gradient(135deg, #b30000, #ff1a1a);
            border-color: transparent; color: #fff;
            box-shadow: 0 6px 18px -6px rgba(255,26,26,0.55);
        }
        @media (max-width: 640px) {
            .danger-row { flex-direction: column; align-items: flex-start; gap: .85rem; }
            .danger-row .btn-danger-outline, .danger-row .btn-secondary { align-self: stretch; justify-content: center; }
        }

        /* Gaps de grid/flex */
        .gap-x-5 { column-gap: 1.25rem; }
        .gap-y-4 { row-gap: 1rem; }
        .gap-2 { gap: .5rem; }
        .gap-3 { gap: .75rem; }
        .gap-4 { gap: 1rem; }
        .gap-5 { gap: 1.25rem; }
        .gap-6 { gap: 1.5rem; }
        [x-cloak] { display: none !important; }
        .fade-enter { animation: fade .25s ease; }
        @keyframes fade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        /* Tabs horizontales */
        .tabs-bar {
            display: flex; align-items: center; gap: .25rem;
            padding: .35rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .tabs-bar::-webkit-scrollbar { display: none; }

        /* Botones del top-bar (idioma / moneda / cerrar sesión) */
        .topbar-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem .85rem;
            border-radius: 9999px;
            font-size: .8rem; font-weight: 500;
            color: #cbd5e1;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            cursor: pointer;
            transition: all .18s ease;
        }
        .topbar-btn:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.20); color: #fff; }
        .popover {
            position: absolute; top: calc(100% + .5rem); right: 0;
            min-width: 180px;
            background: #11141b;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: .8rem;
            box-shadow: 0 18px 40px -14px rgba(0,0,0,.6);
            padding: .35rem;
            z-index: 60;
        }
        .popover-item {
            display: flex; align-items: center; gap: .55rem;
            padding: .55rem .7rem;
            border-radius: .55rem;
            font-size: .82rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: background .15s;
        }
        .popover-item:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .popover-item.active { background: linear-gradient(135deg, rgba(180,0,0,0.18), rgba(180,0,0,0.05)); color: #fff; }
        .popover-item .flag { font-size: 1rem; line-height: 1; }
        .tab-btn {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .55rem .9rem;
            border-radius: 10px;
            color: #94a3b8;
            font-size: .82rem;
            font-weight: 500;
            white-space: nowrap;
            cursor: pointer;
            border: 1px solid transparent;
            background: transparent;
            transition: all .15s ease;
            flex-shrink: 0;
        }
        .tab-btn:hover { color: #e2e8f0; background: rgba(255,255,255,0.04); }
        .tab-btn .tab-icon { width: 16px; height: 16px; flex-shrink: 0; }
        .tab-btn.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(180,0,0,0.26), rgba(180,0,0,0.08));
            border-color: rgba(255,51,51,0.35);
            box-shadow: 0 4px 14px -6px rgba(180,0,0,0.55), inset 0 0 0 1px rgba(255,255,255,0.04);
        }
        .tab-btn.danger { color: #fca5a5; }
        .tab-btn.danger.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(220,38,38,0.30), rgba(220,38,38,0.10));
            border-color: rgba(220,38,38,0.55);
        }
    </style>
</head>
<body class="min-h-screen text-slate-200 antialiased font-sans"
      x-data="profilePanel()" x-cloak>

{{-- Top bar: selector idioma + selector moneda + cerrar sesión --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="display:flex; align-items:center; justify-content:flex-end; gap:1rem; padding-top:1.75rem; padding-bottom:1.5rem; flex-wrap:wrap;">
    <div style="display:flex; align-items:center; gap:.55rem; flex-wrap:wrap;">
        {{-- Selector de idioma --}}
        <div style="position:relative;" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" class="topbar-btn" @click="open = !open" :title="t('language')">
                <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                <span class="flag" x-text="languages[prefs.language].flag"></span>
                <span x-text="languages[prefs.language].code"></span>
                <svg style="width:12px;height:12px;opacity:.7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="open" x-transition class="popover fade-enter" x-cloak>
                <template x-for="(lang, code) in languages" :key="code">
                    <div class="popover-item" :class="{ active: prefs.language === code }"
                         @click="prefs.language = code; open = false; notify(t('languageChanged'), lang.label)">
                        <span class="flag" x-text="lang.flag"></span>
                        <span class="flex-1" x-text="lang.label"></span>
                        <svg x-show="prefs.language === code" style="width:14px;height:14px;color:#10b981" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </template>
            </div>
        </div>

        {{-- Selector de moneda --}}
        <div style="position:relative;" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" class="topbar-btn" @click="open = !open" :title="t('currency')">
                <span style="font-weight:700; font-size:.95rem; line-height:1;" x-text="currencies[prefs.currency].symbol"></span>
                <span x-text="prefs.currency"></span>
                <svg style="width:12px;height:12px;opacity:.7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="open" x-transition class="popover fade-enter" x-cloak>
                <template x-for="(cur, code) in currencies" :key="code">
                    <div class="popover-item" :class="{ active: prefs.currency === code }"
                         @click="prefs.currency = code; open = false; notify(t('currencyChanged'), cur.label)">
                        <span style="font-weight:700; width:1.2rem; text-align:center;" x-text="cur.symbol"></span>
                        <span class="flex-1" x-text="cur.label"></span>
                        <svg x-show="prefs.currency === code" style="width:14px;height:14px;color:#10b981" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </template>
            </div>
        </div>

        {{-- Cerrar sesión --}}
        <button type="button"
                style="display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:9999px; font-size:.8rem; font-weight:500; color:#fca5a5; background:rgba(220,38,38,0.08); border:1px solid rgba(220,38,38,0.30); transition:all .18s ease; cursor:pointer;"
                onmouseover="this.style.background='rgba(220,38,38,0.18)';this.style.borderColor='rgba(220,38,38,0.60)';this.style.color='#fff'"
                onmouseout="this.style.background='rgba(220,38,38,0.08)';this.style.borderColor='rgba(220,38,38,0.30)';this.style.color='#fca5a5'">
            <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span x-text="t('logout')"></span>
        </button>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="padding-top:.25rem; padding-bottom:2.5rem;">

    {{-- HERO ------------------------------------------------------------- --}}
    <section class="glass rounded-2xl mb-8 relative overflow-hidden" style="padding: 2rem 2rem;">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(700px 260px at 85% -20%, rgba(220,30,30,0.22), transparent 60%); opacity:.85;"></div>

        <div class="relative" style="display:flex; flex-direction:column; gap:1.75rem;">

            {{-- Fila superior: avatar + identidad + acciones --}}
            <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
                {{-- Avatar --}}
                <div style="position:relative; width:96px; height:96px; flex-shrink:0;">
                    <div style="width:96px; height:96px; border-radius:20px; background:linear-gradient(135deg,#b30000,#7a0000); display:grid; place-items:center; color:#fff; font-size:2rem; font-weight:700; box-shadow:0 18px 40px -14px rgba(180,0,0,.55); border:1px solid rgba(255,255,255,.12); overflow:hidden;">
                        <template x-if="!user.avatar"><span x-text="initials"></span></template>
                        <template x-if="user.avatar"><img :src="user.avatar" alt="avatar" style="width:100%;height:100%;object-fit:cover;"></template>
                    </div>

                    {{-- Botón cámara: pegado abajo-derecha del avatar, mismo tamaño que un fab pequeño --}}
                    <label title="Cambiar foto de perfil"
                           style="position:absolute; right:-6px; bottom:-6px; width:32px; height:32px; border-radius:9999px; background:#0b0d12; border:1px solid rgba(255,255,255,.18); display:grid; place-items:center; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,.45); transition:all .18s ease;"
                           onmouseover="this.style.background='#1a1d24';this.style.borderColor='rgba(244,63,94,.5)'"
                           onmouseout="this.style.background='#0b0d12';this.style.borderColor='rgba(255,255,255,.18)'">
                        <svg style="width:14px;height:14px;color:#cbd5e1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <input type="file" accept="image/*" @change="onAvatar($event)" style="display:none">
                    </label>
                </div>

                {{-- Identidad --}}
                <div style="flex:1 1 280px; min-width:0;">
                    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-bottom:.5rem;">
                        <h1 style="font-size:1.75rem; font-weight:600; color:#fff; letter-spacing:-.01em; line-height:1.15;" x-text="user.firstName + ' ' + user.lastName"></h1>
                        <span x-show="user.email && user.email === user.verifiedEmail" class="badge badge-success">
                            <svg style="width:12px;height:12px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            Verificado
                        </span>
                        <span x-show="user.email !== user.verifiedEmail" class="badge badge-warning">
                            <svg style="width:12px;height:12px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Sin verificar
                        </span>
                        <span class="badge badge-info">Plan <span style="font-weight:700; margin-left:2px" x-text="billing.plan"></span></span>
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:.35rem 1.25rem; font-size:.825rem; color:#94a3b8;">
                        <span style="display:inline-flex; align-items:center; gap:.4rem;">
                            <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <span x-text="user.email"></span>
                        </span>
                        <span style="display:inline-flex; align-items:center; gap:.4rem;">
                            <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Madrid, España
                        </span>
                        <span style="display:inline-flex; align-items:center; gap:.4rem;">
                            <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Miembro desde mar 2024
                        </span>
                    </div>
                </div>

                {{-- Acciones rápidas --}}
                <div style="display:flex; gap:.5rem; flex-shrink:0; flex-wrap:wrap;">
                    <a href="{{ route('qr.create') }}" class="btn-secondary" style="padding:.55rem 1rem; border-radius:.6rem; font-size:.8rem; display:inline-flex; align-items:center; gap:.45rem; text-decoration:none;" :title="t('openStudioDesc')">
                        <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14h1v1h-1zM14 20h3v1h-3zM20 17h1v4M17 20h3"/></svg>
                        <span x-text="t('openStudio')"></span>
                    </a>
                    <button class="btn-secondary" style="padding:.55rem 1rem; border-radius:.6rem; font-size:.8rem; display:inline-flex; align-items:center; gap:.4rem;">
                        <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                        <span x-text="t('share')"></span>
                    </button>
                    <button @click="tab='general'" class="btn-primary" style="padding:.55rem 1rem; border-radius:.6rem; color:#fff; font-size:.8rem; display:inline-flex; align-items:center; gap:.4rem;">
                        <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span x-text="t('editProfile')"></span>
                    </button>
                </div>
            </div>

            {{-- Fila inferior: estadísticas rápidas --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:.75rem;">
                <div class="glass-strong" style="border-radius:.85rem; padding:.95rem 1.1rem;">
                    <p style="font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8;" x-text="t('qrGenerated')"></p>
                    <p style="font-size:1.4rem; font-weight:600; color:#fff; margin-top:.15rem;">47</p>
                </div>
                <div class="glass-strong" style="border-radius:.85rem; padding:.95rem 1.1rem;">
                    <p style="font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8;" x-text="t('totalScans')"></p>
                    <p style="font-size:1.4rem; font-weight:600; color:#fff; margin-top:.15rem;">1.284</p>
                </div>
                <div class="glass-strong" style="border-radius:.85rem; padding:.95rem 1.1rem;">
                    <p style="font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8;" x-text="t('teamMembers')"></p>
                    <p style="font-size:1.4rem; font-weight:600; color:#fff; margin-top:.15rem;">3</p>
                </div>
                <div class="glass-strong" style="border-radius:.85rem; padding:.95rem 1.1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.4rem;">
                        <p style="font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8;" x-text="t('storage')"></p>
                        <span style="font-size:.68rem; color:#64748b;"><span x-text="storage.usedLabel"></span> / <span x-text="storage.totalLabel"></span></span>
                    </div>
                    <div class="progress-track" style="height:6px;">
                        <div class="progress-bar" :style="`width: ${storage.percent}%`"></div>
                    </div>
                    <button @click="openUpgrade = true" style="margin-top:.55rem; font-size:.7rem; color:#fb7185; font-weight:600; background:none; border:0; padding:0; cursor:pointer;">
                        <span x-text="t('upgradeStorage')"></span> · <span x-text="formatPrice(0.99)"></span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Tabs horizontales --}}
    <nav class="tabs-bar" style="margin-bottom:1.5rem;">
        <template x-for="item in nav" :key="item.id">
            <button type="button" class="tab-btn" :class="{ active: tab === item.id, danger: item.id === 'danger' }" @click="tab = item.id">
                <span class="tab-icon" x-html="item.icon"></span>
                <span x-text="item.label"></span>
            </button>
        </template>
    </nav>

    {{-- Contenido --}}
    <div>
        <section class="space-y-6">

            {{-- =========================================================
                 GENERAL
            ========================================================= --}}
            <div x-show="tab === 'general'" class="fade-enter space-y-6">
                {{-- Personal --}}
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <div style="display:flex; align-items:center; gap:.6rem;">
                                <h2 class="text-xl font-semibold text-white">Información personal</h2>
                                <span class="badge badge-info" title="Privado" aria-label="Privado" style="display:inline-flex; align-items:center; justify-content:center; padding:.35rem;">
                                    <svg style="width:13px;height:13px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                </span>
                            </div>
                            <p class="text-sm text-slate-400 mt-1">Gestiona los datos básicos de tu cuenta.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                        <div class="field">
                            <label class="label">Nombre</label>
                            <input type="text" class="input w-full px-4 py-2.5 rounded-lg" x-model="user.firstName" placeholder="Tu nombre">
                        </div>
                        <div class="field">
                            <label class="label">Apellidos</label>
                            <input type="text" class="input w-full px-4 py-2.5 rounded-lg" x-model="user.lastName" placeholder="Tus apellidos">
                        </div>
                        <div class="field">
                            <label class="label">Fecha de nacimiento</label>
                            <input type="date" class="input w-full px-4 py-2.5 rounded-lg" x-model="user.birthdate">
                        </div>
                        <div class="field">
                            <label class="label">Género</label>
                            <select class="input w-full px-4 py-2.5 rounded-lg" x-model="user.gender">
                                <option value="">Prefiero no decirlo</option>
                                <option value="m">Masculino</option>
                                <option value="f">Femenino</option>
                                <option value="o">Otro</option>
                            </select>
                        </div>
                        <div class="field sm:col-span-2">
                            <label class="label">Correo electrónico <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <input type="email" class="input w-full px-4 py-2.5 rounded-lg pr-24" x-model="user.email">
                                <span x-show="user.email && user.email === user.verifiedEmail" class="badge badge-success absolute right-2 top-1/2 -translate-y-1/2" title="Verificado" aria-label="Verificado" style="display:inline-flex; align-items:center; justify-content:center; padding:.4rem;">
                                    <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                <button x-show="user.email !== user.verifiedEmail" type="button" @click="notify('Verificación enviada', 'Revisa tu bandeja de entrada')" class="badge badge-warning absolute right-2 top-1/2 -translate-y-1/2" title="Enviar correo de verificación" style="display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .55rem; cursor:pointer; border:0;">
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                    Verificar
                                </button>
                            </div>
                        </div>
                        <div class="field sm:col-span-2">
                            <label class="label">Número de teléfono</label>
                            <template x-for="(phone, idx) in user.phones" :key="idx">
                                <div class="flex items-center gap-2 mb-2">
                                    <select class="input px-3 py-2.5 rounded-lg w-28" x-model="phone.code">
                                        <option value="+34">+34 ES</option>
                                        <option value="+1">+1 US</option>
                                        <option value="+44">+44 UK</option>
                                        <option value="+52">+52 MX</option>
                                        <option value="+54">+54 AR</option>
                                    </select>
                                    <input type="tel" class="input flex-1 px-4 py-2.5 rounded-lg" x-model="phone.number" placeholder="600 000 000">
                                    <button @click="user.phones.splice(idx,1)" class="p-2.5 rounded-lg btn-secondary" title="Eliminar">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6M10 11v6M14 11v6"/></svg>
                                    </button>
                                </div>
                            </template>
                            <button @click="user.phones.push({code:'+34', number:''})" class="inline-flex items-center gap-1.5 text-sm text-rose-400 hover:text-rose-300 font-medium mt-1">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Agregar teléfono
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-2">
                        <button class="btn-secondary px-5 py-2.5 rounded-lg text-sm font-medium">Cancelar</button>
                        <button @click="save('Información personal')" class="btn-primary px-5 py-2.5 rounded-lg text-white text-sm font-medium">Guardar cambios</button>
                    </div>
                </div>

                {{-- Empresa (opcional) --}}
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="mb-6" style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                        <div style="flex:1 1 280px; min-width:0;">
                            <div style="display:flex; align-items:center; gap:.6rem; flex-wrap:wrap;">
                                <h2 class="text-xl font-semibold text-white">Información corporativa</h2>
                                <span class="badge" style="background:rgba(148,163,184,0.10); color:#94a3b8; border:1px solid rgba(148,163,184,0.25);">Opcional</span>
                            </div>
                            <p class="text-sm text-slate-400 mt-1">Solo si eres profesional o emites facturas a nombre de tu estudio o empresa.</p>
                        </div>
                        <label style="display:inline-flex; align-items:center; gap:.6rem; cursor:pointer; flex-shrink:0;">
                            <span style="font-size:.82rem; color:#cbd5e1; font-weight:500;">Soy profesional</span>
                            <span class="toggle" :class="{ on: company.isProfessional }" @click="company.isProfessional = !company.isProfessional"></span>
                        </label>
                    </div>

                    {{-- Estado: particular --}}
                    <div x-show="!company.isProfessional" class="fade-enter"
                         style="text-align:center; padding:2rem 1rem; border:1px dashed rgba(255,255,255,0.10); border-radius:1rem; background:rgba(255,255,255,0.02);">
                        <div style="display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; border-radius:9999px; background:rgba(148,163,184,0.10); margin-bottom:.75rem;">
                            <svg style="width:22px;height:22px;color:#94a3b8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <p style="font-size:.9rem; color:#cbd5e1; font-weight:500;">Cuenta personal</p>
                        <p style="font-size:.8rem; color:#94a3b8; margin-top:.25rem; max-width:420px; margin-left:auto; margin-right:auto;">No necesitas rellenar datos de empresa. Activa el interruptor si más adelante quieres facturar como profesional.</p>
                    </div>

                    {{-- Estado: profesional --}}
                    <div x-show="company.isProfessional" class="fade-enter">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                            <div class="field">
                                <label class="label">Nombre de la empresa</label>
                                <input type="text" class="input w-full px-4 py-2.5 rounded-lg" x-model="company.name" placeholder="Black Ink Studio">
                            </div>
                            <div class="field">
                                <label class="label">Categoría</label>
                                <select class="input w-full px-4 py-2.5 rounded-lg" x-model="company.category">
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
                                <input type="email" class="input w-full px-4 py-2.5 rounded-lg" x-model="company.email" placeholder="contacto@empresa.com">
                            </div>
                            <div class="field">
                                <label class="label">Sitio web</label>
                                <input type="url" class="input w-full px-4 py-2.5 rounded-lg" x-model="company.website" placeholder="https://...">
                            </div>
                            <div class="field sm:col-span-2">
                                <label class="label">Dirección fiscal</label>
                                <input type="text" class="input w-full px-4 py-2.5 rounded-lg" x-model="company.address" placeholder="Calle, número, ciudad, provincia, código postal, país">
                            </div>
                            <div class="field">
                                <label class="label">CIF / NIF</label>
                                <input type="text" class="input w-full px-4 py-2.5 rounded-lg" x-model="company.taxId" placeholder="B12345678">
                            </div>
                            <div class="field">
                                <label class="label">Impuesto VAT</label>
                                <input type="text" class="input w-full px-4 py-2.5 rounded-lg" x-model="company.vat" placeholder="ESB12345678">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-2">
                            <button class="btn-secondary px-5 py-2.5 rounded-lg text-sm font-medium">Cancelar</button>
                            <button @click="save('Datos de empresa')" class="btn-primary px-5 py-2.5 rounded-lg text-white text-sm font-medium">Guardar cambios</button>
                        </div>
                    </div>
                </div>

                {{-- Idioma y región se gestiona desde el selector global del top-bar --}}
            </div>

            {{-- =========================================================
                 SEGURIDAD
            ========================================================= --}}
            <div x-show="tab === 'security'" class="fade-enter space-y-6">
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <h2 class="text-xl font-semibold text-white">Cambiar contraseña</h2>
                    <p class="text-sm text-slate-400 mt-1 mb-6">Usa al menos 12 caracteres con letras, números y símbolos.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4 max-w-2xl">
                        <div class="field sm:col-span-2">
                            <label class="label">Contraseña actual</label>
                            <input type="password" class="input w-full px-4 py-2.5 rounded-lg" placeholder="••••••••••">
                        </div>
                        <div class="field">
                            <label class="label">Nueva contraseña</label>
                            <input type="password" class="input w-full px-4 py-2.5 rounded-lg" x-model="security.newPassword" placeholder="••••••••••">
                            <div class="mt-2 flex gap-1">
                                <span class="h-1 flex-1 rounded-full" :style="`background:${pwScoreColor(0)}`"></span>
                                <span class="h-1 flex-1 rounded-full" :style="`background:${pwScoreColor(1)}`"></span>
                                <span class="h-1 flex-1 rounded-full" :style="`background:${pwScoreColor(2)}`"></span>
                                <span class="h-1 flex-1 rounded-full" :style="`background:${pwScoreColor(3)}`"></span>
                            </div>
                            <p class="text-[11px] mt-1.5" :style="`color:${pwScoreColor(0)}`" x-text="pwLabel"></p>
                        </div>
                        <div class="field">
                            <label class="label">Confirmar contraseña</label>
                            <input type="password" class="input w-full px-4 py-2.5 rounded-lg" placeholder="••••••••••">
                        </div>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button @click="save('Contraseña')" class="btn-primary px-5 py-2.5 rounded-lg text-white text-sm font-medium">Actualizar contraseña</button>
                    </div>
                </div>

                {{-- Sesiones --}}
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="mb-5">
                        <h2 class="text-xl font-semibold text-white">Sesiones activas</h2>
                        <p class="text-sm text-slate-400 mt-1">Dispositivos conectados a tu cuenta actualmente.</p>
                    </div>
                    <ul class="space-y-3">
                        <template x-for="s in sessions" :key="s.id">
                            <li class="flex items-center gap-4 p-4 rounded-xl bg-white/[0.03] border border-white/[0.06]">
                                <span class="w-10 h-10 rounded-lg bg-white/5 grid place-items-center text-slate-300" x-html="s.icon"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-white truncate" x-text="s.device"></p>
                                        <span x-show="s.current" class="badge badge-success">Esta sesión</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5"><span x-text="s.location"></span> · <span x-text="s.ip"></span> · <span x-text="s.lastActive"></span></p>
                                </div>
                                <button x-show="!s.current" class="btn-danger px-3 py-1.5 rounded-lg text-xs font-medium">Revocar</button>
                            </li>
                        </template>
                    </ul>
                    <button class="btn-secondary mt-4 px-4 py-2 rounded-lg text-sm">Cerrar todas las demás sesiones</button>
                </div>
            </div>

            {{-- =========================================================
                 NOTIFICACIONES
            ========================================================= --}}
            <div x-show="tab === 'notifications'" class="fade-enter">
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Notificaciones</h2>
                            <p class="text-sm text-slate-400 mt-1">Elige cómo y cuándo quieres recibir avisos.</p>
                        </div>
                        {{-- Header con leyenda de canales --}}
                        <div class="hidden sm:flex items-center gap-2 shrink-0">
                            <span class="notif-channel-legend">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Email
                            </span>
                            <span class="notif-channel-legend">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
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
                                    <button type="button"
                                            class="notif-chip"
                                            :class="n.email ? 'is-on' : ''"
                                            @click="n.email = !n.email"
                                            :aria-pressed="n.email ? 'true' : 'false'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="notif-chip-label">Email</span>
                                        <span class="notif-dot"></span>
                                    </button>
                                    <button type="button"
                                            class="notif-chip"
                                            :class="n.push ? 'is-on' : ''"
                                            @click="n.push = !n.push"
                                            :aria-pressed="n.push ? 'true' : 'false'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
                                        <span class="notif-chip-label">Push</span>
                                        <span class="notif-dot"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 ALMACENAMIENTO + FACTURACIÓN
            ========================================================= --}}
            <div x-show="tab === 'storage'" class="fade-enter space-y-6">
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Almacenamiento</h2>
                            <p class="text-sm text-slate-400 mt-1">Gestiona el espacio usado por tus QR, recursos y exportaciones.</p>
                        </div>
                        <span class="badge" :class="storage.percent < 70 ? 'badge-success' : (storage.percent < 90 ? 'badge-warning' : 'badge-danger')">
                            <span x-text="storage.percent < 70 ? 'Espacio saludable' : (storage.percent < 90 ? 'Acercándote al límite' : 'Casi sin espacio')"></span>
                        </span>
                    </div>

                    <div class="storage-stats">
                        <div class="storage-stat">
                            <span class="storage-stat-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
                            </span>
                            <div>
                                <p class="storage-stat-label">Usado</p>
                                <p class="storage-stat-value" x-text="storage.usedLabel"></p>
                            </div>
                        </div>
                        <div class="storage-stat">
                            <span class="storage-stat-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.5 14.5A6.5 6.5 0 0114 21H7a5 5 0 01-1-9.9A7 7 0 0119 9a5.5 5.5 0 011.5 5.5z"/></svg>
                            </span>
                            <div>
                                <p class="storage-stat-label">Disponible</p>
                                <p class="storage-stat-value" x-text="storage.totalLabel"></p>
                            </div>
                        </div>
                        <div class="storage-stat">
                            <span class="storage-stat-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            </span>
                            <div>
                                <p class="storage-stat-label">QR generados</p>
                                <p class="storage-stat-value">47</p>
                            </div>
                        </div>
                    </div>

                    <div class="storage-progress-card">
                        <div class="storage-progress-head">
                            <span class="storage-progress-title">Uso del plan</span>
                            <span class="storage-progress-pct"><span x-text="storage.percent"></span>% utilizado · <span x-text="storage.usedLabel"></span> de <span x-text="storage.totalLabel"></span></span>
                        </div>
                        <div class="storage-track">
                            <div class="storage-bar" :style="`width: ${storage.percent}%`"></div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="flex items-end justify-between gap-3 mb-2 flex-wrap">
                        <div>
                            <h3 class="text-base font-semibold text-white">Ampliar espacio</h3>
                            <p class="text-xs text-slate-400 mt-1">Pago único · sin renovación automática.</p>
                        </div>
                        <span class="text-xs text-slate-500">Selecciona un paquete</span>
                    </div>

                    <div class="pack-grid">
                        <template x-for="(pack, idx) in storagePacks" :key="pack.size">
                            <div class="pack-card"
                                 :class="{ 'is-selected': storage.selectedPack === pack.size, 'is-featured': idx === 1 }"
                                 @click="storage.selectedPack = pack.size">
                                <span class="pack-tier" x-text="pack.label"></span>
                                <p class="pack-size"><span x-text="pack.size"></span><span class="pack-unit">MB</span></p>
                                <p class="pack-price"><span x-text="formatPrice(pack.price)"></span><span class="pack-price-suffix">/ pago único</span></p>
                                <span class="pack-check">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-3" style="margin-top: 1.75rem;">
                        <button class="btn-secondary px-4 py-2.5 rounded-lg text-sm">Cancelar</button>
                        <button class="btn-primary px-5 py-2.5 rounded-lg text-white text-sm font-medium inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 5h13M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                            Comprar ampliación
                        </button>
                    </div>
                </div>

                {{-- Facturación --}}
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Facturación y plan</h2>
                            <p class="text-sm text-slate-400 mt-1">Gestiona tu suscripción, método de pago y facturas.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="btn-secondary px-4 py-2.5 rounded-lg text-sm">Cancelar plan</button>
                            <button class="btn-primary px-5 py-2.5 rounded-lg text-white text-sm font-medium">Cambiar de plan</button>
                        </div>
                    </div>

                    <div class="billing-summary">
                        {{-- Plan actual --}}
                        <div class="billing-plan-card">
                            <div class="billing-plan-head">
                                <span class="billing-plan-tier">Plan actual</span>
                                <span class="badge badge-success">Activo</span>
                            </div>
                            <div class="billing-plan-name" x-text="billing.plan"></div>
                            <div class="billing-plan-price">
                                <span class="amount" x-text="formatPrice(billing.price)"></span>
                                <span class="period">/ <span x-text="billing.period"></span></span>
                            </div>
                            <div class="billing-plan-features">
                                <template x-for="feature in billing.features" :key="feature">
                                    <div class="billing-feature">
                                        <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span x-text="feature"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Próximo cobro + método de pago --}}
                        <div class="billing-side">
                            <div class="billing-side-card">
                                <span class="billing-side-icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="billing-side-label">Próximo cobro</p>
                                    <p class="billing-side-value" x-text="billing.nextBilling"></p>
                                    <p class="billing-side-sub"><span x-text="formatPrice(billing.price)"></span> · renovación automática</p>
                                </div>
                            </div>
                            <div class="billing-side-card">
                                <span class="billing-side-icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="billing-side-label">Método de pago</p>
                                    <p class="billing-side-value"><span x-text="billing.paymentMethod.brand"></span> •••• <span x-text="billing.paymentMethod.last4"></span></p>
                                    <p class="billing-side-sub">Caduca <span x-text="billing.paymentMethod.expires"></span></p>
                                </div>
                                <button class="ic-action" type="button">Editar</button>
                            </div>
                        </div>
                    </div>

                    {{-- Historial de facturas --}}
                    <div class="invoices-head">
                        <div>
                            <p class="invoices-title">Historial de facturas</p>
                            <p class="invoices-sub">Descarga tus recibos en PDF cuando quieras.</p>
                        </div>
                        <button class="ic-action" type="button">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                            Exportar todo
                        </button>
                    </div>

                    <div class="invoice-list">
                        <template x-for="inv in invoices" :key="inv.id">
                            <div class="invoice-row">
                                <span class="ic-date" x-text="inv.date"></span>
                                <span class="ic-desc" x-text="inv.desc"></span>
                                <span class="ic-amount" x-text="formatPrice(inv.amount)"></span>
                                <span class="ic-status"><span class="badge" :class="inv.status === 'Pagado' ? 'badge-success' : 'badge-warning'" x-text="inv.status"></span></span>
                                <button class="ic-action" type="button">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                                    PDF
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

              {{-- =========================================================
                  GRUPO
              ========================================================= --}}
            <div x-show="tab === 'team'" class="fade-enter">
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Grupo y miembros</h2>
                            <p class="text-sm text-slate-400 mt-1">Invita personas a colaborar en tus proyectos.</p>
                        </div>
                        <button class="btn-primary px-5 py-2.5 rounded-lg text-white text-sm font-medium inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Invitar miembro
                        </button>
                    </div>

                    <div class="team-meta">
                        <div class="team-meta-item">
                            <span class="team-meta-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm10 4l2 2 4-4"/></svg>
                            </span>
                            <span><strong x-text="team.length"></strong> de 5 miembros</span>
                        </div>
                        <div class="team-meta-item">
                            <span class="team-meta-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/></svg>
                            </span>
                            <span><strong x-text="team.filter(m => m.online).length"></strong> en línea</span>
                        </div>
                        <div class="team-meta-item">
                            <span class="team-meta-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/></svg>
                            </span>
                            <span>Plan <strong x-text="billing.plan"></strong></span>
                        </div>
                    </div>

                    <div class="team-list">
                        <template x-for="m in team" :key="m.id">
                            <div class="team-card">
                                <span class="tc-avatar" :class="'role-' + m.role" x-text="m.initials">
                                    <span class="tc-status-dot" :class="m.online ? 'is-online' : 'is-offline'"></span>
                                </span>
                                <div class="tc-info">
                                    <div class="tc-name">
                                        <span x-text="m.name"></span>
                                        <span class="tc-you-tag" x-show="m.role === 'Propietario'">Tú</span>
                                    </div>
                                    <div class="tc-meta">
                                        <span x-text="m.email" class="truncate"></span>
                                        <span class="tc-meta-sep"></span>
                                        <span x-text="m.lastActive"></span>
                                    </div>
                                </div>
                                <span class="tc-role role-pill" :class="'role-' + m.role" x-text="m.role"></span>
                                <div class="tc-actions">
                                    <button class="tc-icon-btn" type="button" title="Editar permisos" x-show="m.role !== 'Propietario'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11.8 15.6 8 16.4l.8-3.8 9.8-7.2z"/></svg>
                                    </button>
                                    <button class="tc-icon-btn danger" type="button" title="Eliminar miembro" x-show="m.role !== 'Propietario'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 ACTIVIDAD
            ========================================================= --}}
            <div x-show="tab === 'activity'" class="fade-enter">
                <div class="glass rounded-2xl p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Historial de actividad</h2>
                            <p class="text-sm text-slate-400 mt-1">Eventos recientes en tu cuenta.</p>
                        </div>
                        <button class="ic-action" type="button">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                            Exportar registro
                        </button>
                    </div>

                    <div class="activity-filters">
                        <button class="activity-filter" :class="activityFilter === 'all'      ? 'is-active' : ''" @click="activityFilter = 'all'">Todo</button>
                        <button class="activity-filter" :class="activityFilter === 'qr'       ? 'is-active' : ''" @click="activityFilter = 'qr'">QR</button>
                        <button class="activity-filter" :class="activityFilter === 'security' ? 'is-active' : ''" @click="activityFilter = 'security'">Seguridad</button>
                        <button class="activity-filter" :class="activityFilter === 'billing'  ? 'is-active' : ''" @click="activityFilter = 'billing'">Facturación</button>
                        <button class="activity-filter" :class="activityFilter === 'account'  ? 'is-active' : ''" @click="activityFilter = 'account'">Cuenta</button>
                    </div>

                    <template x-for="(items, groupLabel) in groupedActivity" :key="groupLabel">
                        <div class="activity-group">
                            <p class="activity-group-label" x-text="groupLabel"></p>
                            <div class="activity-timeline">
                                <template x-for="a in items" :key="a.id">
                                    <div class="activity-item">
                                        <span class="activity-icon"
                                              :style="`--act-color:${activityIcons[a.type].color}; --act-bg:${activityIcons[a.type].color}1F; --act-border:${activityIcons[a.type].color}55;`">
                                            <svg viewBox="0 0 24 24" x-html="activityIcons[a.type].svg"></svg>
                                        </span>
                                        <div class="activity-card">
                                            <div class="activity-content">
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

                    <div x-show="filteredActivity.length === 0" class="text-center py-10">
                        <p class="text-sm text-slate-400">No hay eventos en esta categoría.</p>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 ACCIONES AVANZADAS
            ========================================================= --}}
            <div x-show="tab === 'danger'" class="fade-enter">
                <div class="danger-panel">
                    <div class="danger-head">
                        <span class="danger-head-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
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
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                                </span>
                                <div class="danger-row-text">
                                    <p class="danger-row-title">Exportar mis datos</p>
                                    <p class="danger-row-desc">Descarga una copia de tu información personal, QR y facturas en formato JSON.</p>
                                </div>
                            </div>
                            <button class="btn-secondary px-4 py-2.5 rounded-lg text-sm inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                                Exportar
                            </button>
                        </div>

                        <div class="danger-row">
                            <div class="danger-row-info">
                                <span class="danger-row-icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.36 6.64A9 9 0 0120.77 15M12 3v9M3.23 15A9 9 0 015.64 6.64"/></svg>
                                </span>
                                <div class="danger-row-text">
                                    <p class="danger-row-title">Desactivar cuenta</p>
                                    <p class="danger-row-desc">Tu cuenta quedará oculta para otros usuarios. Podrás reactivarla iniciando sesión de nuevo.</p>
                                </div>
                            </div>
                            <button class="btn-secondary px-4 py-2.5 rounded-lg text-sm">Desactivar</button>
                        </div>

                        <div class="danger-row is-critical">
                            <div class="danger-row-info">
                                <span class="danger-row-icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </span>
                                <div class="danger-row-text">
                                    <p class="danger-row-title">Eliminar cuenta permanentemente</p>
                                    <p class="danger-row-desc">Todos tus QR, configuración, equipo y datos serán borrados sin posibilidad de recuperación.</p>
                                </div>
                            </div>
                            <button @click="confirmDelete = true" class="btn-danger-outline" type="button">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/></svg>
                                Eliminar cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>

</main>

{{-- Toast --}}
<div x-show="toast.show" x-transition.opacity
     class="fixed bottom-6 right-6 z-50 glass-strong rounded-xl px-5 py-3 flex items-center gap-3 shadow-2xl">
    <span class="w-8 h-8 rounded-full bg-emerald-500/20 grid place-items-center">
        <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
    </span>
    <div>
        <p class="text-sm font-medium text-white" x-text="toast.title"></p>
        <p class="text-xs text-slate-400" x-text="toast.msg"></p>
    </div>
</div>

{{-- Modal upgrade --}}
<div x-show="openUpgrade" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-black/60 backdrop-blur-sm p-4" @click.self="openUpgrade = false">
    <div class="glass-strong rounded-2xl p-6 sm:p-8 max-w-md w-full">
        <h3 class="text-xl font-semibold text-white mb-1">Ampliar almacenamiento</h3>
        <p class="text-sm text-slate-400 mb-5">Selecciona el paquete que mejor se adapte a ti.</p>
        <div class="space-y-2 mb-5">
            <template x-for="pack in storagePacks" :key="pack.size">
                <label class="flex items-center justify-between gap-3 p-4 rounded-xl border border-white/10 hover:border-rose-500/40 cursor-pointer transition" :class="{ '!border-rose-500/60 bg-rose-500/5': storage.selectedPack === pack.size }">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="pack" :value="pack.size" x-model.number="storage.selectedPack" class="accent-rose-500">
                        <div>
                            <p class="text-white font-medium"><span x-text="pack.size"></span> MB extra</p>
                            <p class="text-xs text-slate-400" x-text="pack.label"></p>
                        </div>
                    </div>
                    <p class="text-rose-400 font-semibold" x-text="formatPrice(pack.price)"></p>
                </label>
            </template>
        </div>
        <div class="flex justify-end gap-2">
            <button class="btn-secondary px-4 py-2 rounded-lg text-sm" @click="openUpgrade = false">Cancelar</button>
            <button class="btn-primary px-5 py-2 rounded-lg text-white text-sm font-medium" @click="openUpgrade = false; save('Almacenamiento ampliado')">Confirmar compra</button>
        </div>
    </div>
</div>

{{-- Modal eliminar --}}
<div x-show="confirmDelete" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-black/60 backdrop-blur-sm p-4" @click.self="confirmDelete = false">
    <div class="glass-strong rounded-2xl p-6 sm:p-8 max-w-md w-full border border-rose-700/50">
        <div class="w-12 h-12 rounded-full bg-rose-500/20 grid place-items-center mb-4">
            <svg class="w-6 h-6 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-white mb-1">¿Eliminar cuenta?</h3>
        <p class="text-sm text-slate-400 mb-5">Esta acción no se puede deshacer. Escribe <span class="text-rose-400 font-mono">ELIMINAR</span> para confirmar.</p>
        <input type="text" class="input w-full px-4 py-2.5 rounded-lg mb-5" x-model="deleteConfirmText" placeholder="ELIMINAR">
        <div class="flex justify-end gap-2">
            <button class="btn-secondary px-4 py-2 rounded-lg text-sm" @click="confirmDelete = false">Cancelar</button>
            <button class="btn-danger px-4 py-2 rounded-lg text-sm font-semibold" :disabled="deleteConfirmText !== 'ELIMINAR'" :class="{ 'opacity-50 cursor-not-allowed': deleteConfirmText !== 'ELIMINAR' }">Sí, eliminar</button>
        </div>
    </div>
</div>

<script>
    function profilePanel() {
        return {
            tab: 'general',
            openUpgrade: false,
            confirmDelete: false,
            deleteConfirmText: '',
            toast: { show: false, title: '', msg: '' },

            user: {
                firstName: 'Miguel',
                lastName: 'Novoa García',
                email: 'geometrymike20@gmail.com',
                verifiedEmail: 'geometrymike20@gmail.com',
                birthdate: '1995-08-14',
                gender: 'm',
                avatar: null,
                phones: [{ code: '+34', number: '612 345 678' }],
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
            prefs: { language: 'es', timezone: 'Europe/Madrid', currency: 'EUR' },

            // ----- i18n: idiomas, monedas y traducciones -----
            languages: {
                es: { code: 'ES', label: 'Español',     flag: '🇪🇸' },
                en: { code: 'EN', label: 'English',     flag: '🇬🇧' },
                pt: { code: 'PT', label: 'Português',   flag: '🇵🇹' },
                fr: { code: 'FR', label: 'Français',    flag: '🇫🇷' },
                de: { code: 'DE', label: 'Deutsch',     flag: '🇩🇪' },
            },
            // Tasas relativas a EUR (mock)
            currencies: {
                EUR: { symbol: '€', label: 'Euro',          rate: 1.00,  position: 'after',  decimals: 2 },
                USD: { symbol: '$', label: 'US Dollar',     rate: 1.08,  position: 'before', decimals: 2 },
                GBP: { symbol: '£', label: 'Libra esterlina', rate: 0.85, position: 'before', decimals: 2 },
                MXN: { symbol: '$', label: 'Peso mexicano', rate: 18.50, position: 'before', decimals: 2 },
            },
            i18n: {
                es: {
                    backToStudio: 'Volver al QR Studio',
                    openStudio: 'Abrir QR Studio',
                    openStudioDesc: 'Crea, gestiona y personaliza tus códigos QR',
                    logout: 'Cerrar sesión',
                    language: 'Idioma',
                    currency: 'Moneda',
                    languageChanged: 'Idioma actualizado',
                    currencyChanged: 'Moneda actualizada',
                    share: 'Compartir',
                    editProfile: 'Editar perfil',
                    qrGenerated: 'QR generados',
                    totalScans: 'Escaneos totales',
                    teamMembers: 'Miembros del equipo',
                    storage: 'Almacenamiento',
                    upgradeStorage: 'Ampliar +100 MB',
                    nav_general: 'Información general',
                    nav_security: 'Seguridad y contraseña',
                    nav_notifications: 'Notificaciones',
                    nav_storage: 'Almacenamiento y plan',
                    nav_team: 'Equipo',
                    nav_activity: 'Actividad',
                    nav_danger: 'Acciones avanzadas',
                },
                en: {
                    backToStudio: 'Back to QR Studio',
                    openStudio: 'Open QR Studio',
                    openStudioDesc: 'Create, manage and customize your QR codes',
                    logout: 'Sign out',
                    language: 'Language',
                    currency: 'Currency',
                    languageChanged: 'Language updated',
                    currencyChanged: 'Currency updated',
                    share: 'Share',
                    editProfile: 'Edit profile',
                    qrGenerated: 'QR codes generated',
                    totalScans: 'Total scans',
                    teamMembers: 'Team members',
                    storage: 'Storage',
                    upgradeStorage: 'Add +100 MB',
                    nav_general: 'General info',
                    nav_security: 'Security & password',
                    nav_notifications: 'Notifications',
                    nav_storage: 'Storage & plan',
                    nav_team: 'Team',
                    nav_activity: 'Activity',
                    nav_danger: 'Danger zone',
                },
                pt: {
                    backToStudio: 'Voltar ao QR Studio',
                    openStudio: 'Abrir QR Studio',
                    openStudioDesc: 'Crie, gerencie e personalize seus códigos QR',
                    logout: 'Sair',
                    language: 'Idioma',
                    currency: 'Moeda',
                    languageChanged: 'Idioma atualizado',
                    currencyChanged: 'Moeda atualizada',
                    share: 'Partilhar',
                    editProfile: 'Editar perfil',
                    qrGenerated: 'QR gerados',
                    totalScans: 'Verificações totais',
                    teamMembers: 'Membros da equipa',
                    storage: 'Armazenamento',
                    upgradeStorage: 'Adicionar +100 MB',
                    nav_general: 'Informação geral',
                    nav_security: 'Segurança e palavra-passe',
                    nav_notifications: 'Notificações',
                    nav_storage: 'Armazenamento e plano',
                    nav_team: 'Equipa',
                    nav_activity: 'Atividade',
                    nav_danger: 'Zona de perigo',
                },
                fr: {
                    backToStudio: 'Retour au QR Studio',
                    openStudio: 'Ouvrir QR Studio',
                    openStudioDesc: 'Créez, gérez et personnalisez vos codes QR',
                    logout: 'Déconnexion',
                    language: 'Langue',
                    currency: 'Devise',
                    languageChanged: 'Langue mise à jour',
                    currencyChanged: 'Devise mise à jour',
                    share: 'Partager',
                    editProfile: 'Modifier le profil',
                    qrGenerated: 'QR générés',
                    totalScans: 'Scans totaux',
                    teamMembers: "Membres de l'équipe",
                    storage: 'Stockage',
                    upgradeStorage: 'Ajouter +100 Mo',
                    nav_general: 'Informations générales',
                    nav_security: 'Sécurité et mot de passe',
                    nav_notifications: 'Notifications',
                    nav_storage: 'Stockage et forfait',
                    nav_team: 'Équipe',
                    nav_activity: 'Activité',
                    nav_danger: 'Zone dangereuse',
                },
                de: {
                    backToStudio: 'Zurück zum QR Studio',
                    openStudio: 'QR Studio öffnen',
                    openStudioDesc: 'QR-Codes erstellen, verwalten und anpassen',
                    logout: 'Abmelden',
                    language: 'Sprache',
                    currency: 'Währung',
                    languageChanged: 'Sprache aktualisiert',
                    currencyChanged: 'Währung aktualisiert',
                    share: 'Teilen',
                    editProfile: 'Profil bearbeiten',
                    qrGenerated: 'Erzeugte QR',
                    totalScans: 'Gesamtscans',
                    teamMembers: 'Teammitglieder',
                    storage: 'Speicher',
                    upgradeStorage: '+100 MB hinzufügen',
                    nav_general: 'Allgemeine Informationen',
                    nav_security: 'Sicherheit & Passwort',
                    nav_notifications: 'Benachrichtigungen',
                    nav_storage: 'Speicher & Tarif',
                    nav_team: 'Team',
                    nav_activity: 'Aktivität',
                    nav_danger: 'Gefahrenzone',
                },
            },
            security: { twoFA: true, newPassword: '' },
            storage: {
                used: 0.04, total: 0.1, selectedPack: 100,
                get usedLabel() { return this.used.toFixed(2) + ' GB'; },
                get totalLabel() { return this.total.toFixed(1) + ' GB'; },
                get percent() { return Math.min(100, Math.round((this.used / this.total) * 100)); },
            },
            billing: {
                plan: 'Pro',
                price: 9.99,
                period: 'mensual',
                nextBilling: '01 jun 2026',
                paymentMethod: { brand: 'Visa', last4: '4242', expires: '12/27' },
                features: ['QR ilimitados', 'Estadísticas avanzadas', 'Marca personalizada', 'Soporte prioritario'],
            },
            storagePacks: [
                { size: 100, price: 0.99, label: 'Básico' },
                { size: 500, price: 3.99, label: 'Recomendado' },
                { size: 1000, price: 6.99, label: 'Pro' },
            ],
            invoices: [
                { id: 1, date: '01 may 2026', desc: 'Plan Pro · Mensual', amount: 9.99, status: 'Pagado' },
                { id: 2, date: '01 abr 2026', desc: 'Plan Pro · Mensual', amount: 9.99, status: 'Pagado' },
                { id: 3, date: '15 mar 2026', desc: 'Ampliación 500 MB',  amount: 3.99, status: 'Pagado' },
                { id: 4, date: '01 mar 2026', desc: 'Plan Pro · Mensual', amount: 9.99, status: 'Pagado' },
            ],
            sessions: [
                { id: 1, current: true,  device: 'Chrome · Windows 11', location: 'Madrid, ES', ip: '83.52.10.4',  lastActive: 'Ahora', icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"2\" y=\"3\" width=\"20\" height=\"14\" rx=\"2\"/><line x1=\"8\" y1=\"21\" x2=\"16\" y2=\"21\"/><line x1=\"12\" y1=\"17\" x2=\"12\" y2=\"21\"/></svg>' },
                { id: 2, current: false, device: 'Safari · iPhone 15',   location: 'Madrid, ES', ip: '83.52.10.4',  lastActive: 'hace 2 h', icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"5\" y=\"2\" width=\"14\" height=\"20\" rx=\"2\"/><line x1=\"12\" y1=\"18\" x2=\"12.01\" y2=\"18\"/></svg>' },
                { id: 3, current: false, device: 'Firefox · macOS',      location: 'Barcelona, ES', ip: '95.122.0.7', lastActive: 'hace 3 días', icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"2\" y=\"4\" width=\"20\" height=\"12\" rx=\"2\"/><path d=\"M2 20h20\"/></svg>' },
            ],
            notifications: {
                qr_created:    { title: 'QR generado',          desc: 'Cuando se crea un nuevo código QR.',     email: true,  push: true  },
                qr_scanned:    { title: 'Escaneo de QR',        desc: 'Notifícame cuando alguien escanee un QR.', email: false, push: true  },
                billing:       { title: 'Facturación',          desc: 'Recibos, renovaciones y avisos de pago.', email: true,  push: false },
                security:      { title: 'Alertas de seguridad', desc: 'Inicios de sesión sospechosos o cambios.', email: true,  push: true  },
                product:       { title: 'Novedades del producto', desc: 'Nuevas funciones y actualizaciones.',  email: true,  push: false },
                marketing:     { title: 'Marketing',            desc: 'Ofertas, descuentos y promociones.',      email: false, push: false },
            },
            team: [
                { id: 1, name: 'Miguel Novoa',  email: 'geometrymike20@gmail.com', initials: 'MN', role: 'Propietario', online: true,  lastActive: 'En línea' },
                { id: 2, name: 'Laura Pérez',   email: 'laura@blackink.es',        initials: 'LP', role: 'Editor',      online: true,  lastActive: 'hace 12 min' },
                { id: 3, name: 'David Romero',  email: 'david@blackink.es',        initials: 'DR', role: 'Visor',       online: false, lastActive: 'hace 3 días' },
            ],
            activity: [
                { id: 1, type: 'qr',       title: 'Nuevo QR generado',       detail: 'd-tattoo.com/sesion-mayo · personalizado', time: 'hace 12 min', date: 'Hoy' },
                { id: 2, type: 'security', title: 'Inicio de sesión',         detail: 'Chrome · Windows 11 · Madrid, ES',         time: 'hace 1 h',    date: 'Hoy' },
                { id: 3, type: 'billing',  title: 'Plan actualizado a Pro',   detail: 'Facturación €9.99/mes',                    time: 'hace 1 día',  date: 'Esta semana' },
                { id: 4, type: 'security', title: 'Contraseña modificada',    detail: 'Cambio realizado desde ajustes',           time: 'hace 4 días', date: 'Esta semana' },
                { id: 5, type: 'account',  title: 'Cuenta creada',            detail: 'Bienvenido a dtattoos',                    time: '14 mar 2024', date: 'Más antiguo' },
            ],
            activityIcons: {
                qr:       { color: '#ef4444', svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z"/>' },
                security: { color: '#10b981', svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.5 1-3 3-3s3 1.5 3 3-1 2.5-3 3v2m0 3h.01M5 13a7 7 0 1014 0H5z"/>' },
                billing:  { color: '#3b82f6', svg: '<rect x="2" y="5" width="20" height="14" rx="2" stroke-linejoin="round"/><line x1="2" y1="10" x2="22" y2="10" stroke-linecap="round"/>' },
                account:  { color: '#94a3b8', svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm10-3v6m3-3h-6"/>' },
            },
            activityFilter: 'all',
            get filteredActivity() {
                if (this.activityFilter === 'all') return this.activity;
                return this.activity.filter(a => a.type === this.activityFilter);
            },
            get groupedActivity() {
                const groups = {};
                this.filteredActivity.forEach(a => { (groups[a.date] = groups[a.date] || []).push(a); });
                return groups;
            },

            navItems: [
                { id: 'general',       icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"7\" r=\"4\"/><path d=\"M5.5 21a6.5 6.5 0 0113 0\"/></svg>' },
                { id: 'security',      icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"11\" width=\"18\" height=\"11\" rx=\"2\"/><path d=\"M7 11V7a5 5 0 0110 0v4\"/></svg>' },
                { id: 'notifications', icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9\"/><path d=\"M13.73 21a2 2 0 01-3.46 0\"/></svg>' },
                { id: 'storage',       icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><ellipse cx=\"12\" cy=\"5\" rx=\"9\" ry=\"3\"/><path d=\"M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5\"/><path d=\"M3 12c0 1.66 4 3 9 3s9-1.34 9-3\"/></svg>' },
                { id: 'team',          icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75\"/></svg>' },
                { id: 'activity',      icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"22 12 18 12 15 21 9 3 6 12 2 12\"/></svg>' },
                { id: 'danger',        icon: '<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z\"/><line x1=\"12\" y1=\"9\" x2=\"12\" y2=\"13\"/><line x1=\"12\" y1=\"17\" x2=\"12.01\" y2=\"17\"/></svg>' },
            ],
            get nav() {
                return this.navItems.map(it => ({ ...it, label: this.t('nav_' + it.id) }));
            },

            // ----- Helpers de i18n / formato de moneda -----
            t(key) {
                const dict = this.i18n[this.prefs.language] || this.i18n.es;
                return dict[key] ?? this.i18n.es[key] ?? key;
            },
            formatPrice(amount) {
                const cur = this.currencies[this.prefs.currency] || this.currencies.EUR;
                const value = (Number(amount) * cur.rate).toFixed(cur.decimals);
                return cur.position === 'before' ? `${cur.symbol}${value}` : `${value} ${cur.symbol}`;
            },

            get initials() {
                return ((this.user.firstName?.[0] ?? '') + (this.user.lastName?.[0] ?? '')).toUpperCase();
            },

            pwScore() {
                const p = this.security.newPassword || '';
                let s = 0;
                if (p.length >= 8) s++;
                if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++;
                if (/\d/.test(p)) s++;
                if (/[^A-Za-z0-9]/.test(p) && p.length >= 12) s++;
                return s;
            },
            get pwLabel() {
                return ['Introduce una contraseña','Débil','Aceptable','Buena','Excelente'][this.pwScore()];
            },
            pwScoreColor(idx) {
                const score = this.pwScore();
                if (idx >= score) return 'rgba(255,255,255,0.08)';
                return ['#ef4444','#f59e0b','#3b82f6','#10b981'][score-1] || '#ef4444';
            },

            onAvatar(e) {
                const file = e.target.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = ev => this.user.avatar = ev.target.result;
                reader.readAsDataURL(file);
            },

            copy(text) {
                navigator.clipboard?.writeText(text);
                this.notify('Copiado', 'El texto se ha copiado al portapapeles.');
            },

            save(what) {
                this.notify('Cambios guardados', what + ' se actualizó correctamente.');
            },

            notify(title, msg) {
                this.toast = { show: true, title, msg };
                clearTimeout(this._t);
                this._t = setTimeout(() => this.toast.show = false, 2800);
            },
        }
    }
</script>

</body>
</html>
