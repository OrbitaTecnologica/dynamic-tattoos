<x-mail::message>
# Verifica tu correo electrónico

Gracias por registrarte en **Dynamic Tattoos**. Introduce el siguiente código para activar tu cuenta:

<x-mail::panel>
<p style="text-align:center;font-size:42px;font-weight:800;letter-spacing:16px;color:#111111;margin:10px 0 10px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">{{ $code }}</p>
</x-mail::panel>

<x-mail::button :url="rtrim(config('app.frontend_url'), '/') . '/verificar-email'">
Verificar mi cuenta
</x-mail::button>

Este código expira en **{{ config('verification.ttl_minutes') }} minutos**. Si no creaste esta cuenta, ignora este correo de forma segura.

Gracias,<br>
El equipo de Dynamic Tattoos
</x-mail::message>
