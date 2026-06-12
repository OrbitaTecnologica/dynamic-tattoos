<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Tatuadores;
use App\Mail\TatuadorApprovedMail;
use App\Models\TatuadorSolicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

final class TatuadorApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function pendingSolicitud(string $email = 'iker@donostiaink.es'): TatuadorSolicitud
    {
        return TatuadorSolicitud::query()->create([
            'name' => 'Iker Zubiaurre',
            'studio_name' => 'Donostia Ink',
            'city' => 'San Sebastián',
            'email' => $email,
            'phone' => '+34 943 111 222',
            'status' => TatuadorSolicitud::STATUS_PENDING,
        ]);
    }

    public function test_livewire_approve_creates_user_and_sends_email(): void
    {
        Mail::fake();
        $this->actingAs($this->admin());

        $sol = $this->pendingSolicitud();

        Livewire::test(Tatuadores::class)
            ->call('approveSolicitud', $sol->id)
            ->assertHasNoErrors();

        $user = User::query()->where('email', 'iker@donostiaink.es')->firstOrFail();
        $this->assertSame('artist', $user->role);

        $sol->refresh();
        $this->assertSame(TatuadorSolicitud::STATUS_APPROVED, $sol->status);
        $this->assertSame($user->id, $sol->user_id);
        $this->assertNotNull($sol->approved_at);

        Mail::assertSent(TatuadorApprovedMail::class, fn ($mail) => $mail->user->id === $user->id);
    }

    public function test_livewire_approve_promotes_existing_user_to_artist(): void
    {
        Mail::fake();
        $this->actingAs($this->admin());

        // Usuario que ya existía como cliente
        $existing = User::factory()->create([
            'email' => 'iker@donostiaink.es',
            'role' => 'user',
        ]);

        $sol = $this->pendingSolicitud();

        Livewire::test(Tatuadores::class)->call('approveSolicitud', $sol->id);

        $this->assertSame('artist', $existing->fresh()->role);
        $this->assertSame($existing->id, $sol->fresh()->user_id);
    }

    public function test_api_approve_endpoint_requires_admin(): void
    {
        Mail::fake();

        $sol = $this->pendingSolicitud();
        $client = User::factory()->create(['role' => 'user']);

        $this->actingAs($client, 'sanctum')
            ->postJson('/api/v1/admin/tatuadores/solicitudes/'.$sol->id.'/aprobar')
            ->assertForbidden();
    }

    public function test_api_approve_endpoint_marks_solicitud_processed(): void
    {
        Mail::fake();

        $sol = $this->pendingSolicitud();

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/v1/admin/tatuadores/solicitudes/'.$sol->id.'/aprobar')
            ->assertOk()
            ->assertJsonStructure(['data' => ['user_id', 'solicitud_id']]);

        $this->assertSame(TatuadorSolicitud::STATUS_APPROVED, $sol->fresh()->status);
    }

    public function test_double_approval_returns_error(): void
    {
        Mail::fake();

        $sol = $this->pendingSolicitud();
        $sol->update(['status' => TatuadorSolicitud::STATUS_APPROVED]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/v1/admin/tatuadores/solicitudes/'.$sol->id.'/aprobar')
            ->assertStatus(422);
    }
}
