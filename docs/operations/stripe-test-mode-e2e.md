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
