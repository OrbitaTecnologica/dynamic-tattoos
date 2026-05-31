<?php

declare(strict_types=1);

use App\Http\Controllers\LinkPageController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TattooRedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Raíz → panel administrativo (el frontend del cliente vive en el SPA)
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/admin')->name('home');

Route::view('/docs/api', 'docs.api')->name('docs.api');
Route::get('/docs/api/openapi.yaml', static function () {
    $openApiPath = base_path('docs/api/openapi.yaml');

    abort_unless(file_exists($openApiPath), 404);

    return response()->file($openApiPath, [
        'Content-Type' => 'application/yaml; charset=UTF-8',
    ]);
})->name('docs.api.spec');

/*
|--------------------------------------------------------------------------
| Público: redirección/landing del QR escaneado (registra el scan)
|--------------------------------------------------------------------------
*/
Route::get('/t/{shortCode}', TattooRedirectController::class)
    ->name('tattoo.show')
    ->where('shortCode', '[a-zA-Z0-9]{1,12}');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

/*
|--------------------------------------------------------------------------
| Público: tarjeta de links (Linktree) + tracking de clics
|--------------------------------------------------------------------------
*/
Route::get('/u/{slug}', [LinkPageController::class, 'show'])->name('link-page.show');
Route::get('/u/{slug}/c/{link}', [LinkPageController::class, 'redirect'])
    ->whereNumber('link')
    ->name('link-page.redirect');

/*
|--------------------------------------------------------------------------
| Panel administrativo (sesión + rol admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::view('/billing-alerts', 'admin.billing-alerts')->name('billing-alerts');
    Route::view('/tattoos', 'admin.tattoos')->name('tattoos');
    Route::view('/scans', 'admin.scans')->name('scans');
    Route::view('/qr-generator', 'admin.qr-generator')->name('qr-generator');
    Route::view('/pricing', 'admin.pricing')->name('pricing');
    Route::view('/account', 'admin.account')->name('account');
    Route::view('/users', 'admin.users')->name('users');
});

require __DIR__.'/auth.php';
