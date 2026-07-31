<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login');

    // Challenge 2FA: llega con la contraseña ya validada (sesión pre-login).
    Route::get('two-factor/challenge', [TwoFactorChallengeController::class, 'create'])
        ->name('two-factor.challenge');

    Route::post('two-factor/challenge', [TwoFactorChallengeController::class, 'store'])
        ->name('two-factor.challenge.store')
        ->middleware('throttle:login');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('throttle:login');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Enrolamiento 2FA del panel (fuera del grupo /admin para no crear un bucle
    // de redirección con el middleware admin.2fa).
    Route::get('two-factor/setup', [TwoFactorSetupController::class, 'show'])
        ->name('two-factor.setup');

    Route::post('two-factor/setup', [TwoFactorSetupController::class, 'store'])
        ->name('two-factor.setup.confirm')
        ->middleware('throttle:login');
});
