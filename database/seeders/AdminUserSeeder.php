<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@dynamictattoos.test'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('Admin1234!'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
