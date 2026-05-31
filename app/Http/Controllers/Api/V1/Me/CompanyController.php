<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateCompanyRequest;
use App\Http\Resources\Api\V1\CompanyResource;
use App\Models\Company;
use App\Models\User;
use App\Services\Billing\StripeTaxProfile;
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

    public function update(UpdateCompanyRequest $request, StripeTaxProfile $taxProfile): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $company = $this->resolve($user);
        $company->update($request->validated());

        // Propaga el NIF/IVA a Stripe si el cliente ya existe en Stripe.
        if (config('billing.tax_enabled') && $user->hasStripeId()) {
            $taxProfile->sync($user->refresh());
        }

        return response()->json([
            'data' => new CompanyResource($company),
        ]);
    }

    private function resolve(User $user): Company
    {
        return Company::query()->firstOrCreate(['user_id' => $user->id]);
    }
}
