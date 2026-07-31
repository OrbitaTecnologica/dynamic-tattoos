<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\HandleCashierWebhook;
use App\Services\Billing\BillingGateway;
use App\Services\Billing\CashierBillingGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookHandled;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BillingGateway::class, CashierBillingGateway::class);
    }

    public function boot(): void
    {
        $this->forceApplicationUrl();
        $this->configureRateLimiting();

        Event::listen(WebhookHandled::class, HandleCashierWebhook::class);
    }

    private function forceApplicationUrl(): void
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl === '') {
            return;
        }

        URL::forceRootUrl($appUrl);

        $scheme = parse_url($appUrl, PHP_URL_SCHEME);

        if (is_string($scheme) && $scheme !== '') {
            URL::forceScheme($scheme);
        }
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-auth', static function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('api-write', static function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Login web del panel: además del limitador por email+IP del
        // LoginRequest, corta por IP los barridos de fuerza bruta.
        RateLimiter::for('login', static function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
