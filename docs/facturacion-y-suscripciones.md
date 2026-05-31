# Facturación y suscripciones

Cómo funciona el cobro, los planes, los impuestos y los recordatorios de renovación. Todo vive en el backend (`dynamic-tattoos`) sobre **Laravel Cashier + Stripe**. El frontend solo consume la API.

## Planes

- Viven en la tabla `plans` y se siembran con `database/seeders/PlanSeeder.php` (Básico, Premium, Premium Top).
- **Todos son anuales** (`billing_cycle = 'yearly'`). El `price` guardado es el **importe anual** que cobra Stripe (p. ej. Básico 4.90 €/año).
- `Premium` está marcado como destacado (`is_featured = true`) → en el dashboard sale resaltado con "Más elegido".
- El frontend los lista dinámicamente en *Mi cuenta → Suscripción y pagos* vía `GET /api/v1/plans`. (La sección de planes del **landing** es estática/hardcodeada.)

### Crear/actualizar precios en Stripe
Los planes necesitan un `stripe_price_id`. Se generan con un comando idempotente que crea producto + precio recurrente en Stripe:

```bash
php artisan stripe:sync-plans          # crea los que falten
php artisan stripe:sync-plans --force  # recrea (p. ej. tras cambiar precio/ciclo/impuesto)
```

El intervalo del precio se deduce de `billing_cycle` (`yearly` → `year`). Si cambias un precio, ciclo o el comportamiento fiscal, hay que correr `--force` (los precios de Stripe son inmutables).

## Quién emite la factura → **Stripe**

No hay facturación interna. Stripe Billing genera la factura/recibo en cada cobro y la aloja (PDF). La app solo las **lista** (`GET /api/v1/me/invoices` → `$user->invoices()` de Cashier).

### Facturas fiscales con IVA (Stripe Tax)
Preparado y desactivado por defecto detrás de un flag (`config/billing.php`):

```dotenv
STRIPE_TAX_ENABLED=false        # poner true SOLO tras activar Stripe Tax en el panel
STRIPE_TAX_BEHAVIOR=inclusive   # IVA incluido en el precio
```

Cuando el flag está activo, el checkout: calcula IVA (`automatic_tax`), recoge el **NIF/IVA** del cliente (`tax_id_collection`), pide dirección de facturación y la guarda en el Customer. Además, el NIF/IVA guardado en *Mi cuenta → empresa* (`companies.vat`/`tax_id`) se **empuja al Customer de Stripe** (`App\Services\Billing\StripeTaxProfile`, llamado en el checkout y al actualizar la empresa).

Pasos para activarlo:
1. En el **panel de Stripe**: activar **Stripe Tax** y registrar la región (España/IVA), poner datos fiscales de la empresa, numeración de facturas y emails de factura al cliente.
2. `STRIPE_TAX_ENABLED=true` en `.env` → `php artisan config:clear` → `php artisan stripe:sync-plans --force`.

> ⚠️ No actives el flag antes de activar Stripe Tax en el panel: `automatic_tax` exige Stripe Tax activo o el checkout fallará.

## Próximo pago y recordatorio de renovación

### Registro del próximo cobro
- Columnas en `users`: `renews_at` (fecha del próximo cobro) y `renewal_reminded_at`.
- Se rellenan automáticamente desde el **webhook de Stripe** (`App\Listeners\HandleCashierWebhook`): en `customer.subscription.created/updated` se guarda `current_period_end` en `renews_at`. Si la fecha cambia (renovó), se reinicia `renewal_reminded_at`. En `customer.subscription.deleted` se limpian.
- `GET /api/v1/me/billing` expone `renews_at` → el dashboard lo muestra en "Próximo cobro".

### Recordatorio ~1 mes antes
- Mailable `App\Mail\RenewalReminderMail` (+ vista `resources/views/emails/renewal-reminder.blade.php`).
- Comando: avisa a quien renueva dentro de la ventana (30 días por defecto) y no ha sido avisado; marca `renewal_reminded_at` para no duplicar.

```bash
php artisan subscriptions:send-renewal-reminders            # 30 días
php artisan subscriptions:send-renewal-reminders --days=15  # otra antelación
```

- Está **programado a diario (08:00)** en `routes/console.php`.

### ⚙️ Requisito en producción: cron del scheduler
El recordatorio depende del scheduler de Laravel. En el servidor hay que añadir **una** entrada de cron:

```cron
* * * * * cd /ruta/al/backend && php artisan schedule:run >> /dev/null 2>&1
```

En local se puede probar con `php artisan schedule:work` o ejecutando el comando directamente.

## Otros endpoints de billing (API v1, auth Sanctum)

| Acción | Endpoint |
|---|---|
| Listar planes | `GET /plans` |
| Estado suscripción | `GET /billing/subscription` · `GET /me/billing` |
| Suscribirse (checkout Stripe) | `POST /billing/checkout/{plan}` |
| Cancelar / reanudar | `POST /billing/subscription/cancel` · `/resume` |
| Portal de pago (Stripe) | `POST /billing/portal` |
| Facturas | `GET /me/invoices` |

El retorno tras pagar vuelve al frontend: `FRONTEND_URL` en `.env` (p. ej. `http://127.0.0.1:8001`) → `/mi-cuenta?checkout=success|cancelled`.
