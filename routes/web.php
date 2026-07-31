<?php

declare(strict_types=1);

use App\Http\Controllers\HomeRedirectController;
use App\Http\Controllers\LinkPageController;
use App\Http\Controllers\PublicAmbassadorController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TattooRedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Raíz → panel administrativo (el frontend del cliente vive en el SPA)
| Usa route() (no una ruta absoluta) para respetar el prefijo /backend.
|--------------------------------------------------------------------------
*/
Route::get('/', HomeRedirectController::class)->name('home');

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
| Público: landing del embajador (registra la visita y enlaza al registro)
|--------------------------------------------------------------------------
*/
Route::get('/e/{slug}', [PublicAmbassadorController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('ambassador.public');

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
Route::middleware(['auth', 'admin', 'admin.2fa'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/', 'admin.dashboard')->name('dashboard');

    // Contenido
    Route::view('/tattoos', 'admin.tattoos')->name('tattoos');
    Route::view('/qr-generator', 'admin.qr-generator')->name('qr-generator');
    Route::view('/scans', 'admin.scans')->name('scans');
    Route::view('/link-pages', 'admin.link-pages')->name('link-pages');
    Route::view('/tatuadores', 'admin.tatuadores')->name('tatuadores');

    // Clientes
    Route::view('/users', 'admin.users')->name('users');
    Route::view('/companies', 'admin.companies')->name('companies');
    Route::view('/team-members', 'admin.team-members')->name('team-members');
    Route::view('/storage-usage', 'admin.storage-usage')->name('storage-usage');

    // Monetización
    Route::view('/pricing', 'admin.pricing')->name('pricing');
    Route::view('/storage-packs', 'admin.storage-packs')->name('storage-packs');
    Route::view('/subscriptions', 'admin.subscriptions')->name('subscriptions');
    Route::view('/referrals', 'admin.referrals')->name('referrals');
    Route::view('/withdrawals', 'admin.withdrawals')->name('withdrawals');
    Route::view('/billing-alerts', 'admin.billing-alerts')->name('billing-alerts');

    // Sistema
    Route::view('/activity-log', 'admin.activity-log')->name('activity-log');
    Route::view('/api-tokens', 'admin.api-tokens')->name('api-tokens');
    Route::view('/account', 'admin.account')->name('account');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| QrCode redirect — d-t.me/{slug} o d-t.me/{id}
| Primero intenta por slug (texto), luego por ID numérico.
| DEBE ir después de auth.php para no capturar /login, /forgot-password, etc.
|--------------------------------------------------------------------------
*/
Route::get('/{slug}', static function (string $slug) {
    $frontendUrl = rtrim((string) env('FRONTEND_URL', 'https://www.dynamic-tattoos.com'), '/');

    $qr = \App\Models\QrCode::where('slug', $slug)->first()
        ?? (ctype_digit($slug) ? \App\Models\QrCode::find((int) $slug) : null);

    if ($qr) {
        $dest = (string) ($qr->url ?? '');
        // Avoid self-referential redirect loops (url points back to d-t.me/{slug})
        $isSelf = $dest === '' || str_ends_with(rtrim($dest, '/'), '/' . $slug);
        if (!$isSelf) {
            return redirect()->away($dest, 302);
        }
        // QR exists but no real destination yet — show the frontend gallery
        return redirect()->away("{$frontendUrl}/t/{$slug}", 302);
    }

    // Fallback: legacy tattoos stored in the tattoos table (short_code system)
    $tattoo = \App\Models\Tattoo::where('short_code', $slug)->first();
    if ($tattoo) {
        return redirect()->route('tattoo.show', ['shortCode' => $slug], 302);
    }

    abort(404);
})->where('slug', '[A-Za-z0-9_\-]+')->name('qr.redirect');
