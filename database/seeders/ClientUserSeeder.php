<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tattoo;
use App\Models\TattooContent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class ClientUserSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::updateOrCreate(
            ['email' => 'cliente@dynamictattoos.test'],
            [
                'name'              => 'Carlos Cliente',
                'password'          => Hash::make('Cliente1234!'),
                'role'              => 'user',
                'email_verified_at' => now(),
            ]
        );

        // Tattoo 1: active link content
        $tattoo1 = Tattoo::updateOrCreate(
            ['user_id' => $client->id, 'name' => 'Mi primer tatuaje'],
            ['is_active' => true]
        );

        TattooContent::updateOrCreate(
            ['tattoo_id' => $tattoo1->id, 'type' => TattooContent::TYPE_LINK],
            [
                'payload'   => [
                    'url'             => 'https://instagram.com',
                    'label'           => 'Sígueme en Instagram',
                    'open_in_new_tab' => true,
                ],
                'is_active' => true,
                'order'     => 1,
            ]
        );

        TattooContent::updateOrCreate(
            ['tattoo_id' => $tattoo1->id, 'type' => TattooContent::TYPE_VIDEO],
            [
                'payload'   => [
                    'url'      => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'platform' => 'youtube',
                    'autoplay' => false,
                    'title'    => 'Mi proceso artístico',
                ],
                'is_active' => false,
                'order'     => 2,
            ]
        );

        // Tattoo 2: active gallery content
        $tattoo2 = Tattoo::updateOrCreate(
            ['user_id' => $client->id, 'name' => 'Galería de diseños'],
            ['is_active' => true]
        );

        TattooContent::updateOrCreate(
            ['tattoo_id' => $tattoo2->id, 'type' => TattooContent::TYPE_GALLERY],
            [
                'payload'   => [
                    'title'  => 'Mis diseños favoritos',
                    'images' => [],
                ],
                'is_active' => true,
                'order'     => 1,
            ]
        );
    }
}
