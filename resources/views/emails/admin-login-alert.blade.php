<!doctype html>
<html lang="es">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f4f4f7;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:560px;margin:24px auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
        <div style="background:#0a1030;padding:20px 28px;">
            <span style="color:#22d3ee;font-weight:bold;font-size:18px;letter-spacing:.04em;">DYNAMIC TATTOOS</span>
        </div>
        <div style="padding:28px;">
            <h1 style="font-size:18px;margin:0 0 12px;">Nuevo acceso al panel de administración</h1>
            <p style="font-size:14px;line-height:1.6;margin:0 0 16px;">
                Hola {{ $name }}, se ha iniciado sesión en el panel de administración desde una
                dirección que no habíamos visto antes:
            </p>
            <table style="font-size:13px;line-height:1.8;color:#374151;border-collapse:collapse;">
                <tr><td style="padding-right:12px;color:#6b7280;">Fecha</td><td><b>{{ $when }}</b></td></tr>
                <tr><td style="padding-right:12px;color:#6b7280;">IP</td><td><b>{{ $ip }}</b></td></tr>
                <tr><td style="padding-right:12px;color:#6b7280;">Navegador</td><td>{{ $userAgent }}</td></tr>
            </table>
            <p style="font-size:13px;line-height:1.6;margin:20px 0 0;color:#6b7280;">
                Si fuiste tú, no necesitas hacer nada. Si no reconoces este acceso, cambia tu
                contraseña inmediatamente desde el panel y avisa al equipo técnico.
            </p>
        </div>
        <div style="padding:16px 28px;border-top:1px solid #e5e7eb;font-size:12px;color:#9ca3af;">
            Dynamic Tattoos · aviso automático de seguridad
        </div>
    </div>
</body>
</html>
