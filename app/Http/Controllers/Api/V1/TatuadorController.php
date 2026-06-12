<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TatuadorResource;
use App\Models\Tatuador;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TatuadorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Tatuador::query()->active()->ordered();

        if (is_string($q = $request->query('q')) && trim($q) !== '') {
            $q = trim($q);
            $query->where(function ($w) use ($q): void {
                $w->where('studio_name', 'like', "%{$q}%")
                    ->orWhere('artist_name', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%");
            });
        }

        return TatuadorResource::collection($query->get());
    }
}
