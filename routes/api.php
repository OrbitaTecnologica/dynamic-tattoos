<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\TatuadorApprovalController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BillingCheckoutController;
use App\Http\Controllers\Api\V1\BillingPortalApiController;
use App\Http\Controllers\Api\V1\BillingSubscriptionController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\LinkPageController;
use App\Http\Controllers\Api\V1\LinkPageLinkController;
use App\Http\Controllers\Api\V1\Me\AccountController;
use App\Http\Controllers\Api\V1\Me\AmbassadorController as MeAmbassadorController;
use App\Http\Controllers\Api\V1\Me\ActivityController;
use App\Http\Controllers\Api\V1\Me\BillingController as MeBillingController;
use App\Http\Controllers\Api\V1\Me\CompanyController;
use App\Http\Controllers\Api\V1\Me\NotificationController;
use App\Http\Controllers\Api\V1\Me\PreferenceController;
use App\Http\Controllers\Api\V1\Me\ProfileController;
use App\Http\Controllers\Api\V1\Me\ReferralController as MeReferralController;
use App\Http\Controllers\Api\V1\Me\SecurityController;
use App\Http\Controllers\Api\V1\Me\SessionController;
use App\Http\Controllers\Api\V1\Me\StatsController;
use App\Http\Controllers\Api\V1\Me\StorageController;
use App\Http\Controllers\Api\V1\Me\TeamController;
use App\Http\Controllers\Api\V1\Me\TwoFactorController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\Public\PublicTattooController;
use App\Http\Controllers\Api\V1\QrCodeController;
use App\Http\Controllers\Api\V1\ReferralVisitController;
use App\Http\Controllers\Api\V1\StoragePackController;
use App\Http\Controllers\Api\V1\TattooContentController;
use App\Http\Controllers\Api\V1\TattooController;
use App\Http\Controllers\Api\V1\TattooScanController;
use App\Http\Controllers\Api\V1\TatuadorController;
use App\Http\Controllers\Api\V1\TatuadorSolicitudController;
use App\Http\Middleware\TrackTeamMemberActivity;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [AuthTokenController::class, 'register'])
        ->middleware('throttle:api-auth')
        ->name('api.v1.auth.register');

    Route::post('/auth/login', [AuthTokenController::class, 'store'])
        ->middleware('throttle:api-auth')
        ->name('api.v1.auth.login');

    Route::post('/auth/email/verify', [AuthTokenController::class, 'verifyEmail'])
        ->middleware('throttle:api-auth')
        ->name('api.v1.auth.email.verify');

    Route::post('/auth/email/resend', [AuthTokenController::class, 'resendEmailCode'])
        ->middleware('throttle:api-auth')
        ->name('api.v1.auth.email.resend');

    // Visita/escaneo del QR de referidos (público).
    Route::post('/referrals/visit', ReferralVisitController::class)
        ->middleware('throttle:api')
        ->name('api.v1.referrals.visit');

    // Contenido público de un tatuaje por short_code (consumido por la galería del SPA).
    Route::get('/public/tattoos/{shortCode}', PublicTattooController::class)
        ->middleware('throttle:api')
        ->where('shortCode', '[a-zA-Z0-9]{1,12}')
        ->name('api.v1.public.tattoos.show');

    // Formulario de contacto público.
    Route::post('/contact', ContactController::class)
        ->middleware('throttle:api')
        ->name('api.v1.contact');

    // Red de tatuadores certificados (público): mapa + solicitud de homologación.
    Route::get('/tatuadores', [TatuadorController::class, 'index'])
        ->middleware('throttle:api')
        ->name('api.v1.tatuadores.index');
    Route::post('/tatuadores/solicitud', [TatuadorSolicitudController::class, 'store'])
        ->middleware('throttle:api')
        ->name('api.v1.tatuadores.solicitud');

    Route::middleware(['auth:sanctum', 'throttle:api', TrackTeamMemberActivity::class])->group(function (): void {
        Route::get('/auth/me', [AuthTokenController::class, 'me'])
            ->name('api.v1.auth.me');

        Route::post('/auth/logout', [AuthTokenController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.auth.logout');

        Route::get('/plans', [PlanController::class, 'index'])
            ->name('api.v1.plans.index');

        Route::get('/billing/subscription', [BillingSubscriptionController::class, 'show'])
            ->name('api.v1.billing.subscription.show');
        Route::post('/billing/subscription/cancel', [BillingSubscriptionController::class, 'cancel'])
            ->middleware('throttle:api-write')
            ->name('api.v1.billing.subscription.cancel');
        Route::post('/billing/subscription/resume', [BillingSubscriptionController::class, 'resume'])
            ->middleware('throttle:api-write')
            ->name('api.v1.billing.subscription.resume');
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

        // Cuenta: perfil, empresa, preferencias, notificaciones
        Route::get('/me/profile', [ProfileController::class, 'show'])
            ->name('api.v1.me.profile.show');
        Route::patch('/me/profile', [ProfileController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.profile.update');
        Route::post('/me/avatar', [ProfileController::class, 'uploadAvatar'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.avatar');
        Route::get('/me/company', [CompanyController::class, 'show'])
            ->name('api.v1.me.company.show');
        Route::patch('/me/company', [CompanyController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.company.update');
        Route::patch('/me/preferences', [PreferenceController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.preferences');
        Route::get('/me/notifications', [NotificationController::class, 'show'])
            ->name('api.v1.me.notifications.show');
        Route::patch('/me/notifications', [NotificationController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.notifications.update');

        // Cuenta: facturación, sesiones, seguridad
        Route::get('/me/billing', [MeBillingController::class, 'overview'])
            ->name('api.v1.me.billing');
        Route::get('/me/invoices', [MeBillingController::class, 'invoices'])
            ->name('api.v1.me.invoices');
        Route::get('/me/sessions', [SessionController::class, 'index'])
            ->name('api.v1.me.sessions.index');
        Route::delete('/me/sessions/{id}', [SessionController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.sessions.destroy');
        Route::delete('/me/sessions', [SessionController::class, 'destroyOthers'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.sessions.destroy-others');
        Route::patch('/me/password', [SecurityController::class, 'updatePassword'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.password');
        Route::delete('/me', [AccountController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.destroy');

        // Cuenta: stats, actividad
        Route::get('/me/stats', [StatsController::class, 'index'])
            ->name('api.v1.me.stats');
        Route::get('/me/activity', [ActivityController::class, 'index'])
            ->name('api.v1.me.activity');

        // Cuenta: referidos (solo plan Partner)
        Route::get('/me/referrals', [MeReferralController::class, 'index'])
            ->name('api.v1.me.referrals');
        Route::post('/me/referrals/withdraw', [MeReferralController::class, 'withdraw'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.referrals.withdraw');

        // Cuenta: panel embajador (rol ambassador)
        Route::get('/me/ambassador/summary', [MeAmbassadorController::class, 'summary'])
            ->name('api.v1.me.ambassador.summary');
        Route::patch('/me/ambassador/slug', [MeAmbassadorController::class, 'updateSlug'])
            ->middleware('throttle:3,43200') // 3 cambios cada 30 días (43200 min)
            ->name('api.v1.me.ambassador.slug');

        // Cuenta: 2FA
        Route::post('/me/2fa/enable', [TwoFactorController::class, 'enable'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.2fa.enable');
        Route::post('/me/2fa/confirm', [TwoFactorController::class, 'confirm'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.2fa.confirm');
        Route::delete('/me/2fa', [TwoFactorController::class, 'disable'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.2fa.disable');

        // Cuenta: equipo
        Route::get('/me/team', [TeamController::class, 'index'])
            ->name('api.v1.me.team.index');
        Route::post('/me/team', [TeamController::class, 'store'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.team.store');
        Route::patch('/me/team/{member}', [TeamController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.team.update');
        Route::delete('/me/team/{member}', [TeamController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.team.destroy');
        Route::post('/team/invitations/{token}/accept', [TeamController::class, 'accept'])
            ->middleware('throttle:api-write')
            ->name('api.v1.team.invitations.accept');

        // Cuenta: almacenamiento
        Route::get('/me/storage', [StorageController::class, 'show'])
            ->name('api.v1.me.storage.show');
        Route::post('/me/storage/packs/{pack}/checkout', [StorageController::class, 'checkout'])
            ->middleware('throttle:api-write')
            ->name('api.v1.me.storage.checkout');
        Route::get('/storage-packs', [StoragePackController::class, 'index'])
            ->name('api.v1.storage-packs.index');

        // QR Studio
        Route::get('/qr-codes', [QrCodeController::class, 'index'])
            ->name('api.v1.qr-codes.index');
        Route::get('/qr-codes/slug-available', [QrCodeController::class, 'slugAvailable'])
            ->name('api.v1.qr-codes.slug-available');
        Route::post('/qr-codes', [QrCodeController::class, 'store'])
            ->middleware('throttle:api-write')
            ->name('api.v1.qr-codes.store');
        Route::get('/qr-codes/{qrCode}', [QrCodeController::class, 'show'])
            ->name('api.v1.qr-codes.show');
        Route::delete('/qr-codes/{qrCode}', [QrCodeController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.qr-codes.destroy');
        Route::post('/qr-codes/{qrCode}/email', [QrCodeController::class, 'email'])
            ->middleware('throttle:api-write')
            ->name('api.v1.qr-codes.email');

        // Tarjeta de links (Link page)
        Route::get('/link-page', [LinkPageController::class, 'show'])
            ->name('api.v1.link-page.show');
        Route::patch('/link-page', [LinkPageController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.update');
        Route::post('/link-page/avatar', [LinkPageController::class, 'uploadAvatar'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.avatar');
        Route::delete('/link-page/avatar', [LinkPageController::class, 'deleteAvatar'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.avatar.destroy');
        Route::post('/link-page/cover', [LinkPageController::class, 'uploadCover'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.cover');
        Route::delete('/link-page/cover', [LinkPageController::class, 'deleteCover'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.cover.destroy');
        Route::get('/link-page/links', [LinkPageLinkController::class, 'index'])
            ->name('api.v1.link-page.links.index');
        Route::post('/link-page/links', [LinkPageLinkController::class, 'store'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.links.store');
        Route::post('/link-page/links/reorder', [LinkPageLinkController::class, 'reorder'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.links.reorder');
        Route::patch('/link-page/links/{link}', [LinkPageLinkController::class, 'update'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.links.update');
        Route::delete('/link-page/links/{link}', [LinkPageLinkController::class, 'destroy'])
            ->middleware('throttle:api-write')
            ->name('api.v1.link-page.links.destroy');

        // Admin: aprobación de solicitudes de tatuadores
        Route::post('/admin/tatuadores/solicitudes/{solicitud}/aprobar', [TatuadorApprovalController::class, 'approve'])
            ->middleware(['admin', 'throttle:api-write'])
            ->name('api.v1.admin.tatuadores.solicitudes.aprobar');

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
