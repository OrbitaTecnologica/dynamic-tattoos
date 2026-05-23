# Billing Incident Response Runbook

This runbook covers operational response for billing and Stripe webhook incidents.

## 1) Scope

Use this playbook when one or more of the following occurs:

- Checkout succeeds in Stripe but local plan is not updated.
- `/api/v1/billing/subscription` returns unexpected status for subscribed users.
- `failed_jobs` grows for `payments` or `notifications` queues.
- Stripe webhook retries increase or duplicate webhook ratio spikes.

## 2) Severity levels

- SEV-1: Active revenue impact (new subscriptions failing broadly, >20 minutes unresolved).
- SEV-2: Partial degradation (some users impacted, manual recovery possible).
- SEV-3: Non-blocking issues (delays, retries, no direct revenue loss).

## 3) First 10 minutes checklist

1. Confirm incident channel and assign incident owner.
2. Check queue backlog and failed jobs:

```bash
php artisan queue:failed
```

3. Check recent Stripe webhook processing records:

```sql
SELECT id, stripe_event_id, type, processed_at
FROM stripe_webhook_events
ORDER BY id DESC
LIMIT 50;
```

4. Verify worker health (process running, expected queues):

```bash
php artisan queue:work --queue=payments,notifications,default
```

5. Validate webhook endpoint reachability from Stripe dashboard and current signing secret.

## 4) Diagnosis matrix

### Symptom A: Stripe charge/subscription exists but local plan not updated

Likely causes:

- `SyncUserPlanJob` retries exhausted (`failed_jobs`).
- Missing or stale `STRIPE_WEBHOOK_SECRET`.
- Queue worker not consuming `payments` queue.

Checks:

```bash
php artisan queue:failed
```

```sql
SELECT id, email, plan_id, plan_expires_at, stripe_id
FROM users
WHERE email = 'owner@example.com';
```

```sql
SELECT id, user_id, stripe_id, stripe_status, stripe_price, ends_at, updated_at
FROM subscriptions
ORDER BY id DESC
LIMIT 20;
```

### Symptom B: Frequent webhook duplicates

Likely causes:

- Upstream retries due to delayed acknowledgements.
- Intermittent endpoint/network issues.

Checks:

```sql
SELECT type, COUNT(*) AS total
FROM stripe_webhook_events
WHERE processed_at >= NOW() - INTERVAL 1 HOUR
GROUP BY type
ORDER BY total DESC;
```

Also inspect app logs for:

- `stripe.webhook.duplicate_ignored`
- `stripe.webhook.user_not_found`
- `stripe.webhook.payload_encode_failed`

### Symptom C: Notification emails not sent

Likely causes:

- `SendSubscriptionNotificationJob` failed repeatedly.
- Notification channel/mail transport issue.

Checks:

```bash
php artisan queue:failed
```

Inspect logs for:

- `billing.notification.failed`

## 5) Containment and recovery

1. Stabilize workers:

```bash
php artisan queue:restart
php artisan queue:work --queue=payments,notifications,default
```

2. Retry failed jobs:

```bash
php artisan queue:retry all
```

Or retry targeted UUID:

```bash
php artisan queue:retry <failed-job-uuid>
```

3. Re-verify affected user state via API:

```bash
curl -s http://127.0.0.1:8000/api/v1/billing/subscription \
  -H "Authorization: Bearer <USER_TOKEN>"
```

4. If webhook secret drift is detected, update `.env`, then:

```bash
php artisan config:clear
```

5. If needed, request safe Stripe webhook replay from dashboard/CLI for the impacted event IDs.

## 6) Verification after recovery

1. `failed_jobs` stops growing.
2. New webhook events appear in `stripe_webhook_events` with current timestamps.
3. Affected users show expected plan/subscription state in DB and API.
4. No sustained spikes for `billing.plan_sync.failed` or `billing.notification.failed` logs.

## 7) Escalation rules

- Escalate to platform owner if SEV-1 lasts >20 minutes.
- Escalate to product/ops if manual user remediation is required.
- Open Stripe support ticket if webhook delivery anomalies persist after local remediation.

## 8) Post-incident checklist

1. Record timeline (detection, mitigation, recovery, closure).
2. Capture impacted event IDs and user IDs.
3. Add permanent fix tasks (code, monitoring, docs).
4. Update [stripe-test-mode-e2e.md](stripe-test-mode-e2e.md) if operational behavior changed.
5. Share postmortem summary with technical owner and QA.
