<x-mail::message>
# Verifica tu correo electrónico

Gracias por registrarte en **Dynamic Tattoos**. Introduce el siguiente código para activar tu cuenta:

<x-mail::panel>
<p style="text-align:center;font-size:44px;font-weight:800;letter-spacing:18px;color:#1a1a1a;margin:8px 0 8px 18px;font-family:'Manrope',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">{{ $code }}</p>
</x-mail::panel>

<x-mail::button :url="rtrim(config('app.frontend_url'), '/') . '/verificar-email'">
Verificar mi cuenta
</x-mail::button>

Este código expira en **{{ config('verification.ttl_minutes') }} minutos**.

Si no creaste esta cuenta, puedes ignorar este correo de forma segura.

Gracias,<br>
El equipo de Dynamic Tattoos
</x-mail::message>
