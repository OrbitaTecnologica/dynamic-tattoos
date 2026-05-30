<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateCompanyRequest;
use App\Http\Resources\Api\V1\CompanyResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompanyController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new CompanyResource($this->resolve($request->user())),
        ]);
    }

    public function update(UpdateCompanyRequest $request): JsonResponse
    {
        $company = $this->resolve($request->user());
        $company->update($request->validated());

        return response()->json([
            'data' => new CompanyResource($company),
        ]);
    }

    private function resolve(User $user): Company
    {
        return Company::query()->firstOrCreate(['user_id' => $user->id]);
    }
}
