<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tatuador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tatuador>
 */
final class TatuadorFactory extends Factory
{
    protected $model = Tatuador::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'studio_name' => $this->faker->company().' Studio',
            'artist_name' => $this->faker->name(),
            'city' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'maps_url' => 'https://maps.google.com/?q='.$this->faker->latitude().','.$this->faker->longitude(),
            'lat' => $this->faker->latitude(36, 43),
            'lng' => $this->faker->longitude(-9, 3),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
