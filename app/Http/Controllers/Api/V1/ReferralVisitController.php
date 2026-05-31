<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Referrals\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReferralVisitController extends Controller
{
    public function __invoke(Request $request, ReferralService $referrals): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $referrals->recordVisit($validated['code'], $request->ip(), $request->userAgent());

        return response()->json(['message' => 'ok']);
    }
}
