<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\User;

interface BillingGateway
{
    public function createCheckoutUrl(User $user, Plan $plan, string $successUrl, string $cancelUrl): string;

    public function createPortalUrl(User $user, string $returnUrl): string;

    public function cancelSubscription(User $user, string $subscriptionName = 'default'): void;

    public function resumeSubscription(User $user, string $subscriptionName = 'default'): void;
}
