<x-mail::message>
# Verifica tu correo

Usa este código para confirmar tu cuenta de Dynamic Tattoos:

<x-mail::panel>
# {{ $code }}
</x-mail::panel>

El código caduca en {{ config('verification.ttl_minutes') }} minutos. Si no creaste esta cuenta, ignora este mensaje.

Gracias,<br>
Dynamic Tattoos
</x-mail::message>
