<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse as IlluminateRedirectResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final class CashierBillingGateway implements BillingGateway
{
    public function createCheckoutUrl(User $user, Plan $plan, string $successUrl, string $cancelUrl): string
    {
        $checkoutResponse = $user
            ->newSubscription('default', (string) $plan->stripe_price_id)
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

        return $this->extractUrl($checkoutResponse);
    }

    public function createPortalUrl(User $user, string $returnUrl): string
    {
        return $user->billingPortalUrl($returnUrl);
    }

    private function extractUrl(mixed $response): string
    {
        if ($response instanceof IlluminateRedirectResponse || $response instanceof SymfonyRedirectResponse) {
            return $response->getTargetUrl();
        }

        if (is_object($response) && method_exists($response, 'url')) {
            /** @var mixed $url */
            $url = $response->url();

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        if (is_object($response) && isset($response->url) && is_string($response->url) && $response->url !== '') {
            return $response->url;
        }

        throw new RuntimeException('Unable to extract checkout URL from billing gateway response.');
    }
}
