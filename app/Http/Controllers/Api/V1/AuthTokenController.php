<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResendCodeRequest;
use App\Http\Requests\Api\V1\VerifyEmailRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Mail\AmbassadorWelcomeMail;
use App\Models\Plan;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Services\Referrals\AmbassadorTierService;
use App\Services\Referrals\ReferralService;
use App\Services\TotpService;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class AuthTokenController extends Controller
{
    public function register(RegisterRequest $request, ReferralService $referrals, EmailVerificationService $verification, AmbassadorTierService $tiers): JsonResponse
    {
        $email = mb_strtolower((string) $request->input('email'));

        // Email existente NO verificado (el unique solo aplica a verificados): actualiza
        // nombre/contraseña con los datos del nuevo intento y reenvía el código. Es seguro
        // porque no se emite token hasta verificar y el OTP solo llega al dueño del buzón.
        $existing = User::query()->where('email', $email)->first();
        if ($existing !== null) {
            $existing->update([
                'name' => trim((string) $request->input('name')),
                'password' => (string) $request->input('password'),
            ]);

            // El reintento también puede traer código de referido: sin esto, la
            // atribución se perdía en el caso más común (no le llegó el OTP).
            $referrals->attach($existing, $request->input('referral_code'));

            $verification->issue($existing);

            return response()->json([
                'data' => ['requires_verification' => true, 'email' => $existing->email],
            ], 202);
        }

        // Plan elegido en el landing (opcional). Su allowed_roles determina el rol
        // cuando no llega uno explícito: elegir el plan Embajador/Empresa ES darse
        // de alta como embajador.
        $planSlug = $request->input('plan');
        $plan = is_string($planSlug) && $planSlug !== ''
            ? Plan::query()->active()->where('slug', $planSlug)->first()
            : null;

        $role = (string) $request->input('role', '');
        if (! in_array($role, ['user', 'ambassador'], true)) {
            $role = in_array('ambassador', $plan->allowed_roles ?? [], true) ? 'ambassador' : 'user';
        }

        $user = User::query()->create([
            'name' => trim((string) $request->input('name')),
            'email' => $email,
            'password' => (string) $request->input('password'),
            'role' => $role,
        ]);

        $referrals->attach($user, $request->input('referral_code'));

        // Todo usuario nace con su código de recomendador (antes solo se generaba
        // si abría la pestaña "Recomienda"; por eso casi nadie lo tenía).
        $referrals->ensureCode($user);

        // Setup específico de embajador: tier por defecto, slug público compartible
        // y plan gratis Embajador si no llegó otro.
        if ($user->role === 'ambassador') {
            $defaultTier = $tiers->defaultTier();

            $user->forceFill([
                'ambassador_tier_id' => $defaultTier->id,
                'ambassador_slug' => $this->generateUniqueAmbassadorSlug((string) $user->name),
            ])->save();
        }

        // Solo asignamos aquí los planes GRATIS (p.ej. Embajador) y solo si el plan
        // admite el rol del usuario; los de pago los fija el webhook de Stripe tras
        // el checkout, no el registro.
        if ($plan !== null
            && (float) $plan->price === 0.0
            && ($plan->allowed_roles === null || in_array($user->role, $plan->allowed_roles, true))) {
            $user->forceFill(['plan_id' => $plan->id])->save();
        }

        // Si es embajador y aún no tiene plan, asignar el plan gratis "embajador".
        if ($user->role === 'ambassador' && $user->plan_id === null) {
            $embPlan = Plan::query()->active()->where('slug', 'embajador')->first();
            if ($embPlan !== null) {
                $user->forceFill(['plan_id' => $embPlan->id])->save();
            }
        }

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
    public function verifyEmail(VerifyEmailRequest $request, EmailVerificationService $verification, ReferralService $referrals): JsonResponse
    {
        $email = mb_strtolower((string) $request->input('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user === null || $user->hasVerifiedEmail() || ! $verification->verify($user, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => 'El código no es válido o ha expirado.',
            ]);
        }

        // Revoke all previous tokens so stale sessions don't survive a re-registration.
        $user->tokens()->delete();

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

        if ($user->isAmbassador()) {
            try {
                $code = $referrals->ensureCode($user);
                $referralLink = rtrim((string) config('app.frontend_url'), '/').'/register?ref='.$code;
                $reward = (float) ($user->plan?->referral_reward ?? config('billing.referral_reward', 5.0));
                Mail::to($user->email)->send(new AmbassadorWelcomeMail(
                    name: (string) $user->name,
                    referralLink: $referralLink,
                    rewardPerReferral: $reward,
                ));
            } catch (\Throwable) {
                // El fallo del email de bienvenida no debe bloquear la verificación.
            }
        }

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
    public function store(LoginRequest $request, TotpService $totp, EmailVerificationService $verification): JsonResponse
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

        if (! $user->hasVerifiedEmail()) {
            $verification->issue($user); // respeta cooldown

            return response()->json([
                'error' => [
                    'code' => 'email_not_verified',
                    'message' => 'Debes verificar tu correo antes de iniciar sesión.',
                    'email' => $user->email,
                ],
            ], 403);
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

    /**
     * Slug compartible del embajador a partir del nombre, con sufijo numérico si colisiona.
     * Evita palabras reservadas para que no choque con rutas del sitio.
     */
    private function generateUniqueAmbassadorSlug(string $name): string
    {
        $reserved = config('ambassador.reserved_slugs', [
            'admin', 'api', 'app', 'login', 'logout', 'register', 'reset',
            'verify', 'me', 'cuenta', 'panel', 'embajador', 'embajadores',
            'tatuador', 'tatuadores', 'cliente', 'clientes', 'r', 'e', 't', 'u',
        ]);

        $base = Str::slug($name) ?: 'embajador';
        $base = mb_substr($base, 0, 40);

        $candidate = $base;
        $i = 1;

        while (
            in_array($candidate, $reserved, true) ||
            User::query()->where('ambassador_slug', $candidate)->exists()
        ) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
