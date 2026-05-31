<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse as IlluminateRedirectResponse;
use Laravel\Cashier\Checkout;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final class CashierBillingGateway implements BillingGateway
{
    public function __construct(private readonly StripeTaxProfile $taxProfile) {}

    public function createCheckoutUrl(User $user, Plan $plan, string $successUrl, string $cancelUrl): string
    {
        $options = [
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        if (config('billing.tax_enabled')) {
            // Empuja NIF/IVA del cliente a Stripe y activa IVA + recogida fiscal.
            $this->taxProfile->sync($user);

            $options['automatic_tax'] = ['enabled' => true];
            $options['tax_id_collection'] = ['enabled' => true];
            $options['billing_address_collection'] = 'required';
            $options['customer_update'] = ['address' => 'auto', 'name' => 'auto'];
        }

        $checkoutResponse = $user
            ->newSubscription('default', (string) $plan->stripe_price_id)
            ->checkout($options);

        return $this->extractUrl($checkoutResponse);
    }

    public function createPortalUrl(User $user, string $returnUrl): string
    {
        return $user->billingPortalUrl($returnUrl);
    }

    public function cancelSubscription(User $user, string $subscriptionName = 'default'): void
    {
        $user->subscription($subscriptionName)?->cancel();
    }

    public function resumeSubscription(User $user, string $subscriptionName = 'default'): void
    {
        $user->subscription($subscriptionName)?->resume();
    }

    private function extractUrl(mixed $response): string
    {
        if ($response instanceof IlluminateRedirectResponse || $response instanceof SymfonyRedirectResponse) {
            return $response->getTargetUrl();
        }

        if ($response instanceof Checkout) {
            $session = $response->asStripeCheckoutSession();

            if (isset($session->url) && is_string($session->url) && $session->url !== '') {
                return $session->url;
            }

            return $response->redirect()->getTargetUrl();
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
