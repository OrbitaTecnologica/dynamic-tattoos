# Dynamic Tattoos — Backend (API + Admin Panel)

**Dynamic Tattoos** is a SaaS where a permanent QR tattooed on the skin links to content the owner can change at any time (a link, image gallery, video, or link page). The QR never changes; the destination does — so the tattoo "evolves" without a new tattoo.

## What this repository is

This repo is the **backend**. After the legacy client-web layer was removed, it has exactly **two faces** (plus essential public routes):

1. **REST API (`/api/v1/*`)** — consumed by the customer-facing SPA, which lives in a **separate repo: `dynamic-tattoos-frontend`**. Auth via **Laravel Sanctum** (Bearer tokens). Response envelope: `{ "data": … }` on success, `{ "error": { code, message, status, errors } }` on failure (validation under `error.errors.*`).
2. **Admin panel (`/admin/*`)** — internal tool for administrators (`role = admin`), built with **Livewire 3** + session auth (Breeze). Manages **every** entity: users, plans, subscriptions, referrals, tattoos & content, link pages, companies, teams, storage/uploads, activity log and API tokens.

Essential **public web** routes: the QR scan landing (`/t/{shortCode}`, records the scan), public link pages (`/u/{slug}`), the Stripe webhook, and the API docs.

> ⚠️ The customer-facing website (landing, login/registration, "Mi Cuenta", QR Studio) is **NOT** in this repo — it's the `dynamic-tattoos-frontend` SPA, which talks to this API. There is **no client dashboard** here anymore.

## Features

- **Plans & subscriptions** — annual plans via **Stripe Checkout + Billing Portal** (Cashier), renewal reminders, optional **Stripe Tax** (IVA). Plans are fully configurable from the admin: price, storage, "featured", "referral/Partner", and a **per-plan referral reward**.
- **Referrals / Partner program** — a referral QR for shops, a monitoring panel, and a reward (per-plan € or global fallback) credited to the Partner's Stripe balance when a referred user pays.
- **Account** — profile, company (fiscal/NIF-IVA), preferences, notifications, real **TOTP 2FA**, sessions (Sanctum tokens), storage usage + packs, **team members**, **link pages** (Linktree-style), and an **activity log** (spatie).
- **Admin panel** — full CRUD / monitoring across all of the above.

> Project knowledge skills live in the **frontend** repo under `.claude/skills/`: `dynamic-tattoos-backend`, `dynamic-tattoos-data-model`, `dynamic-tattoos-business`, `dynamic-tattoos-frontend`.

---

## Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Framework | Laravel | ^13.7 (simplified directory structure) |
| Admin panel | Livewire | 3.x (session auth, `role = admin`) |
| API auth | Laravel Sanctum | ^4.0 (Bearer tokens) |
| Billing | Laravel Cashier + Stripe | ^15.0 (Checkout, Portal, webhooks, optional Stripe Tax) |
| 2FA | Custom TOTP (`TotpService`) | RFC 6238 — avoids `ext-bcmath` |
| Audit log | `spatie/laravel-activitylog` | ^4.8 (`config/activitylog.php` committed) |
| Database | MySQL | 8.4.8 |
| Frontend build | Vite + Tailwind CSS | latest (admin panel + login assets) |
| QR generation | `simplesoftwareio/simple-qrcode` | Error Correction Level `H` |
| PHP | PHP | 8.2+ (strict types required) |
| Storage | `Storage::disk('public')` | symlinked via `php artisan storage:link` |
| Cache | Laravel Cache (file/redis) | `Cache::remember` pattern |

---

## How It Works

1. A user registers and creates a **Tattoo** entry. The platform generates a unique 8–12 character `short_code`.
2. A QR code is generated encoding the URL `https://app.com/t/{short_code}` with error correction level `H` (survives skin distortion).
3. The owner can attach one active **TattooContent** row to the tattoo — a link, image gallery, or embedded video.
4. Every QR scan hits `GET /t/{shortCode}`, which reads the active content from cache (5-minute TTL) and renders or redirects accordingly.
5. The owner can swap content at any time from the **SPA** (or an admin from the panel). The cache key `tattoo_content_{shortCode}` is invalidated on every change.

---

## PHP & Laravel Conventions

### Always Required
- Every PHP file **must** begin with `declare(strict_types=1);`.
- Follow **PSR-12** code style throughout (4-space indent, blank line before `return`, etc.).
- Use **PHP 8.2+ named arguments and match expressions** when they improve clarity.
- Prefer `final class` for controllers, Livewire components, policies and service classes — only omit `final` when inheritance is explicitly needed.
- Use **constructor property promotion** wherever possible.
- Type-hint every method parameter and return type, including `void`, `never`, and union types.

### Eloquent & Database
- Use **Eloquent scopes** (`scopeActive`, `scopeByShortCode`) instead of raw query clauses in controllers.
- **Never** use `DB::raw` unless there is no Eloquent alternative.
- Wrap multi-step write operations in `DB::transaction(function(): void { … })`.
- Bust the cache key `tattoo_content_{shortCode}` via `Cache::forget(…)` every time a `TattooContent` row changes state (activated, updated, deleted).

### Routing & Authorization
- Use `abort_if` / `abort_unless` instead of manual `if/throw` guards in controllers and Livewire actions.
- Route model binding is preferred over manual `findOrFail` in controller arguments.
- Always use named routes (`route('tattoo.show', …)`) — never hardcode URL strings.
- Policies are auto-discovered by Laravel 11; implement `authorize()` calls in Livewire `mount()` methods for ownership checks.

---

## Database Conventions

- Table names: **snake_case plural** (`tattoos`, `tattoo_contents`).
- Foreign keys: `{model}_id` pattern with `constrained()->cascadeOnDelete()`.
- Boolean columns default to `false` unless a safe default is `true` (e.g., `is_active`).
- Use **composite indexes** for multi-column lookups (e.g., `(short_code, is_active)`). 
- The `payload` column is a native MySQL **JSON** type — cast to `'array'` in Eloquent `casts()`.
- `short_code` is always 8–12 URL-safe alphanumeric characters. Use `Tattoo::generateUniqueShortCode()` — never set it manually.

---

## Livewire 3 Conventions

- Component classes live in `app/Livewire/`; views live in `resources/views/livewire/`.
- Use `#[Computed]` for derived data; never compute in the `render()` method directly.
- Use `wire:model.live.debounce.400ms` for text inputs to avoid flooding the server.
- Use `wire:model.live` (no debounce) for `<select>` and `<input type="checkbox">`.
- Use `wire:loading.attr="disabled"` + `wire:target="…"` on every action button.
- Use `wire:confirm="…"` for destructive actions (e.g., image deletion).
- Dispatch browser events with `$this->dispatch('event-name')` after successful mutations.
- File uploads use `WithFileUploads`; always store to `Storage::disk('public')` with a path scoped to the entity (e.g., `gallery/{shortCode}/`).
- Reset validation errors in `updated{Property}` hooks when the user changes context (e.g., switches content type tab).

---

## Setup

### Requirements

- PHP 8.2+ with extensions: `pdo_mysql`, `gd`, `mbstring`, `fileinfo`
- MySQL 8.4+
- Node.js 18+ (for Vite)
- Composer 2.x

### Installation

**1. Clone and install dependencies:**

```bash
git clone <repo-url> dynamic-tattoos
cd dynamic-tattoos

composer install
npm install
```

**2. Configure environment:**

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dynamic_tattoos
DB_USERNAME=root
DB_PASSWORD=
```

**3. Initialize database and storage:**

```bash
php artisan migrate
php artisan db:seed        # admin user, demo client, plans, storage packs
php artisan storage:link
```

The default admin (from `AdminUserSeeder`) is `admin@dynamictattoos.test` / `Admin1234!` — **change it in production**.

**4. Build frontend assets and start the server:**

```bash
npm run build
php artisan serve
```

The application will be available at `http://localhost:8000`.

For **development** with hot reload, run in separate terminals:
```bash
# Terminal 1
npm run dev

# Terminal 2
php artisan serve
```

### Deployment notes

- **Admin access:** seed an admin (above) to reach `/admin`. Non-admins get `403`; guests are redirected to `/login`.
- **Subdirectory (`/backend`):** if the app is served under a subpath, set `APP_URL=https://your-domain/backend`. The app forces the root URL from `APP_URL` (`AppServiceProvider`), so `route()`, `asset()`/`@vite` and Livewire all carry the prefix. The root redirect uses a named route (not a hardcoded `/admin`) so it keeps the prefix. **Re-run `php artisan config:cache` after changing `APP_URL`.**
- **`config:cache` + Activity Log:** `config/activitylog.php` is committed so the spatie config survives `config:cache` (otherwise the `activity_log` table name resolves empty). Always `php artisan config:clear` after editing `.env`.
- **Scheduler:** enable Laravel's cron (`* * * * * php artisan schedule:run`) so `subscriptions:send-renewal-reminders` runs (renewal reminder ~30 days before the annual charge).
- **Stripe:** point the webhook to `/stripe/webhook`; run `php artisan stripe:sync-plans` after creating/editing plans so Stripe Products/Prices stay in sync.
- **Assets:** run `npm run build` on deploy — the admin panel and the admin login render via `@vite`. Admin Livewire layouts load **CSS only** (Alpine comes bundled with Livewire; loading `app.js` there would start a second Alpine and break `wire:*`).

---

## Directory Structure

```
app/
  Http/Controllers/
    Api/V1/              # REST API controllers (consumed by the SPA)
    *                    # public web: TattooRedirect, LinkPage, StripeWebhook, HomeRedirect
  Livewire/Admin/        # Admin panel components (Livewire)
  Models/                # Eloquent models
  Policies/              # Authorization policies (auto-discovered)
  Services/              # Billing (Cashier gateway, Stripe Tax), Referrals, Totp, Uploads, LinkCatalog
  Listeners/             # HandleCashierWebhook (Stripe subscription/payment events)
  Console/Commands/      # stripe:sync-plans, subscriptions:send-renewal-reminders
database/migrations/
resources/views/
  admin/                 # Admin panel pages (extend layouts/admin)
  livewire/admin/        # Admin Livewire component views
  tattoo/                # Public QR scan destination
  public link pages + auth (admin login / password reset)
routes/
  api.php                # /api/v1/* (Sanctum)
  web.php                # / → /admin, /t/{code}, /u/{slug}, /stripe/webhook, /admin/*
  auth.php               # admin login + password reset
```

---

## Routes

| Method | URI | Name | Description |
|---|---|---|---|
| `GET` | `/` | `home` | Redirects to `/admin` |
| `GET` | `/t/{shortCode}` | `tattoo.show` | Public QR scan destination (records the scan) |
| `GET` | `/u/{slug}` | `link-page.show` | Public link page (Linktree-style) |
| `POST` | `/stripe/webhook` | `cashier.webhook` | Stripe / Cashier webhook |
| `*` | `/admin/*` | `admin.*` | Admin panel — `auth` + `admin` role (Livewire) |
| `*` | `/api/v1/*` | `api.v1.*` | REST API — Sanctum (see `docs/api/openapi.yaml`) |

---

## API Contract (OpenAPI)

The API v1 contract is documented in:

- `docs/api/openapi.yaml`

It covers auth, tattoos, plans, billing, and the standardized API error envelope (`401`, `403`, `404`, `422`).

You can validate or visualize it with tools like Swagger Editor or Redoc.

Local docs endpoints:

- `/docs/api` (Redoc UI)
- `/docs/api/openapi.yaml` (raw OpenAPI YAML)

E2E operations runbook:

- `docs/operations/stripe-test-mode-e2e.md`

Billing incident response runbook:

- `docs/operations/billing-incident-response.md`

### Runbook Execution Log (2026-05-23)

Latest Stripe test-mode E2E execution completed successfully in local environment (`http://127.0.0.1:8000`).

Scope executed:

- Checkout completion with Stripe test card (`4242 4242 4242 4242`) and redirect back to app.
- Webhook ingestion and idempotent processing for subscription lifecycle events.
- API lifecycle transitions for subscription cancel and resume.

Operational evidence captured:

- `subscriptions` latest row for `owner@example.com`: `stripe_status=active`, `stripe_price=price_1TaIT9HNn4kAnu3BsUDQDWf2`, `ends_at=null`.
- `/api/v1/billing/subscription` final state: `status=activa` with active Stripe subscription id.
- `stripe_webhook_events` processed `customer.subscription.created` and `customer.subscription.updated` events for the checkout and resume cycle.

Notes:

- Immediately after resume, one read briefly returned `grace_period` before webhook propagation completed; subsequent check returned `activa` and DB state matched (`ends_at=null`).
- This behavior is expected eventual consistency between direct API actions and asynchronous webhook synchronization.

---

## Content Types

Each tattoo has one active `TattooContent` row. The `payload` JSON column structure varies by type:

```jsonc
// type = "link"
{ "url": "https://...", "label": "string|null", "open_in_new_tab": true }

// type = "gallery"
{ "title": "string|null", "images": ["gallery/shortCode/file.jpg", ...] }

// type = "video"
{ "url": "string", "platform": "youtube|vimeo|tiktok", "autoplay": false, "title": "string|null" }
```

---

## QR Code Generation

QR codes are generated as **SVG** (vector, resolution-independent) with the highest error correction level:

```php
QrCode::format('svg')
    ->errorCorrection('H')   // highest redundancy — survives skin distortion
    ->size(300)
    ->generate(route('tattoo.show', $tattoo->short_code));
```

**Error correction level `H` is non-negotiable** — it allows the QR to remain scannable even with up to 30% of the code obscured by skin distortion or scarring. Never embed the QR image as a rasterised PNG in the user dashboard; always render as SVG.

---

## Caching

- **Key pattern:** `tattoo_content_{shortCode}`
- **TTL:** 300 seconds (5 minutes), controlled by `TattooRedirectController::CACHE_TTL_SECONDS`
- **Invalidation:** `Cache::forget("tattoo_content_{$shortCode}")` is called by the API (`Api\V1\TattooContentController@activate`) and by the admin `TattooList` actions (activate version / toggle / delete) whenever a `TattooContent` row changes state.

---

## File Storage

- All user-uploaded media is stored on `Storage::disk('public')`.
- Path convention: `gallery/{shortCode}/{filename}`.
- Always call `Storage::disk('public')->delete($path)` before removing the DB record.
- Public URLs are generated with `Storage::url($path)` — never with `asset()` or hardcoded `/storage/` prefixes.
- Accepted MIME types: `jpeg`, `png`, `webp`, `gif`. Maximum 2 MB per file.
- `php artisan storage:link` must be documented in deployment instructions.

---

## Security

- **Open redirect (CWE-601):** Before issuing `redirect()->away($url)`, validate with `filter_var($url, FILTER_VALIDATE_URL)` **and** assert `parse_url($url, PHP_URL_SCHEME)` is in `['http', 'https']`.
- **Path traversal / injection:** Validate `shortCode` against `/^[a-zA-Z0-9]{1,12}$/` at the route level _and_ inside the controller before any DB query.
- **Mass assignment:** Only list fields in `$fillable`; never use `$guarded = []`.
- **Authorization:** Every Livewire component that mutates data must call `$this->authorize()` in `mount()`. Policies are auto-discovered by Laravel 11.
- **File uploads:** Restrict MIME types to `jpeg,png,webp,gif` with a 2 MB limit per file.
- **XSS:** Always use `{{ }}` (auto-escaped) in Blade. Use `{!! !!}` only for explicitly sanitised HTML (e.g., QR SVG output from `simplesoftwareio/simple-qrcode`).
- **CSRF:** All mutating routes must be inside `web` middleware group (which includes `VerifyCsrfToken`). Never disable CSRF for authenticated routes.

---

## Frontend Conventions

- **Styling:** Tailwind utility classes only — no custom CSS files unless unavoidable.
- **Conditional classes:** Use `@class([…])` for conditional Blade class merging.
- **Client-side state:** Use Alpine.js (`x-data`, `x-entangle`) for purely client-side toggle state that does not need a server round-trip.
- **Livewire/Alpine sync:** Use `@entangle('property').live` to sync Livewire properties into Alpine.
- **Mobile preview:** The phone frame is a fixed `260px` wide `div` with `sticky top-6` positioning.
- **Public pages:** All public-facing pages (QR scan destination) must have `<meta name="robots" content="noindex, nofollow">`.
- **Iframes:** Must include `referrerpolicy="strict-origin-when-cross-origin"` and `loading="lazy"`.

---

## What NOT to Do

- Do **not** add `$guarded = []` to any model.
- Do **not** use `errorCorrection('L')` or omit error correction entirely for QR generation.
- Do **not** redirect to user-supplied URLs without the scheme whitelist check.
- Do **not** store absolute server paths in the `payload` JSON — always store relative `Storage::disk('public')` paths.
- Do **not** compute derived data inside `render()` — use `#[Computed]`.
- Do **not** invalidate the entire cache; only forget the specific `tattoo_content_{shortCode}` key.
- Do **not** use `{!! $userContent !!}` in Blade unless the value comes from a trusted internal source (e.g., QR SVG).
- Do **not** create controllers with multiple public actions — use invokable (`__invoke`) or resource controllers.
