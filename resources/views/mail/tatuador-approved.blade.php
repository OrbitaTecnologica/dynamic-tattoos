<x-mail::message>
# ¡Bienvenido a Dynamic Tattoos, {{ $name }}!

Tu solicitud para formar parte de la red de tatuadores certificados ha sido **aprobada**.

Para empezar, define tu contraseña haciendo clic en el botón:

<x-mail::button :url="$resetUrl">
Crear mi contraseña
</x-mail::button>

Una vez dentro podrás completar tu perfil y empezar a recibir clientes.

Si tienes cualquier duda, responde a este correo.

Gracias,
**Dynamic Tattoos**
</x-mail::message>
