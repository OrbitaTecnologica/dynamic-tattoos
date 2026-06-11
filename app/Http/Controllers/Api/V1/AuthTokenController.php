<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResendCodeRequest;
use App\Http\Requests\Api\V1\VerifyEmailRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Services\Referrals\ReferralService;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthTokenController extends Controller
{
    public function register(RegisterRequest $request, ReferralService $referrals, EmailVerificationService $verification): JsonResponse
    {
        $email = mb_strtolower((string) $request->input('email'));

        // Email existente NO verificado (el unique solo aplica a verificados): reenvía código.
        $existing = User::query()->where('email', $email)->first();
        if ($existing !== null) {
            $verification->issue($existing);

            return response()->json([
                'data' => ['requires_verification' => true, 'email' => $existing->email],
            ], 202);
        }

        $user = User::query()->create([
            'name' => trim((string) $request->input('name')),
            'email' => $email,
            'password' => (string) $request->input('password'),
            'role' => 'user',
        ]);

        $referrals->attach($user, $request->input('referral_code'));

        activity('account')
            ->causedBy($user)
            ->event('account_created')
            ->withProperties(['detail' => 'Bienvenido a Dynamic Tattoos'])
            ->log('Cuenta creada');

        $verification->issue($user);

        return response()->json([
            'data' => ['requires_verification' => true, 'email' => $user->email],
        ], 202);
    }

    /**
     * @throws ValidationException
     */
    public function verifyEmail(VerifyEmailRequest $request, EmailVerificationService $verification): JsonResponse
    {
        $email = mb_strtolower((string) $request->input('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user === null || $user->hasVerifiedEmail() || ! $verification->verify($user, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => 'El código no es válido o ha expirado.',
            ]);
        }

        $tokenName = (string) $request->input('device_name', 'api-token');
        $newToken = $user->createToken($tokenName);
        $newToken->accessToken->forceFill([
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ])->save();

        activity('security')
            ->causedBy($user)
            ->event('email_verified')
            ->log('Email verificado');

        return response()->json([
            'data' => [
                'token' => $newToken->plainTextToken,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    public function resendEmailCode(ResendCodeRequest $request, EmailVerificationService $verification): JsonResponse
    {
        $email = mb_strtolower((string) $request->input('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user !== null && ! $user->hasVerifiedEmail() && ! $verification->issue($user)) {
            return response()->json([
                'error' => [
                    'code' => 'resend_cooldown',
                    'message' => 'Espera unos segundos antes de pedir otro código.',
                ],
            ], 429);
        }

        return response()->json([
            'data' => ['requires_verification' => true, 'email' => $email],
        ], 202);
    }

    /**
     * @throws ValidationException
     */
    public function store(LoginRequest $request, TotpService $totp): JsonResponse
    {
        $email = mb_strtolower((string) $request->input('email'));

        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user === null || ! Hash::check((string) $request->input('password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            $this->assertTwoFactor($user, $totp, (string) $request->input('two_factor_code', ''));
        }

        $tokenName = (string) $request->input('device_name', 'api-token');
        $newToken = $user->createToken($tokenName);
        $newToken->accessToken->forceFill([
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ])->save();
        $plainTextToken = $newToken->plainTextToken;

        activity('security')
            ->causedBy($user)
            ->event('login')
            ->withProperties(['detail' => trim($tokenName.' · '.(string) $request->ip())])
            ->log('Inicio de sesión');

        return response()->json([
            'data' => [
                'token' => $plainTextToken,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    /**
     * @throws ValidationException
     */
    private function assertTwoFactor(User $user, TotpService $totp, string $code): void
    {
        if ($code === '') {
            throw ValidationException::withMessages([
                'two_factor_code' => 'Se requiere el código de autenticación en dos pasos.',
            ]);
        }

        if ($totp->verify((string) $user->two_factor_secret, $code)) {
            return;
        }

        if ($this->consumeRecoveryCode($user, $code)) {
            return;
        }

        throw ValidationException::withMessages([
            'two_factor_code' => 'El código de dos pasos no es válido.',
        ]);
    }

    private function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => array_values(array_filter($codes, static fn ($c): bool => $c !== $code)),
        ])->save();

        return true;
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Token revoked successfully.',
        ]);
    }
}
