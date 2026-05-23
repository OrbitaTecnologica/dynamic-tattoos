<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

final class StripeWebhookController extends \Laravel\Cashier\Http\Controllers\WebhookController
{
    /**
     * Cashier does not include this handler in this version.
     * Defining it here ensures invoice.payment_failed reaches WebhookHandled.
     *
     * @param array<string, mixed> $payload
     */
    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        return $this->successMethod();
    }
}