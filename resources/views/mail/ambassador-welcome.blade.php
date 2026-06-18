<x-mail::message>
# ¡Bienvenido, {{ $name }}!

Tu cuenta de embajador ya está activa. Ya puedes empezar a compartir tu enlace personal y ganar comisiones.

## Tu enlace de embajador

Comparte este enlace con quien quieras. Cada vez que alguien se registre y active un plan de pago usando tu enlace, ganarás **{{ number_format($rewardPerReferral, 2, ',', '.') }} €**.

<x-mail::button :url="$referralLink">
Ver mi enlace de embajador
</x-mail::button>

O cópialo directamente:

`{{ $referralLink }}`

Puedes consultar tus referidos, tus ganancias y personalizar tu enlace desde tu panel de embajador.

<x-mail::button :url="rtrim(config('app.frontend_url'), '/') . '/mi-cuenta'" color="white">
Ir a mi panel
</x-mail::button>

Sin límite de referidos. Sin coste de alta. Cuantos más clientes actives, más ganas.

Gracias,<br>
Dynamic Tattoos
</x-mail::message>
