# GitHub Copilot Instructions — Dynamic Tattoos Platform

## Project overview

**Dynamic Tattoos** is a SaaS platform that links a permanent QR code tattooed on a person's skin to a user-controlled multimedia page. Because the QR code itself never changes, the _destination content_ (links, image galleries, videos) can be updated at any time through a dashboard, allowing the owner to reflect their personal evolution without a new tattoo.

---

## Tech stack

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

## PHP & Laravel conventions

### Always required
- Every PHP file **must** begin with `declare(strict_types=1);`.
- Follow **PSR-12** code style throughout (4-space indent, blank line before `return`, etc.).
- Use **PHP 8.2+ named arguments and match expressions** when they improve clarity.
- Prefer `final class` for controllers, Livewire components, policies and service classes — only omit `final` when inheritance is explicitly needed.
- Use **constructor property promotion** wherever possible.
- Type-hint every method parameter and return type, including `void`, `never`, and union types.
- Annotate generic collections and query builders with `@template` / `@param Builder<self>` PHPDoc only where static analysis requires it.

### Laravel-specific
- Use **Eloquent scopes** (`scopeActive`, `scopeByShortCode`) instead of raw query clauses in controllers.
- **Never** use `DB::raw` unless there is no Eloquent alternative.
- Wrap multi-step write operations in `DB::transaction(function(): void { … })`.
- Bust the cache key `tattoo_content_{shortCode}` via `Cache::forget(…)` every time a `TattooContent` row changes state (activated, updated, deleted).
- Use `abort_if` / `abort_unless` instead of manual `if/throw` guards in controllers and Livewire actions.
- Route model binding is preferred over manual `findOrFail` in controller arguments.
- Always use named routes (`route('tattoo.show', …)`) — never hardcode URL strings.

---

## Database conventions

- Table names: **snake_case plural** (`tattoos`, `tattoo_contents`).
- Foreign keys: `{model}_id` pattern with `constrained()->cascadeOnDelete()`.
- Boolean columns default to `false` unless a safe default is `true` (e.g., `is_active`).
- Use **composite indexes** for multi-column lookups (e.g., `(short_code, is_active)`).
- The `payload` column is a native MySQL **JSON** type — cast to `'array'` in Eloquent `casts()`.
- `short_code` is always 8–12 URL-safe alphanumeric characters. Use `Tattoo::generateUniqueShortCode()` — never set it manually.

### JSON payload schemas (do not deviate)

```jsonc
// type = "link"
{ "url": "string (https)", "label": "string|null", "open_in_new_tab": true }

// type = "gallery"
{ "title": "string|null", "images": ["storage/public path", …] }

// type = "video"
{ "url": "string", "platform": "youtube|vimeo|tiktok", "autoplay": false, "title": "string|null" }
```

---

## Livewire 3 conventions

- Component classes live in `app/Livewire/`.
- Views live in `resources/views/livewire/`.
- Use `#[Computed]` for derived data; never compute in the `render()` method directly.
- Use `wire:model.live.debounce.400ms` for text inputs to avoid flooding the server.
- Use `wire:model.live` (no debounce) for `<select>` and `<input type="checkbox">`.
- Use `wire:loading.attr="disabled"` + `wire:target="…"` on every action button.
- Use `wire:confirm="…"` for destructive actions (e.g., image deletion).
- Dispatch browser events with `$this->dispatch('event-name')` after successful mutations.
- Call `$this->authorize(…)` inside `mount()` for ownership checks — rely on `AuthorizesRequests` trait.
- Reset validation errors in `updated{Property}` hooks when the user changes context (e.g., switches content type tab).
- File uploads use `WithFileUploads`; always store to `Storage::disk('public')` with a path scoped to the entity (e.g., `gallery/{shortCode}/`).

---

## Security requirements (OWASP Top 10)

- **Open redirect (CWE-601)**: Before issuing `redirect()->away($url)`, validate with `filter_var($url, FILTER_VALIDATE_URL)` **and** assert `parse_url($url, PHP_URL_SCHEME)` is in `['http', 'https']`.
- **Path traversal / injection**: Validate `shortCode` against `/^[a-zA-Z0-9]{1,12}$/` at the route level _and_ inside the controller before any DB query.
- **Mass assignment**: Only list fields in `$fillable`; never use `$guarded = []`.
- **Authorization**: Every Livewire component that mutates data must call `$this->authorize()` in `mount()`. Policies are auto-discovered by Laravel 11.
- **File uploads**: Restrict MIME types to `jpeg,png,webp,gif` with a 2 MB limit per file.
- **XSS**: Always use `{{ }}` (auto-escaped) in Blade. Use `{!! !!}` only for explicitly sanitised HTML (e.g., QR SVG output from `simplesoftwareio/simple-qrcode`).
- **CSRF**: All mutating routes must be inside `web` middleware group (which includes `VerifyCsrfToken`). Never disable CSRF for authenticated routes.

---

## QR code generation

Use `simplesoftwareio/simple-qrcode` with:
```php
QrCode::format('svg')
    ->errorCorrection('H')   // highest redundancy — survives skin distortion
    ->size(300)
    ->generate(route('tattoo.show', $tattoo->short_code));
```
- Always use `errorCorrection('H')` — this is non-negotiable for tattoo use.
- Render QR codes as **SVG** (vector, resolution-independent).
- Never embed the QR image as a rasterised PNG in the user dashboard.

---

## Caching strategy

- Cache key pattern: `tattoo_content_{shortCode}` (e.g., `tattoo_content_aB3xQ9mZ`).
- TTL: **300 seconds** (5 minutes). Adjust only via the constant `TattooRedirectController::CACHE_TTL_SECONDS`.
- **Invalidation**: `Cache::forget("tattoo_content_{$tattoo->short_code}")` must be called in:
  - `ManageTattoo::save()`
  - Any future controller or job that activates/deactivates a `TattooContent` row.
- The cached value is a `TattooContent` model instance (or `null`). Do not cache raw query results.

---

## File storage

- All user-uploaded media is stored on `Storage::disk('public')`.
- Path convention: `gallery/{shortCode}/{filename}`.
- Always call `Storage::disk('public')->delete($path)` before removing the DB record.
- Public URLs are generated with `Storage::url($path)` — never with `asset()` or hardcoded `/storage/` prefixes.
- The `php artisan storage:link` command must be documented in deployment instructions.

---

## Blade & frontend conventions

- Tailwind utility classes only — no custom CSS files unless unavoidable.
- Use `@class([…])` for conditional Blade class merging.
- Use Alpine.js (`x-data`, `x-entangle`) for purely client-side toggle state that does not need a server round-trip.
- Use `@entangle('property').live` to sync Livewire properties into Alpine.
- The mobile preview phone frame is a fixed `260px` wide `div` with `sticky top-6` positioning.
- All public-facing pages (QR scan destination) must have `<meta name="robots" content="noindex, nofollow">`.
- `<iframe>` embeds must include `referrerpolicy="strict-origin-when-cross-origin"` and `loading="lazy"`.

---

## Directory structure (Laravel 11 simplified)

```
app/
  Http/Controllers/      # Invokable controllers only (single responsibility)
  Livewire/              # Livewire component classes
  Models/                # Eloquent models
  Policies/              # Authorization policies (auto-discovered)
database/
  migrations/            # Named YYYY_MM_DD_HHMMSS_verb_noun_table.php
resources/views/
  livewire/              # Component views + partials/
  tattoo/                # Public QR destination views
  dashboard/             # Authenticated user views
routes/
  web.php                # All routes (public QR + authenticated dashboard)
```

---

## Naming conventions

| Artefact | Convention | Example |
|---|---|---|
| Controller | `PascalCase` + noun + `Controller` | `TattooRedirectController` |
| Livewire class | `PascalCase` verb+noun | `ManageTattoo`, `TattooViewer` |
| Livewire view | `kebab-case` matching class | `manage-tattoo.blade.php` |
| Model | `PascalCase` singular | `Tattoo`, `TattooContent` |
| Policy | `{Model}Policy` | `TattooPolicy` |
| Migration | `YYYY_MM_DD_HHMMSS_create_{table}_table` | — |
| Route name | `resource.action` | `tattoo.show`, `tattoos.manage` |
| Cache key | `{entity}_{identifier}` | `tattoo_content_aB3xQ9mZ` |
| Event (dispatch) | `kebab-case` | `content-saved`, `images-uploaded` |

---

## What NOT to do

- Do **not** add `$guarded = []` to any model.
- Do **not** use `errorCorrection('L')` or omit it entirely for QR generation.
- Do **not** redirect to user-supplied URLs without the scheme whitelist check.
- Do **not** store absolute server paths in the `payload` JSON — always store relative `Storage::disk('public')` paths.
- Do **not** compute derived data inside `render()` — use `#[Computed]`.
- Do **not** invalidate the entire cache; only forget the specific `tattoo_content_{shortCode}` key.
- Do **not** use `{!! $userContent !!}` in Blade unless the value comes from a trusted internal source (e.g., QR SVG).
- Do **not** create controllers with multiple public actions — use invokable (`__invoke`) or resource controllers.
