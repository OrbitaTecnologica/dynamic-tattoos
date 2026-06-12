<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Tatuadores;
use App\Models\Tatuador;
use App\Models\TatuadorSolicitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class TatuadorAdminTest extends TestCase
{
    use RefreshDatabase;

    private function pendingSolicitud(): TatuadorSolicitud
    {
        return TatuadorSolicitud::query()->create([
            'name' => 'Iker Zubiaurre',
            'studio_name' => 'Donostia Ink',
            'city' => 'San Sebastián',
            'email' => 'iker@donostiaink.es',
            'phone' => '+34 943 111 222',
            'status' => TatuadorSolicitud::STATUS_PENDING,
        ]);
    }

    public function test_admin_can_create_toggle_and_delete_a_tatuador(): void
    {
        Livewire::test(Tatuadores::class)
            ->set('studioName', 'Nuevo Estudio')
            ->set('city', 'Madrid')
            ->set('lat', '40.4200')
            ->set('lng', '-3.7025')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tatuadores', ['studio_name' => 'Nuevo Estudio', 'city' => 'Madrid']);

        $t = Tatuador::query()->where('studio_name', 'Nuevo Estudio')->firstOrFail();

        Livewire::test(Tatuadores::class)->call('toggleActive', $t->id);
        $this->assertFalse($t->fresh()->is_active);

        Livewire::test(Tatuadores::class)->call('deleteTatuador', $t->id);
        $this->assertDatabaseMissing('tatuadores', ['id' => $t->id]);
    }

    public function test_save_requires_studio_city_and_coordinates(): void
    {
        Livewire::test(Tatuadores::class)
            ->call('save')
            ->assertHasErrors(['studioName', 'city', 'lat', 'lng']);
    }

    public function test_admin_can_reject_an_application(): void
    {
        $sol = $this->pendingSolicitud();

        Livewire::test(Tatuadores::class)->call('rejectSolicitud', $sol->id);

        $this->assertSame(TatuadorSolicitud::STATUS_REJECTED, $sol->fresh()->status);
    }

    public function test_certifying_an_application_prefills_and_save_creates_and_approves(): void
    {
        $sol = $this->pendingSolicitud();

        Livewire::test(Tatuadores::class)
            ->call('certifySolicitud', $sol->id)
            ->assertSet('studioName', 'Donostia Ink')
            ->assertSet('city', 'San Sebastián')
            ->set('lat', '43.3100')
            ->set('lng', '-1.9800')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tatuadores', ['studio_name' => 'Donostia Ink', 'city' => 'San Sebastián']);
        $this->assertSame(TatuadorSolicitud::STATUS_APPROVED, $sol->fresh()->status);
    }
}
