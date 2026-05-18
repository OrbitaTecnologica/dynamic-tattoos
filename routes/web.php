<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BillingPortalController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\SubscriptionCheckoutController;
use App\Http\Controllers\TattooRedirectController;
use App\Models\Tattoo;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Landing
|--------------------------------------------------------------------------
*/
Route::view('/', 'welcome')->name('home');

/*
|--------------------------------------------------------------------------
| Public QR Redirect Route
|--------------------------------------------------------------------------
*/
Route::get('/t/{shortCode}', TattooRedirectController::class)
    ->name('tattoo.show')
    ->where('shortCode', '[a-zA-Z0-9]{1,12}');

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Breeze profile + tattoo management)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::get('/dashboard', function () {
        return view('dashboard', [
            'tattoos' => auth()->user()->tattoos()->with('activeContent')->get(),
        ]);
    })->name('dashboard');

    Route::view('/billing', 'dashboard.billing')->name('billing');
    Route::post('/billing/checkout/{plan}', SubscriptionCheckoutController::class)->name('billing.checkout');
    Route::post('/billing/portal', BillingPortalController::class)->name('billing.portal');

    Route::get('/dashboard/tattoos/{tattoo}/manage', function (Tattoo $tattoo) {
        return view('dashboard.manage-tattoo', compact('tattoo'));
    })->name('tattoos.manage');

    // QR Studio
    Route::get('/dashboard/qr-studio', [QrCodeController::class, 'create'])->name('qr.create');
    Route::post('/dashboard/qr-studio', [QrCodeController::class, 'store'])->name('qr.store');
    Route::get('/dashboard/qr-studio/history', [QrCodeController::class, 'history'])->name('qr.history');
    Route::post('/dashboard/qr-studio/email', [QrCodeController::class, 'sendEmail'])->name('qr.email');

    // Client profile panel
    Route::view('/perfil', 'profile.index')->name('profile.index');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::view('/tattoos', 'admin.tattoos')->name('tattoos');
    Route::view('/scans', 'admin.scans')->name('scans');
    Route::view('/qr-generator', 'admin.qr-generator')->name('qr-generator');
    Route::view('/pricing', 'admin.pricing')->name('pricing');
    Route::view('/account', 'admin.account')->name('account');
    Route::view('/users', 'admin.users')->name('users');
});

require __DIR__.'/auth.php';
