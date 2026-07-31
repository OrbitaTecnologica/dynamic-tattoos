<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminLoginNotifier;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Challenge 2FA del login web: la contraseña ya se validó, pero la sesión no se
 * abre hasta verificar el código TOTP (o un código de recuperación).
 */
final class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private readonly TotpService $totp,
        private readonly AdminLoginNotifier $notifier,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->missing('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login.id');
        if ($userId === null) {
            return redirect()->route('login');
        }

        $validated = $request->validate(['code' => ['required', 'string']]);
        $user = User::findOrFail($userId);
        $code = trim($validated['code']);

        if (! $this->verifyCode($user, $code)) {
            throw ValidationException::withMessages([
                'code' => 'El código de verificación no es válido.',
            ]);
        }

        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $this->notifier->record($user, $request);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function verifyCode(User $user, string $code): bool
    {
        if ($user->two_factor_secret !== null
            && $this->totp->verify((string) $user->two_factor_secret, $code)) {
            return true;
        }

        // Código de recuperación: se consume al usarlo.
        $codes = (array) ($user->two_factor_recovery_codes ?? []);
        $index = array_search(mb_strtoupper($code), array_map('mb_strtoupper', $codes), true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        return true;
    }
}
