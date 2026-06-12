<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicAmbassadorLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_renders_and_records_visit(): void
    {
        $user = User::factory()->create([
            'name' => 'Carlos Tattoos',
            'role' => 'ambassador',
            'ambassador_slug' => 'carlos-tattoos',
            'referral_code' => 'CTAT0001',
        ]);

        $this->get('/e/carlos-tattoos')
            ->assertOk()
            ->assertSee('Carlos', false)
            ->assertSee('CTAT0001', false);

        $this->assertDatabaseHas('referral_visits', [
            'referrer_id' => $user->id,
            'code' => 'CTAT0001',
        ]);
    }

    public function test_landing_404_for_unknown_slug(): void
    {
        $this->get('/e/no-existe')->assertNotFound();
    }

    public function test_landing_404_for_non_ambassador_user(): void
    {
        User::factory()->create([
            'role' => 'user',
            'ambassador_slug' => 'no-soy-embajador',
        ]);

        $this->get('/e/no-soy-embajador')->assertNotFound();
    }
}
