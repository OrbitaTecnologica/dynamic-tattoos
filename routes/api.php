<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BillingCheckoutController;
use App\Http\Controllers\Api\V1\BillingPortalApiController;
use App\Http\Controllers\Api\V1\BillingSubscriptionController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\TattooContentController;
use App\Http\Controllers\Api\V1\TattooController;
use App\Http\Controllers\Api\V1\TattooScanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthTokenController::class, 'store'])
        ->middleware('throttle:api-auth')
        ->name('api.v1.auth.login');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::get('/auth/me', [AuthTokenController::class, 'me'])
            ->name('api.v1.auth.me');

        Route::post('/auth/logout', [AuthTokenController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.auth.logout');

        Route::get('/plans', [PlanController::class, 'index'])
            ->name('api.v1.plans.index');

        Route::get('/billing/subscription', [BillingSubscriptionController::class, 'show'])
            ->name('api.v1.billing.subscription.show');
        Route::post('/billing/checkout/{plan}', BillingCheckoutController::class)
            ->middleware('throttle:api-write')
            ->name('api.v1.billing.checkout');
        Route::post('/billing/portal', BillingPortalApiController::class)
            ->middleware('throttle:api-write')
            ->name('api.v1.billing.portal');

        Route::get('/tattoos', [TattooController::class, 'index'])
            ->name('api.v1.tattoos.index');
        Route::post('/tattoos', [TattooController::class, 'store'])
            ->middleware('throttle:api-write')
            ->name('api.v1.tattoos.store');
        Route::get('/tattoos/{tattoo}', [TattooController::class, 'show'])
            ->name('api.v1.tattoos.show');
        Route::patch('/tattoos/{tattoo}', [TattooController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.tattoos.update');
        Route::delete('/tattoos/{tattoo}', [TattooController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.tattoos.destroy');

        Route::get('/tattoos/{tattoo}/contents', [TattooContentController::class, 'index'])
            ->name('api.v1.tattoos.contents.index');
        Route::post('/tattoos/{tattoo}/contents/activate', [TattooContentController::class, 'activate'])
            ->middleware('throttle:api-write')
            ->name('api.v1.tattoos.contents.activate');

        Route::get('/tattoos/{tattoo}/scans', [TattooScanController::class, 'index'])
            ->name('api.v1.tattoos.scans.index');

        Route::get('/admin/plans', [PlanController::class, 'adminIndex'])
            ->name('api.v1.admin.plans.index');
        Route::post('/admin/plans', [PlanController::class, 'store'])
            ->middleware('throttle:api-write')
            ->name('api.v1.admin.plans.store');
        Route::patch('/admin/plans/{plan}', [PlanController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.admin.plans.update');
        Route::delete('/admin/plans/{plan}', [PlanController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.admin.plans.destroy');
    });
});
