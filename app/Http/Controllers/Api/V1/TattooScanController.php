<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TattooScanResource;
use App\Models\Tattoo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TattooScanController extends Controller
{
    public function index(Request $request, Tattoo $tattoo): AnonymousResourceCollection
    {
        $this->authorize('view', $tattoo);

        $scans = $tattoo->scans()
            ->latest('scanned_at')
            ->paginate(50);

        return TattooScanResource::collection($scans);
    }
}
