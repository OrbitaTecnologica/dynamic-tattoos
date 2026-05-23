# Stripe Test Mode E2E Runbook

This runbook validates the full subscription lifecycle against Stripe Test Mode:

- Checkout URL generation
- Stripe checkout completion
- Webhook delivery and signature validation
- Local synchronization (`subscriptions`, `users.plan_id`, `users.plan_expires_at`)
- Idempotency (`stripe_webhook_events`)

## 1) Prerequisites

1. Project is running with migrated database.
2. Queue worker is running (notifications/sync jobs).
3. Stripe account in **Test mode**.
4. Stripe product + recurring price available (`price_...`).
5. CLI tools:
   - `php`
   - `curl`
   - `mysql` (or preferred SQL client)
   - `stripe` CLI (recommended)

### Quick env check

Required in `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_WEBHOOK_TOLERANCE=300
```

## 2) Start local services

In separate terminals:

```bash
# API / app
php artisan serve

# Queue worker (required for notifications)
php artisan queue:work --queue=payments,notifications,default
```

## 3) Configure Stripe webhook tunnel

Start Stripe listener and forward events to local app:

```bash
stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
```

Copy the printed signing secret (`whsec_...`) and set it in `.env` as `STRIPE_WEBHOOK_SECRET`.

Then clear config cache:

```bash
php artisan config:clear
```

## 4) Seed/prepare test data

Create an authenticated user and a plan with valid `stripe_price_id`.

### Option A: API admin flow (recommended)

1. Login and get bearer token:

```bash
curl -s -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password","device_name":"stripe-e2e"}'
```

2. Create/ensure plan (admin token required) with Stripe test price:

```bash
curl -s -X POST http://127.0.0.1:8000/api/v1/admin/plans \
  -H "Authorization: Bearer <ADMIN_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Pro Monthly Test",
    "billing_cycle":"monthly",
    "stripe_price_id":"price_xxx_test",
    "price":29.99,
    "features":["all"],
    "max_tattoos":20,
    "is_active":true,
    "sort_order":1
  }'
```

### Option B: tinker/manual SQL

Use `php artisan tinker` or SQL inserts if preferred.

## 5) Create checkout session from API

```bash
curl -s -X POST http://127.0.0.1:8000/api/v1/billing/checkout/<PLAN_ID> \
  -H "Authorization: Bearer <USER_TOKEN>" \
  -H "Content-Type: application/json"
```

Expected response:

```json
{"data":{"checkout_url":"https://checkout.stripe.com/..."}}
```

Open `checkout_url` in browser and complete payment with a Stripe test card:

- `4242 4242 4242 4242`
- Any future expiration date
- Any CVC

## 6) Trigger additional webhook events (optional but recommended)

Use Stripe CLI to simulate lifecycle updates:

```bash
stripe trigger customer.subscription.updated
stripe trigger customer.subscription.deleted
stripe trigger invoice.payment_failed
```

## 7) Verify database synchronization

### 7.1 Subscriptions table

```sql
SELECT id, user_id, stripe_id, stripe_status, stripe_price, ends_at
FROM subscriptions
ORDER BY id DESC
LIMIT 5;
```

### 7.2 User plan linkage

```sql
SELECT id, email, plan_id, plan_expires_at, stripe_id
FROM users
WHERE email = 'owner@example.com';
```

Expected:

- `plan_id` matches plan mapped by `stripe_price_id` when subscription is valid.
- `plan_expires_at` updates when cancellation/grace period applies.

### 7.3 Idempotency log

```sql
SELECT stripe_event_id, type, processed_at
FROM stripe_webhook_events
ORDER BY id DESC
LIMIT 20;
```

Expected:

- Event IDs are unique.
- Replayed duplicate events do not create duplicated business side-effects.

## 8) API validation checkpoints

Subscription summary endpoint:

```bash
curl -s http://127.0.0.1:8000/api/v1/billing/subscription \
  -H "Authorization: Bearer <USER_TOKEN>"
```

Expected statuses over lifecycle:

- `activa`
- `grace_period`
- `cancelada`
- `sin_plan` / `inactiva` depending on state

## 9) Security checks

1. Invalid webhook signature must be rejected (`403`).
2. Valid signature must pass.
3. `STRIPE_WEBHOOK_SECRET` must not be committed.

## 10) Troubleshooting

1. No webhook processing:
   - Confirm `stripe listen` is forwarding to `/stripe/webhook`.
   - Verify `STRIPE_WEBHOOK_SECRET` matches current listener secret.
   - Run `php artisan config:clear` after env changes.
2. Jobs not running:
   - Ensure queue worker is active and correct queues are consumed.
3. Plan not syncing:
   - Check plan has exact `stripe_price_id` from Stripe.
   - Check recent `customer.subscription.*` payload contains matching `stripe_price`.

## 11) Exit criteria

E2E is successful when all are true:

1. Checkout completes in Stripe Test Mode.
2. Webhook events are accepted with valid signature.
3. `subscriptions` and `users.plan_id/plan_expires_at` reflect Stripe state.
4. `stripe_webhook_events` records processed events and protects idempotency.
5. API endpoint `/api/v1/billing/subscription` returns expected status transitions.

---

## 12) Execution Progress Log

### 2026-05-23 (local)

Completed checkpoints:

1. Stripe CLI authenticated and running locally.
2. Webhook signing secret configured in local environment.
3. Trigger executed: `invoice.payment_failed`.
4. Trigger executed: `customer.subscription.updated`.
5. Trigger executed: `customer.subscription.deleted`.
6. Persistence verified in `stripe_webhook_events` with recent records.

Observed event persistence summary:

- `invoice.payment_failed`: 1
- `customer.subscription.updated`: 1
- `customer.subscription.deleted`: 1

Recent processed examples:

- `evt_1TaIONHNn4kAnu3BJ6sqj90z` -> `invoice.payment_failed`
- `evt_1TaIQ9HNn4kAnu3BVIiYw7Sj` -> `customer.subscription.updated`
- `evt_1TaIQJHNn4kAnu3BoobcnTT6` -> `customer.subscription.deleted`

Completed after final checkout and API validation:

1. Checkout session generated from API and completed in Stripe hosted checkout using test card `4242 4242 4242 4242`.
2. Local subscription persisted for `owner@example.com`:
  - `subscriptions.stripe_id = sub_1TaIWGHNn4kAnu3BmrG2NDfR`
  - `subscriptions.stripe_status = active`
  - `subscriptions.stripe_price = price_1TaIT9HNn4kAnu3BsUDQDWf2`
  - `subscriptions.ends_at = null` (final post-resume state)
3. User linkage verified:
  - `users.plan_id = 1`
  - `users.stripe_id = cus_UZRMZtCHgiTNCO`
4. API lifecycle transitions verified with same owner token:
  - `POST /api/v1/billing/subscription/cancel` -> `status=grace_period`
  - `POST /api/v1/billing/subscription/resume` -> `status=activa`
  - Final `GET /api/v1/billing/subscription` -> `status=activa`
5. Latest processed webhook evidence includes additional `customer.subscription.updated` events for the cancel/resume cycle (processed at `2026-05-23 16:45:20`).

Operational note:

- Immediately after `resume`, one read returned `grace_period` before webhook propagation completed. A subsequent read returned `activa` with DB aligned (`stripe_status=active`, `ends_at=null`). This is expected eventual consistency for async webhook synchronization.

## 13) Preproduction Sign-Off Checklist

Use this checklist before enabling or modifying billing flows in preproduction/staging.

### Environment and configuration

1. [ ] `APP_ENV` is set correctly for preproduction/staging.
2. [ ] Stripe keys are not test placeholders and match the intended environment.
3. [ ] `STRIPE_WEBHOOK_SECRET` matches the active webhook endpoint.
4. [ ] `STRIPE_WEBHOOK_TOLERANCE=300` (or approved value) is configured.
5. [ ] `php artisan config:clear` executed after any `.env` billing change.

### Webhook and queue readiness

1. [ ] Webhook endpoint is reachable from Stripe (`/stripe/webhook`).
2. [ ] Queue worker is running and consuming `payments,notifications,default`.
3. [ ] Invalid signature test returns `403`.
4. [ ] Valid signature test is accepted and recorded.
5. [ ] Duplicate webhook replay does not produce duplicate side-effects.

### Functional billing validation

1. [ ] Checkout URL is generated through `POST /api/v1/billing/checkout/{plan}`.
2. [ ] Hosted Stripe checkout completes successfully.
3. [ ] `GET /api/v1/billing/subscription` returns `activa` after completion.
4. [ ] `POST /api/v1/billing/subscription/cancel` returns `grace_period`.
5. [ ] `POST /api/v1/billing/subscription/resume` returns `activa` (or converges after webhook propagation).

### Data integrity checkpoints

1. [ ] `subscriptions` contains expected `stripe_id`, `stripe_status`, `stripe_price`.
2. [ ] `subscriptions.ends_at` matches lifecycle state (`null` when active after resume).
3. [ ] `users.plan_id` matches plan mapped by `stripe_price_id`.
4. [ ] `users.stripe_id` is present for subscribed users.
5. [ ] `stripe_webhook_events` shows processed lifecycle events with unique `stripe_event_id`.

### Documentation and audit trail

1. [ ] Execution date/time and operator are logged in section `12) Execution Progress Log`.
2. [ ] Relevant event IDs are copied to the progress log.
3. [ ] Any transient consistency delays are documented as operational notes.
4. [ ] README runbook summary is updated if execution changes expected behavior.

### Approval gate

Mark sign-off as complete only when all items above are checked and validated.

- Technical owner approval: [ ]
- QA approval: [ ]
- Product/operations approval: [ ]
- Date: [ ]
