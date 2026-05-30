<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Models\TattooScan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StatsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $totalScans = TattooScan::query()
            ->whereIn('tattoo_id', $user->tattoos()->select('id'))
            ->count();

        return response()->json([
            'data' => [
                'qr_generated' => $user->qrCodes()->count(),
                'total_scans' => $totalScans,
                'team_members' => $user->teamMembers()->count(),
                'storage' => $user->storageUsage(),
            ],
        ]);
    }
}
