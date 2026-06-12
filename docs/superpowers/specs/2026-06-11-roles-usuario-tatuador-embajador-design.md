# Programa Embajador: roles Usuario / Tatuador / Embajador

**Fecha**: 2026-06-11
**Estado**: Diseño aprobado, pendiente plan de implementación
**Alcance**: Backend `dynamic-tattoos` + ajustes mínimos en frontend marketing `dynamic-tattoos-frontend`

## 1. Contexto y motivación

Hoy el modelo de usuario distingue 3 valores en el enum `users.role`: `admin`, `artist`, `user`. La lógica de referidos (`ReferralService`) está implementada y funcional, y los 5 planes ya existen en `PlanSeeder` (Embajador, Start, Plus, Pro, Negocio/`empresa`). Lo que falta es:

1. Hacer explícito un cuarto rol `ambassador` para usuarios cuya identidad primaria es referir (no tatuarse ni tatuar).
2. Distinguir los planes que pagan en crédito (canjeable en cuota) de los que pagan en cash (retirable real). Solo Negocio retira en cash.
3. Un sistema de tiers (Bronce / Plata / Oro) basado en número de referidos pagados, que multiplica la comisión base del plan para usuarios con rol Embajador.
4. Tres puntos de entrada de registro diferenciados (Cliente / Tatuador / Embajador), reusando el endpoint actual cuando aplica y el flujo existente de `TatuadorSolicitud` para Tatuador.
5. Link público compartible por embajador (`/e/{slug}`) con landing mínima.
6. Resumen de embajador en `/me/ambassador/summary` para alimentar un dashboard frontend distinto.
7. Eliminar el formulario de mensajes embebido en la landing de Embajadores (será reemplazado por chatbot en otra iniciativa).

## 2. Decisiones de diseño tomadas

| Decisión | Elección |
|---|---|
| Documentación | Un solo spec para todo el alcance |
| Multi-rol | Cualquier rol puede referir; el rol Embajador es la identidad primaria, no una capacidad exclusiva |
| Tabla `tatuadores` | Se mantiene tal cual está, sin merge con `users`. Solo se vincula `user_id` cuando admin aprueba una solicitud |
| Nomenclatura enum | Inglés en código (`ambassador`), Español en UI (`Embajador`) — consistente con el patrón actual (`artist`/`user`) |
| Registro | 3 landings/rutas en frontend, backend reusa endpoints existentes |
| Planes ↔ Roles | Tatuador → Start/Plus/Pro · Embajador → Embajador/Negocio · Cliente → sin plan |
| Tier multiplier | Aplica **solo** a usuarios con `role = 'ambassador'`. Tatuadores reciben comisión base sin multiplicador |
| Landing pública embajador | Versión mínima (nombre + tier + 2 CTAs), sin perfil personalizable en esta fase |
| Aprobación Tatuador | Acción en panel admin web (botón "Aprobar" en `/admin/tatuadores`) |
| Precios y comisiones específicos | TBD — el spec solo cubre estructura, los valores se ajustan en implementación |

## 3. Modelo de datos

### 3.1 Migraciones

**`2026_xx_xx_000001_add_ambassador_role_to_users_table.php`**
```php
DB::statement("ALTER TABLE users MODIFY COLUMN role
    ENUM('admin', 'artist', 'user', 'ambassador')
    NOT NULL DEFAULT 'user'");
```

**`2026_xx_xx_000002_create_ambassador_tiers_table.php`**
```php
Schema::create('ambassador_tiers', function (Blueprint $t) {
    $t->id();
    $t->string('slug')->unique();              // 'bronze', 'silver', 'gold'
    $t->string('label_es');                    // 'Bronce', 'Plata', 'Oro'
    $t->unsignedInteger('min_referrals');      // 0, 10, 50 (TBD)
    $t->decimal('commission_multiplier', 5, 2); // 1.00, 1.25, 1.50 (TBD)
    $t->unsignedInteger('sort_order');
    $t->timestamps();
});
```

**`2026_xx_xx_000003_add_ambassador_fields_to_users_table.php`**
```php
Schema::table('users', function (Blueprint $t) {
    $t->foreignId('ambassador_tier_id')
        ->nullable()
        ->after('referred_by')
        ->constrained('ambassador_tiers')
        ->nullOnDelete();
    $t->string('ambassador_slug', 50)
        ->nullable()
        ->unique()
        ->after('referral_code');
});
```

**`2026_xx_xx_000004_add_payout_and_roles_to_plans_table.php`**
```php
Schema::table('plans', function (Blueprint $t) {
    $t->enum('payout_mode', ['credit', 'cash'])
        ->default('credit')
        ->after('referral_reward');
    $t->json('allowed_roles')->nullable()->after('payout_mode');
});
```

**`2026_xx_xx_000005_link_tatuador_solicitudes_to_users.php`**
```php
Schema::table('tatuador_solicitudes', function (Blueprint $t) {
    $t->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
    $t->timestamp('approved_at')->nullable();
});
```

### 3.2 Seeders

**`AmbassadorTierSeeder`** (nuevo)

3 filas: Bronce, Plata, Oro. Valores de `min_referrals` y `commission_multiplier` configurables — ejemplos: 0/1.00, 10/1.25, 50/1.50.

**`PlanSeeder`** (modificar)

Agregar a cada plan existente:
- `embajador` → `payout_mode: 'credit'`, `allowed_roles: ['ambassador']`
- `start`, `plus`, `pro` → `payout_mode: 'credit'`, `allowed_roles: ['artist']`
- `empresa` → `payout_mode: 'cash'`, `allowed_roles: ['ambassador']`

### 3.3 Modelo `User` — helpers añadidos

```php
public function isAmbassador(): bool { return $this->role === 'ambassador'; }
public function tier(): BelongsTo { return $this->belongsTo(AmbassadorTier::class, 'ambassador_tier_id'); }
public function successfulReferralsCount(): int {
    return $this->referralsMade()->where('status', Referral::STATUS_PAID)->count();
}
```

### 3.4 Modelo `AmbassadorTier` (nuevo)

```php
final class AmbassadorTier extends Model
{
    protected $fillable = ['slug', 'label_es', 'min_referrals', 'commission_multiplier', 'sort_order'];
    protected $casts = ['commission_multiplier' => 'decimal:2'];
}
```

## 4. Sistema de tiers

### 4.1 Servicio nuevo: `app/Services/Referrals/AmbassadorTierService.php`

```php
final class AmbassadorTierService
{
    public function recompute(User $user): ?AmbassadorTier
    {
        if (! $user->isAmbassador()) return null;

        $count = $user->successfulReferralsCount();

        $newTier = AmbassadorTier::query()
            ->where('min_referrals', '<=', $count)
            ->orderByDesc('min_referrals')
            ->first();

        if ($newTier === null) return null;

        $currentSort = $user->tier?->sort_order ?? -1;
        if ($newTier->sort_order > $currentSort) {
            $user->forceFill(['ambassador_tier_id' => $newTier->id])->save();
            activity('ambassador')->causedBy($user)->event('tier_promoted')
                ->withProperties(['from_sort' => $currentSort, 'to_tier' => $newTier->slug])
                ->log('Promovido a '.$newTier->label_es);
        }

        return $newTier;
    }

    public function defaultTier(): AmbassadorTier
    {
        return AmbassadorTier::query()->orderBy('min_referrals')->firstOrFail();
    }
}
```

### 4.2 Reglas

- Solo promueve, nunca degrada
- Trigger: invocado por `ReferralService::rewardOnPaid()` después de marcar `STATUS_PAID`
- Tatuadores: early return — no obtienen tier ni multiplicador

### 4.3 Modificación a `ReferralService::rewardOnPaid()`

Reemplazar línea 90 actual:
```php
$rewardEuros = $referrer?->plan?->referral_reward ?? config('billing.referral_reward', 0);
```

Por:
```php
$baseEuros = $referrer?->plan?->referral_reward ?? config('billing.referral_reward', 0);
$multiplier = ($referrer?->role === 'ambassador')
    ? ($referrer?->tier?->commission_multiplier ?? 1.0)
    : 1.0;
$rewardEuros = $baseEuros * $multiplier;
```

Y al final del método, después del `forceFill` con `STATUS_PAID`:
```php
app(AmbassadorTierService::class)->recompute($referrer);
```

### 4.4 Decisiones pendientes

- Valores exactos de `min_referrals` por tier (0/10/50 son ejemplos)
- Valores exactos de `commission_multiplier` (1.00/1.25/1.50 son ejemplos)
- Notificación email opcional al promover (sugerido, a confirmar)

## 5. Flujo de registro

### 5.1 Estrategia

UN solo endpoint API `POST /api/v1/auth/register` con parámetro `role`. El frontend tendrá 3 landings que llaman al endpoint con valores diferentes. Esto evita duplicar verificación de email, attach de referral, etc.

El rol `artist` NO se asigna por este endpoint — sigue su flujo dedicado vía `TatuadorSolicitud` + aprobación admin.

### 5.2 Cambios en `RegisterRequest`

Agregar reglas:
```php
'role' => 'required|in:user,ambassador',
'plan' => 'nullable|string|exists:plans,slug',
```

### 5.3 Cambios en `AuthTokenController::register()`

Línea 50 actual — cambiar:
```php
'role' => 'user',
```
Por:
```php
'role' => (string) $request->input('role', 'user'),
```

Después del `$referrals->attach()` (después de línea 53), añadir:
```php
if ($user->role === 'ambassador') {
    $user->forceFill([
        'ambassador_tier_id' => app(AmbassadorTierService::class)->defaultTier()->id,
        'ambassador_slug' => $this->generateUniqueAmbassadorSlug($user->name),
    ])->save();

    if (! $request->input('plan')) {
        $embPlan = Plan::query()->active()->where('slug', 'embajador')->first();
        if ($embPlan !== null) {
            $user->forceFill(['plan_id' => $embPlan->id])->save();
        }
    }
}
```

Helper privado `generateUniqueAmbassadorSlug(string $name): string`:
- Aplica `Str::slug($name)`
- Si choca con `users.ambassador_slug`, agrega sufijo numérico incremental
- Aplica blacklist de palabras reservadas (admin, login, register, api, etc.)

### 5.4 Validación de plan-rol en checkout Stripe

Antes de asignar un plan vía webhook, el código que procesa el checkout debe verificar que `$plan->allowed_roles` (si está poblado) contiene el `$user->role`. Si no, rechazar con error 422 y notificar al admin (no debería pasar si el frontend filtró correctamente, es defensa en profundidad).

### 5.5 Aprobación de Tatuador desde panel admin

Endpoint nuevo: `POST /api/v1/admin/tatuadores/solicitudes/{solicitud}/aprobar`
Middleware: `['auth:sanctum', 'admin']`

Lógica:
```php
public function approve(TatuadorSolicitud $solicitud): JsonResponse
{
    if ($solicitud->status !== TatuadorSolicitud::STATUS_PENDING) {
        abort(422, 'Solicitud ya procesada');
    }

    $user = User::query()->create([
        'name'  => $solicitud->name,
        'email' => mb_strtolower($solicitud->email),
        'password' => Str::random(64),
        'role'  => 'artist',
    ]);

    Tatuador::query()->create([
        'user_id' => $user->id,
        // copiar campos relevantes de la solicitud
    ]);

    $solicitud->forceFill([
        'status'      => TatuadorSolicitud::STATUS_APPROVED,
        'approved_at' => now(),
        'user_id'     => $user->id,
    ])->save();

    Password::broker()->createToken($user);
    Mail::to($user->email)->send(new TatuadorApprovedMail($user));

    return response()->json(['data' => ['user_id' => $user->id]]);
}
```

Frontend admin: agregar botón "Aprobar" en `resources/views/admin/tatuadores.blade.php` que invoca este endpoint y refresca tabla.

Mail nuevo: `app/Mail/TatuadorApprovedMail.php` con link de reset password al frontend.

### 5.6 Mapa final de entradas de registro

| Landing frontend | Llama a | Resultado |
|---|---|---|
| `/register/cliente` | `POST /auth/register` con `role=user` | User creado con `role='user'` |
| `/register/embajador` | `POST /auth/register` con `role=ambassador` | User con rol Embajador + plan Embajador + tier Bronce + slug |
| `/register/tatuador` | `POST /tatuadores/solicitud` | Solicitud creada, pendiente de aprobación admin |
| Admin "Aprobar" | `POST /admin/tatuadores/solicitudes/{id}/aprobar` | User con rol Artist + perfil tatuador + email bienvenida |

## 6. Link personalizado de Embajador

### 6.1 Ruta pública

En `routes/web.php`:
```php
Route::get('/e/{slug}', [PublicAmbassadorController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('ambassador.public');
```

### 6.2 Controller

```php
public function show(string $slug, ReferralService $referrals): Response
{
    $ambassador = User::query()
        ->where('ambassador_slug', $slug)
        ->where('role', 'ambassador')
        ->firstOrFail();

    $referrals->recordVisit(
        $ambassador->referral_code,
        request()->ip(),
        request()->userAgent()
    );

    return view('ambassador.landing', [
        'ambassador' => [
            'name' => $ambassador->name,
            'tier_label' => $ambassador->tier?->label_es,
            'slug' => $slug,
        ],
        'referral_code' => $ambassador->referral_code,
    ]);
}
```

### 6.3 Landing mínima

Blade nuevo `resources/views/ambassador/landing.blade.php`:
- Encabezado: "Recomendado por {$ambassador['name']}" + badge del tier
- Descripción breve de Dynamic Tattoos
- CTA primario: "Crear cuenta" → `/register/cliente?ref={referral_code}`
- CTA secundario: "Únete como Embajador" → `/register/embajador?ref={referral_code}`
- Sin estadísticas privadas, sin ganancias, sin datos sensibles

### 6.4 Edición del slug

Endpoint nuevo: `PATCH /api/v1/me/ambassador/slug`
Middleware: `['auth:sanctum']`
Body: `{ "slug": "carlos-tattoos" }`

Reglas:
- Solo si `auth()->user()->role === 'ambassador'`
- Validación: 3-50 chars, regex `^[a-z0-9-]+$`
- Único en `users.ambassador_slug`
- Blacklist de palabras reservadas
- Rate limit: máximo 3 cambios por mes por usuario, auditado con activity log

## 7. Dashboard por rol

### 7.1 `UserResource` ampliado

Añadir campos:
```php
'is_ambassador' => $this->isAmbassador(),
'ambassador_tier' => $this->whenLoaded('tier', fn () => [
    'slug' => $this->tier->slug,
    'label' => $this->tier->label_es,
    'multiplier' => (float) $this->tier->commission_multiplier,
]),
'ambassador_slug' => $this->ambassador_slug,
```

(`role`, `is_artist`, etc. ya existen.)

### 7.2 Endpoint resumen embajador

Nuevo: `GET /api/v1/me/ambassador/summary`
Middleware: `['auth:sanctum']`
Solo si `role === 'ambassador'`, sino 403.

Respuesta:
```json
{
  "data": {
    "tier": { "slug": "silver", "label": "Plata", "multiplier": 1.25 },
    "next_tier": { "slug": "gold", "label": "Oro", "missing_refs": 35 },
    "referrals": { "total_paid": 15, "total_pending": 3 },
    "earnings_cents": 18750,
    "payout_mode": "credit",
    "link_url": "https://dynamic-tattoos.com/e/carlos-tattoos"
  }
}
```

`earnings_cents` se calcula sumando `referrals.reward_cents` donde `status = 'paid'`.

### 7.3 Frontend (resumen, fuera del alcance de implementación de este spec)

El frontend del SPA cliente decide qué dashboard renderizar según `user.role`:
- `ambassador` → vista enfocada en referidos, tier, ganancias, link
- `artist` → vista de gestión de tatuajes con widget compacto de referidos
- `user` → vista cliente sin widget de referidos (puede mostrar su `referral_code` opcionalmente)
- `admin` → panel admin existente

## 8. Cleanup de mensajes en landing Embajadores

### 8.1 Ubicación

Archivo: `dynamic-tattoos-frontend/resources/views/embajadores.blade.php`
Líneas aproximadas: 403-440 (bloque `<div class="co-form" id="contactForm">…</div>` + bloque `<div class="co-success">…</div>` siguiente).

### 8.2 Acciones

1. Eliminar el bloque del formulario de contacto y el bloque de éxito asociado
2. Reemplazar con un placeholder de CTA chatbot:
   ```html
   <div class="emb-chatbot-cta">
     <h3>¿Tienes dudas?</h3>
     <p>Habla con nuestro asistente y resolvemos tu duda al instante.</p>
     <button data-chatbot-trigger>Abrir chat</button>
   </div>
   ```
3. Verificar `resources/js/contacto.js` — si solo se usa desde `embajadores.blade.php`, eliminar su import; si se usa también en `home.blade.php`, `partners.blade.php`, footer, etc., mantenerlo

### 8.3 Lo que NO se elimina

- `resources/views/includes/contacto.blade.php` (incluido en otras vistas)
- El formulario de contacto en otras páginas

## 9. Lo que NO cambia

- Tabla `tatuadores` (sin merge, sin renombrar)
- Enum value `artist` (se mantiene, etiqueta UI = "Tatuador")
- Enum value `user` (se mantiene, etiqueta UI = "Usuario"/"Cliente")
- `ReferralService::ensureCode/recordVisit/attach` (solo cambia `rewardOnPaid`)
- Stripe webhook para asignación de planes de pago
- 2FA, password reset, email verification
- Estructura general de `Plan` (solo se agregan 2 campos)
- `routes/web.php:77` admin `/referrals` (sin cambios)

## 10. Resumen de archivos a crear / modificar

### Nuevos
- `app/Services/Referrals/AmbassadorTierService.php`
- `app/Models/AmbassadorTier.php`
- `app/Http/Controllers/Admin/TatuadorSolicitudApprovalController.php` (o método en controller existente)
- `app/Http/Controllers/PublicAmbassadorController.php`
- `app/Http/Controllers/Api/V1/MeAmbassadorController.php` (slug edit + summary)
- `app/Mail/TatuadorApprovedMail.php`
- `database/seeders/AmbassadorTierSeeder.php`
- `resources/views/ambassador/landing.blade.php`
- 5 migraciones nuevas

### Modificados
- `app/Models/User.php` (helpers + relación `tier`)
- `app/Models/Plan.php` (fillable de campos nuevos)
- `app/Http/Controllers/Api/V1/AuthTokenController.php` (bloque ambassador)
- `app/Http/Requests/Api/V1/RegisterRequest.php` (regla `role`)
- `app/Services/Referrals/ReferralService.php` (multiplicador en `rewardOnPaid`)
- `app/Http/Resources/Api/V1/UserResource.php` (campos extra)
- `database/seeders/PlanSeeder.php` (payout_mode + allowed_roles)
- `routes/web.php` (ruta `/e/{slug}`)
- `routes/api.php` (rutas admin approval + me/ambassador)
- `resources/views/admin/tatuadores.blade.php` (botón Aprobar)

### Frontend marketing (`dynamic-tattoos-frontend`)
- `resources/views/embajadores.blade.php` (quitar form contacto)
- `resources/js/contacto.js` (revisar imports)

### Frontend SPA cliente (`dynamic-tattoos-frontend` o equivalente)
- Vistas/componentes de dashboard por rol (fuera del alcance detallado)
- 3 landings de registro (`/register/cliente`, `/register/embajador`, `/register/tatuador`)

## 11. Decisiones pendientes consolidadas

| Tema | Pendiente |
|---|---|
| Precios y comisiones planes | Confirmar discrepancias seeder vs marketing (Negocio 100€ vs 20€, comisiones específicas) |
| `min_referrals` por tier | Confirmar 0/10/50 o ajustar |
| `commission_multiplier` por tier | Confirmar 1.00/1.25/1.50 o ajustar |
| Notificación email al promover tier | ¿Mandar email? Sugerido sí |
| Blacklist de slugs reservados | Lista exacta a definir en implementación |
| Si "primer año GRATIS" del plan Negocio sigue vigente | Confirmar |

## 12. No-objetivos (fuera del alcance)

- Sistema de permisos granular (spatie/laravel-permission) — se evaluará a futuro
- Merge de `tatuadores` con `users` — explícitamente fuera
- Perfil personalizable del embajador (avatar, bio, redes) — fase 2
- Integración del chatbot — proyecto separado
- Cambios en flujo de retiro (`/me/referrals/withdraw`) — ya existe y funciona
- Renombrar enum a español — decisión explícita de mantener inglés
