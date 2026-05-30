<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\TeamMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refreshes `last_active_at` for the authenticated user's team membership,
 * throttled to at most once per minute to avoid a write on every request.
 */
final class TrackTeamMemberActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if ($user !== null) {
            TeamMember::query()
                ->where('member_user_id', $user->id)
                ->where(function ($query): void {
                    $query->whereNull('last_active_at')
                        ->orWhere('last_active_at', '<', now()->subMinute());
                })
                ->update(['last_active_at' => now()]);
        }

        return $response;
    }
}
