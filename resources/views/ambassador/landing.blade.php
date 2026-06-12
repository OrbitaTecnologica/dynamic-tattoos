<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recomendado por {{ $name }} — Dynamic Tattoos</title>
    <meta name="robots" content="noindex">
    <style>
        :root { color-scheme: dark; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #0a0a0a; color: #f5f5f5; margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 2rem; }
        .card { max-width: 480px; width: 100%; background: #141414; border: 1px solid rgba(255,255,255,0.08); border-radius: 1.25rem; padding: 2.5rem 2rem; text-align: center; }
        .tier-badge { display: inline-block; padding: 0.25rem 0.75rem; font-size: 0.75rem; border-radius: 999px; background: #1f1f1f; color: #d4d4d4; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.1); }
        h1 { font-size: 1.5rem; margin: 0 0 0.5rem; font-weight: 700; }
        .name { color: #e879f9; }
        p { color: #a3a3a3; line-height: 1.6; }
        .cta-primary, .cta-secondary { display: block; padding: 0.875rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 600; margin-top: 1rem; transition: transform 0.15s ease; }
        .cta-primary { background: linear-gradient(135deg, #d946ef, #a855f7); color: white; }
        .cta-secondary { background: transparent; color: #d4d4d4; border: 1px solid rgba(255,255,255,0.15); }
        .cta-primary:hover, .cta-secondary:hover { transform: translateY(-1px); }
        .legal { font-size: 0.75rem; color: #525252; margin-top: 1.5rem; }
        .logo { font-weight: 700; letter-spacing: 0.04em; margin-bottom: 1.5rem; color: #e879f9; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">DYNAMIC TATTOOS</div>

        @if($tierLabel)
            <span class="tier-badge">Embajador {{ $tierLabel }}</span>
        @endif

        <h1>Recomendado por <span class="name">{{ $name }}</span></h1>

        <p>Tatuajes inteligentes con QR personalizable. Cambia el contenido al que apunta tu tatuaje cuando quieras, sin perder el diseño.</p>

        <a href="{{ $frontendUrl }}/register/cliente?ref={{ $referralCode }}" class="cta-primary">
            Crear mi cuenta gratis
        </a>

        <a href="{{ $frontendUrl }}/register/embajador?ref={{ $referralCode }}" class="cta-secondary">
            Únete como Embajador
        </a>

        <p class="legal">
            Al registrarte aceptas nuestros
            <a href="{{ $frontendUrl }}/condiciones" style="color: #737373;">términos</a> y
            <a href="{{ $frontendUrl }}/privacidad" style="color: #737373;">política de privacidad</a>.
        </p>
    </div>
</body>
</html>
