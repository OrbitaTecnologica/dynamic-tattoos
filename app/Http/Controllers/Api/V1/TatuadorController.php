<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TatuadorResource;
use App\Models\Tatuador;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TatuadorController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TatuadorResource::collection(
            Tatuador::query()->active()->ordered()->get(),
        );
    }
}
