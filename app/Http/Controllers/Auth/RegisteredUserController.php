<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'yearlyPlans' => Plan::query()
                ->where('billing_cycle', 'yearly')
                ->where('is_active', true)
                ->where('price', '>', 0)
                ->orderBy('price')
                ->get(['id', 'name', 'price', 'max_tattoos']),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:artist,user'],
            'plan_id' => [
                'nullable',
                'required_if:role,user',
                Rule::exists(table: 'plans', column: 'id')->where(
                    fn ($query) => $query
                        ->where('billing_cycle', 'yearly')
                        ->where('is_active', true)
                        ->where('price', '>', 0)
                ),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'plan_id' => $request->role === 'user' ? (int) $request->plan_id : null,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('profile.index'));
    }
}
