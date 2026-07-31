<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

/**
 * Enrolamiento 2FA del panel web. Los admins sin 2FA llegan aquí redirigidos
 * por el middleware `admin.2fa`; hasta confirmar el código no pueden entrar.
 */
final class TwoFactorSetupController extends Controller
{
    public function __construct(private readonly TotpService $totp) {}

    public function show(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        // Reutiliza el secret pendiente si ya se generó (para no invalidar un
        // QR ya escaneado al recargar la página).
        if ($user->two_factor_secret === null) {
            $user->forceFill([
                'two_factor_secret' => $this->totp->generateSecret(),
                'two_factor_recovery_codes' => collect(range(1, 8))
                    ->map(static fn (): string => Str::upper(Str::random(5)).'-'.Str::upper(Str::random(5)))
                    ->all(),
                'two_factor_confirmed_at' => null,
            ])->save();
        }

        $otpauthUrl = $this->totp->otpauthUrl(
            (string) $user->two_factor_secret,
            (string) $user->email,
            (string) config('app.name', 'Dynamic Tattoos'),
        );

        return view('auth.two-factor-setup', [
            'qrSvg' => (string) QrCodeGenerator::format('svg')->size(200)->margin(1)->generate($otpauthUrl),
            'secret' => (string) $user->two_factor_secret,
            'recoveryCodes' => (array) $user->two_factor_recovery_codes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate(['code' => ['required', 'string']]);

        if ($user->two_factor_secret === null
            || ! $this->totp->verify((string) $user->two_factor_secret, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => 'El código de verificación no es válido.',
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        activity('security')
            ->causedBy($user)
            ->event('2fa_enabled')
            ->withProperties(['detail' => 'Autenticación en dos pasos activada (panel)'])
            ->log('Seguridad actualizada');

        return redirect()->route('admin.dashboard');
    }
}
