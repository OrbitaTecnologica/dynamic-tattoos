<!doctype html>
<html lang="es">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1a1a1a; line-height:1.6;">
    <h2>Nueva solicitud de homologación de tatuador</h2>
    <p><strong>Contacto:</strong> {{ $s->name }}</p>
    <p><strong>Estudio:</strong> {{ $s->studio_name }}</p>
    @if($s->city)<p><strong>Ciudad:</strong> {{ $s->city }}</p>@endif
    <p><strong>Email:</strong> {{ $s->email }}</p>
    @if($s->phone)<p><strong>Teléfono:</strong> {{ $s->phone }}</p>@endif
    @if($s->message)
        <hr style="border:none;border-top:1px solid #e5e5e5;margin:18px 0;">
        <p style="white-space:pre-wrap;">{{ $s->message }}</p>
    @endif
    <p style="color:#6b6b6b;font-size:13px;">Revisa y certifica desde el panel · Admin → Tatuadores.</p>
</body>
</html>
