<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\HandleCashierWebhook;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookHandled;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(WebhookHandled::class, HandleCashierWebhook::class);
    }
}
