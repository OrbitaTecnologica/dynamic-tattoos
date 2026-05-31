<!doctype html>
<html lang="es">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1a1a1a; line-height:1.6;">
    <h2>Tu suscripción se renueva pronto</h2>
    <p>Hola{{ $name ? ' '.$name : '' }},</p>
    <p>
        Te recordamos que tu plan <strong>{{ $planName }}</strong> en Dynamic&nbsp;Tattoos
        se renovará automáticamente
        @if($renewsAt) el <strong>{{ $renewsAt }}</strong> @endif
        @if($amount) por <strong>{{ $amount }}</strong> @endif.
    </p>
    <p>No necesitas hacer nada: el cobro se realizará de forma automática con tu método de pago habitual.</p>
    <p style="color:#6b6b6b;font-size:13px;">
        Si quieres cambiar o cancelar tu plan, puedes hacerlo desde <em>Mi cuenta → Suscripción y pagos</em> antes de la fecha de renovación.
    </p>
</body>
</html>
