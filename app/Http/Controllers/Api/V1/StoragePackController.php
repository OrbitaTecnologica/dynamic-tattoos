<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StoragePackResource;
use App\Models\StoragePack;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class StoragePackController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return StoragePackResource::collection(StoragePack::query()->active()->get());
    }
}
