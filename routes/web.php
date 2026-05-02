<?php

declare(strict_types=1);

use App\Http\Controllers\TattooRedirectController;
use App\Models\Tattoo;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

/*
|--------------------------------------------------------------------------
| Public QR Redirect Route
|--------------------------------------------------------------------------
| Every QR code encodes a URL like https://app.com/t/{shortCode}.
| The regex constraint prevents path-traversal and injection attempts before
| the request reaches the controller.
*/
Route::get('/t/{shortCode}', TattooRedirectController::class)
    ->name('tattoo.show')
    ->where('shortCode', '[a-zA-Z0-9]{1,12}');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function (): void {

    /*
     * Tattoo management page.
     * Route-model binding resolves the Tattoo by its primary key.
     * The ManageTattoo Livewire component calls $this->authorize('update', $tattoo)
     * internally, so ownership is enforced at the component level regardless of
     * any direct URL access.
     */
    Route::get('/dashboard/tattoos/{tattoo}/manage', function (Tattoo $tattoo) {
        return view('dashboard.manage-tattoo', compact('tattoo'));
    })->name('tattoos.manage');
});
