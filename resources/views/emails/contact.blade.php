<!doctype html>
<html lang="es">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1a1a1a; line-height:1.6;">
    <h2>Nuevo mensaje de contacto</h2>
    <p><strong>Nombre:</strong> {{ $senderName }}</p>
    <p><strong>Email:</strong> {{ $senderEmail }}</p>
    @if($subjectLine)
        <p><strong>Asunto:</strong> {{ $subjectLine }}</p>
    @endif
    <hr style="border:none;border-top:1px solid #e5e5e5;margin:18px 0;">
    <p style="white-space:pre-wrap;">{{ $body }}</p>
</body>
</html>
