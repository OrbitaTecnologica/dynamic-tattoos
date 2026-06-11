<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tatuador;
use Illuminate\Database\Seeder;

final class TatuadorSeeder extends Seeder
{
    public function run(): void
    {
        $tatuadores = [
            ['studio_name' => 'Black Needle Studio', 'artist_name' => 'Marcos Ruiz', 'city' => 'Madrid', 'address' => 'Gran Vía 45, Madrid', 'phone' => '+34 911 234 567', 'email' => 'madrid@blackneedle.es', 'maps_url' => 'https://maps.google.com/?q=Gran+Via+45+Madrid', 'lat' => 40.4200, 'lng' => -3.7025],
            ['studio_name' => 'Ink Karma BCN', 'artist_name' => 'Sara Font', 'city' => 'Barcelona', 'address' => 'Carrer de Gràcia 112, Barcelona', 'phone' => '+34 932 456 789', 'email' => 'bcn@inkkarma.es', 'maps_url' => 'https://maps.google.com/?q=Carrer+de+Gracia+112+Barcelona', 'lat' => 41.3995, 'lng' => 2.1575],
            ['studio_name' => 'Valencia Tattoo Co.', 'artist_name' => 'Luis Campos', 'city' => 'Valencia', 'address' => 'Av. del Puerto 78, Valencia', 'phone' => '+34 963 987 654', 'email' => 'info@valenciatattoo.es', 'maps_url' => 'https://maps.google.com/?q=Avenida+del+Puerto+78+Valencia', 'lat' => 39.4699, 'lng' => -0.3763],
            ['studio_name' => 'Arte en Piel', 'artist_name' => 'Carmen Vega', 'city' => 'Sevilla', 'address' => 'Calle Sierpes 33, Sevilla', 'phone' => '+34 954 321 098', 'email' => 'sevilla@arteenpiel.es', 'maps_url' => 'https://maps.google.com/?q=Calle+Sierpes+33+Sevilla', 'lat' => 37.3891, 'lng' => -5.9845],
            ['studio_name' => 'Bilbao Ink Lab', 'artist_name' => 'Jon Etxebarria', 'city' => 'Bilbao', 'address' => 'Gran Vía Don Diego López de Haro 22, Bilbao', 'phone' => '+34 944 123 456', 'email' => 'info@bilbaoinknklab.es', 'maps_url' => 'https://maps.google.com/?q=Gran+Via+22+Bilbao', 'lat' => 43.2630, 'lng' => -2.9350],
        ];

        foreach ($tatuadores as $i => $data) {
            Tatuador::query()->updateOrCreate(
                ['studio_name' => $data['studio_name']],
                $data + ['is_active' => true, 'sort_order' => $i + 1],
            );
        }
    }
}
