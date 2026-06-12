<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\TatuadorSolicitudMail;
use App\Models\Tatuador;
use Database\Seeders\TatuadorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class TatuadorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_active_tatuadores_for_the_map(): void
    {
        Tatuador::query()->create([
            'studio_name' => 'Black Needle Studio',
            'artist_name' => 'Marcos Ruiz',
            'city' => 'Madrid',
            'lat' => 40.4200,
            'lng' => -3.7025,
            'is_active' => true,
        ]);
        Tatuador::query()->create([
            'studio_name' => 'Inactivo',
            'city' => 'Vigo',
            'lat' => 42.2,
            'lng' => -8.7,
            'is_active' => false,
        ]);

        $this->getJson('/api/v1/tatuadores')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.studio_name', 'Black Needle Studio')
            ->assertJsonStructure(['data' => [['id', 'studio_name', 'artist_name', 'city', 'lat', 'lng']]]);
    }

    public function test_submit_application_creates_pending_record_and_notifies_admin(): void
    {
        Mail::fake();
        config(['contact.to' => 'idi@dynamic-tattoos.test']);

        $this->postJson('/api/v1/tatuadores/solicitud', [
            'name' => 'Iker Zubiaurre',
            'studio_name' => 'Donostia Ink',
            'city' => 'San Sebastián',
            'email' => 'iker@donostiaink.es',
            'phone' => '+34 943 111 222',
            'message' => '10 años de experiencia.',
        ])->assertCreated();

        $this->assertDatabaseHas('tatuador_solicitudes', [
            'studio_name' => 'Donostia Ink',
            'email' => 'iker@donostiaink.es',
            'status' => 'pending',
        ]);

        Mail::assertSent(TatuadorSolicitudMail::class, static function (TatuadorSolicitudMail $mail): bool {
            return $mail->hasTo('idi@dynamic-tattoos.test');
        });
    }

    public function test_application_requires_name_studio_and_email(): void
    {
        $this->postJson('/api/v1/tatuadores/solicitud', [])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['name', 'studio_name', 'email']]]);
    }

    public function test_search_q_filters_by_city(): void
    {
        Tatuador::factory()->create(['studio_name' => 'Ink Palace', 'artist_name' => 'Ana López', 'city' => 'Madrid', 'is_active' => true]);
        Tatuador::factory()->create(['studio_name' => 'Ocean Tattoo', 'artist_name' => 'Pedro Mar', 'city' => 'Barcelona', 'is_active' => true]);

        $this->getJson('/api/v1/tatuadores?q=madrid')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.studio_name', 'Ink Palace');
    }

    public function test_search_q_filters_by_studio_name(): void
    {
        Tatuador::factory()->create(['studio_name' => 'Dragon Ink Studio', 'city' => 'Sevilla', 'is_active' => true]);
        Tatuador::factory()->create(['studio_name' => 'Royal Needles', 'city' => 'Bilbao', 'is_active' => true]);

        $this->getJson('/api/v1/tatuadores?q=dragon')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.studio_name', 'Dragon Ink Studio');
    }

    public function test_seeder_creates_the_five_certified_studios(): void
    {
        $this->seed(TatuadorSeeder::class);

        $this->assertSame(5, Tatuador::query()->active()->count());
        $this->assertDatabaseHas('tatuadores', ['studio_name' => 'Black Needle Studio', 'city' => 'Madrid']);
    }
}
