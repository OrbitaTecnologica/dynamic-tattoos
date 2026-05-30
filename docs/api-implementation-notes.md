# Notas de implementación — APIs de cuenta / QR Studio

Decisiones conscientes tomadas al construir los endpoints del dashboard (rama `feature/account-qr-apis`).

## 2FA con servicio propio (`App\Services\TotpService`)

No se usa `pragmarx/google2fa` porque exige `ext-bcmath`, que no está instalado en el entorno (y al usarse en runtime fallaría). `TotpService` implementa TOTP (RFC 6238, HMAC-SHA1, 6 dígitos, periodo 30s) sin dependencias ni bcmath; es compatible con Google Authenticator/Authy. Cubierto por tests (enable→confirm, login con código y con código de recuperación).

- Columnas en `users`: `two_factor_secret` (encrypted), `two_factor_recovery_codes` (encrypted:array), `two_factor_confirmed_at`.
- El login (`AuthTokenController@store`) exige `two_factor_code` cuando el 2FA está confirmado; acepta TOTP o un código de recuperación de un solo uso.

## `spatie/laravel-activitylog` instalado con `--ignore-platform-req=ext-bcmath`

El proyecto ya corre ignorando `ext-bcmath` (lo requiere Cashier de forma transitiva). Se fijó la **v4** del paquete por compatibilidad con PHP 8.3 (v5 pide 8.4). **Recomendado para producción:** instalar `php8.3-bcmath` en el servidor (`apt install php8.3-bcmath`) para no depender del flag.

## Almacenamiento / cuota

- El uso se contabiliza en la tabla `uploads` (filas con `bytes`) vía `App\Services\UploadManager`, que además **borra el archivo anterior** del mismo `type` al reemplazarlo (avatar, link_avatar, link_cover).
- `User::storageUsage()` = `sum(uploads.bytes)` vs `plans.storage_mb + users.extra_storage_mb`.
- La compra de un pack se acredita en `extra_storage_mb` desde el webhook de Stripe (`HandleCashierWebhook`, evento `checkout.session.completed` con `metadata.storage_pack_id`). `extra_storage_mb` **no** es mass-assignable (se setea con `forceFill`).

## Pendientes menores conocidos (no bloqueantes)

- Forma de respuesta: `qr-codes` pagina; `sessions`/`activity`/`team` devuelven `{data:[...]}` sin meta (listas acotadas). Intencional.
- Activity log: instrumentados login, QR creado, password, perfil, 2FA, invitación de equipo y compra de almacenamiento. Otros eventos de plan/suscripción siguen el flujo de Cashier ya existente.
