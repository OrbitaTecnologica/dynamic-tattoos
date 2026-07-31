<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 2FA obligatorio para el panel: un admin sin 2FA configurado es redirigido al
 * enrolamiento antes de poder usar cualquier página de /admin. El challenge en
 * el login cubre la verificación por sesión; aquí solo se exige el enrolamiento.
 */
final class EnsureAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isAdmin() && ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        return $next($request);
    }
}
