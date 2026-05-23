# Dynamic Tattoos

A SaaS platform that links a permanent QR code tattooed on a person's skin to a user-controlled multimedia page. Because the QR code itself never changes, the destination content (links, image galleries, videos) can be updated at any time through a dashboard, allowing the owner to reflect their personal evolution without a new tattoo.

---

## Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Framework | Laravel | 11.51 (simplified directory structure) |
| Reactive UI | Livewire | 3.x |
| Database | MySQL | 8.4.8 |
| Frontend build | Vite + Tailwind CSS | latest |
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
5. The owner can swap content at any time from the dashboard. The cache key `tattoo_content_{shortCode}` is invalidated on every save.

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
php artisan storage:link
```

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

---

## Directory Structure

```
app/
  Http/Controllers/      # Invokable controllers (single responsibility)
  Livewire/              # Livewire component classes
  Models/                # Eloquent models
  Policies/              # Authorization policies (auto-discovered)
database/
  migrations/
resources/views/
  livewire/              # Component views + partials/
  tattoo/                # Public QR destination views
  dashboard/             # Authenticated user views
routes/
  web.php                # All routes (public QR + authenticated dashboard)
```

---

## Routes

| Method | URI | Name | Description |
|---|---|---|---|
| `GET` | `/t/{shortCode}` | `tattoo.show` | Public QR scan destination |
| `GET` | `/dashboard/tattoos/{tattoo}/manage` | `tattoos.manage` | Owner dashboard (auth required) |

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
- **Invalidation:** `Cache::forget("tattoo_content_{$shortCode}")` is called in `ManageTattoo::save()` and any future job that activates/deactivates a `TattooContent` row.

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
