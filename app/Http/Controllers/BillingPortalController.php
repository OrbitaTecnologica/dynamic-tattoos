<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BillingPortalController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->to($request->user()->billingPortalUrl(route('billing')));
    }
}
