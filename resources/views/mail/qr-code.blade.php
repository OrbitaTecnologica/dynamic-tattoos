<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu código QR · Dynamic Tattoos</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f5; font-family: 'Segoe UI', Arial, sans-serif; color: #18181b; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #0b0d12; padding: 32px 40px 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; color: #fff; letter-spacing: -0.5px; }
        .header span { color: #ff1a1a; }
        .body { padding: 36px 40px; text-align: center; }
        .body p { font-size: 15px; color: #52525b; line-height: 1.6; margin: 0 0 24px; }
        .url-box { background: #f4f4f5; border-radius: 8px; padding: 10px 16px; font-size: 13px; color: #71717a; word-break: break-all; margin-bottom: 28px; }
        .note { font-size: 12px; color: #a1a1aa; margin-top: 24px; }
        .footer { background: #0b0d12; padding: 18px 40px; text-align: center; }
        .footer p { margin: 0; font-size: 11px; color: #52525b; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Dynamic<span>Tattoos</span></h1>
        </div>
        <div class="body">
            <p>Aquí tienes tu código QR. Lo encontrarás adjunto a este correo como archivo <strong>PNG</strong> listo para usar.</p>
            <p>El QR apunta a:</p>
            <div class="url-box">{{ $targetUrl }}</div>
            <p class="note">Si no solicitaste este QR, puedes ignorar este mensaje.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Dynamic Tattoos &mdash; Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
