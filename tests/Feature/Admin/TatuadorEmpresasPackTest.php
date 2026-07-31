<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Companies;
use App\Livewire\Admin\Tatuadores;
use App\Models\Tatuador;
use App\Models\TatuadorSolicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

final class TatuadorEmpresasPackTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // ── Datos fiscales (Companies) ──────────────────────────────────────────

    public function test_company_edit_accepts_website_without_protocol(): void
    {
        $this->actingAs($this->admin());
        $owner = User::factory()->create();
        $company = $owner->company()->create(['is_professional' => false]);

        Livewire::test(Companies::class)
            ->call('openEdit', $company->id)
            ->set('name', 'Estudio Real')
            ->set('website', 'dominio-sin-protocolo.com')
            ->call('saveCompany')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertSame('https://dominio-sin-protocolo.com', $company->fresh()->website);
    }

    public function test_company_edit_accepts_long_names_created_via_api(): void
    {
        $this->actingAs($this->admin());
        $owner = User::factory()->create();
        $company = $owner->company()->create(['name' => str_repeat('N', 200)]);

        Livewire::test(Companies::class)
            ->call('openEdit', $company->id)
            ->call('saveCompany')
            ->assertHasNoErrors();
    }

    public function test_company_can_be_deleted(): void
    {
        $this->actingAs($this->admin());
        $owner = User::factory()->create();
        $company = $owner->company()->create(['is_professional' => false]);

        Livewire::test(Companies::class)->call('deleteCompany', $company->id);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    // ── Tatuadores: flujo único de aprobación ───────────────────────────────

    public function test_approve_creates_user_and_inactive_map_pin(): void
    {
        Mail::fake();
        $this->actingAs($this->admin());
        $sol = TatuadorSolicitud::create([
            'name' => 'Marta Ink',
            'studio_name' => 'Marta Ink Studio',
            'city' => 'Vigo',
            'email' => 'marta@ink.test',
            'phone' => '+34 600 000 000',
        ]);

        Livewire::test(Tatuadores::class)->call('approveSolicitud', $sol->id);

        $user = User::where('email', 'marta@ink.test')->firstOrFail();
        $this->assertSame('artist', $user->role);

        $pin = Tatuador::where('user_id', $user->id)->firstOrFail();
        $this->assertFalse($pin->is_active);
        $this->assertSame('Marta Ink Studio', $pin->studio_name);
        $this->assertNull($pin->lat);
        $this->assertSame(TatuadorSolicitud::STATUS_APPROVED, $sol->fresh()->status);
    }

    public function test_pin_can_be_saved_without_coordinates_when_inactive(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Tatuadores::class)
            ->call('openCreate')
            ->set('studioName', 'Sin Coords Studio')
            ->set('city', 'Madrid')
            ->set('isActive', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tatuadores', ['studio_name' => 'Sin Coords Studio', 'is_active' => false]);
    }

    public function test_publishing_requires_coordinates(): void
    {
        $this->actingAs($this->admin());

        // Guardar activo sin coords → error de validación
        Livewire::test(Tatuadores::class)
            ->call('openCreate')
            ->set('studioName', 'Activo Sin Coords')
            ->set('city', 'Madrid')
            ->set('isActive', true)
            ->call('save')
            ->assertHasErrors(['lat', 'lng']);

        // toggleActive sobre un pin sin coords → bloqueado
        $pin = Tatuador::create(['studio_name' => 'Pendiente', 'city' => 'Bilbao', 'is_active' => false]);
        Livewire::test(Tatuadores::class)->call('toggleActive', $pin->id);
        $this->assertFalse($pin->fresh()->is_active);
    }

    public function test_maps_url_paste_extracts_coordinates(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(Tatuadores::class)
            ->call('openCreate')
            ->set('mapsUrl', 'https://www.google.com/maps/place/Estudio/@40.4200000,-3.7025000,15z/data=xyz')
            ->assertSet('lat', '40.4200000')
            ->assertSet('lng', '-3.7025000');
    }
}
