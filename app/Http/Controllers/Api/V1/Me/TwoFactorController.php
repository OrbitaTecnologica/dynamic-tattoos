<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

final class TwoFactorController extends Controller
{
    public function __construct(private readonly TotpService $totp)
    {
    }

    public function enable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $secret = $this->totp->generateSecret();
        $recoveryCodes = collect(range(1, 8))
            ->map(static fn (): string => Str::upper(Str::random(5)).'-'.Str::upper(Str::random(5)))
            ->all();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => null,
        ])->save();

        $otpauthUrl = $this->totp->otpauthUrl($secret, (string) $user->email, config('app.name', 'Dynamic Tattoos'));

        return response()->json([
            'data' => [
                'secret' => $secret,
                'otpauth_url' => $otpauthUrl,
                'qr_svg' => (string) QrCodeGenerator::format('svg')->size(220)->margin(1)->generate($otpauthUrl),
                'recovery_codes' => $recoveryCodes,
            ],
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        if ($user->two_factor_secret === null || ! $this->totp->verify((string) $user->two_factor_secret, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => 'El código de verificación no es válido.',
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return response()->json([
            'message' => 'Autenticación en dos pasos activada.',
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña no es correcta.',
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Autenticación en dos pasos desactivada.',
        ]);
    }
}
