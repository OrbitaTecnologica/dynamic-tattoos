<?php

namespace App\Http\Middleware;

use App\Support\CurrentUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePremium
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = CurrentUser::get();

        if (! $user->is_premium) {
            abort(403, 'Esta función está disponible solo para usuarios premium.');
        }

        return $next($request);
    }
}
