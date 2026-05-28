<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Helper temporal: mientras no exista auth real, devolvemos siempre
 * el mismo usuario "demo" para todas las funciones del panel.
 * Cuando se conecte Laravel Auth basta con cambiar `get()` por
 * `auth()->user()` y mantener el resto del código intacto.
 */
class CurrentUser
{
    public static function get(): User
    {
        return User::firstOrCreate(
            ['email' => 'demo@dtattoos.test'],
            [
                'name'              => 'Cliente Demo',
                'password'          => Hash::make('demo-dtattoos'),
                'is_premium'        => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
