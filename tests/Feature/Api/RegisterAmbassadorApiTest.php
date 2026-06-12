<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\AmbassadorTierSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class RegisterAmbassadorApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PlanSeeder::class, AmbassadorTierSeeder::class]);
    }

    public function test_registering_as_ambassador_assigns_default_tier_slug_and_free_plan(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Laura Embajadora',
            'email' => 'laura@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => 'ambassador',
        ])->assertStatus(202);

        $user = User::query()->where('email', 'laura@example.com')->firstOrFail();

        $this->assertSame('ambassador', $user->role);
        $this->assertNotNull($user->ambassador_tier_id, 'Debe tener tier por defecto');
        $this->assertSame('bronze', $user->tier->slug);
        $this->assertNotNull($user->ambassador_slug, 'Debe generar slug compartible');
        $this->assertSame('laura-embajadora', $user->ambassador_slug);

        $embPlan = Plan::query()->where('slug', 'embajador')->firstOrFail();
        $this->assertSame($embPlan->id, $user->plan_id);
    }

    public function test_default_role_is_user_when_not_specified(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Carlos Cliente',
            'email' => 'carlos@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertStatus(202);

        $user = User::query()->where('email', 'carlos@example.com')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertNull($user->ambassador_tier_id);
        $this->assertNull($user->ambassador_slug);
    }

    public function test_role_artist_is_rejected_via_register_endpoint(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'No Permitido',
            'email' => 'noart@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => 'artist',
        ])->assertStatus(422);
    }

    public function test_plan_embajador_without_role_derives_ambassador_role(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Pedro Plan',
            'email' => 'pedro@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'plan' => 'embajador',
        ])->assertStatus(202);

        $user = User::query()->where('email', 'pedro@example.com')->firstOrFail();

        $this->assertSame('ambassador', $user->role);
        $this->assertSame('bronze', $user->tier->slug);
        $this->assertNotNull($user->ambassador_slug);

        $embPlan = Plan::query()->where('slug', 'embajador')->firstOrFail();
        $this->assertSame($embPlan->id, $user->plan_id);
    }

    public function test_plan_empresa_without_role_derives_ambassador_role(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Nora Negocio',
            'email' => 'nora@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'plan' => 'empresa',
        ])->assertStatus(202);

        $user = User::query()->where('email', 'nora@example.com')->firstOrFail();

        $this->assertSame('ambassador', $user->role);
        $this->assertNotNull($user->ambassador_slug);

        // Empresa es de pago: el webhook de Stripe lo fija tras el checkout.
        // Mientras tanto queda el plan gratis Embajador como placeholder.
        $embPlan = Plan::query()->where('slug', 'embajador')->firstOrFail();
        $this->assertSame($embPlan->id, $user->plan_id);
    }

    public function test_plan_start_without_role_keeps_user_role(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Tomás Tatuador',
            'email' => 'tomas@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'plan' => 'start',
        ])->assertStatus(202);

        $user = User::query()->where('email', 'tomas@example.com')->firstOrFail();

        $this->assertSame('user', $user->role);
        $this->assertNull($user->ambassador_tier_id);
        $this->assertNull($user->ambassador_slug);
        $this->assertNull($user->plan_id, 'Plan de pago no se asigna en registro');
    }

    public function test_explicit_user_role_is_not_overridden_and_plan_respects_allowed_roles(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Usuario Explícito',
            'email' => 'explicito@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => 'user',
            'plan' => 'embajador',
        ])->assertStatus(202);

        $user = User::query()->where('email', 'explicito@example.com')->firstOrFail();

        $this->assertSame('user', $user->role);
        $this->assertNull($user->plan_id, 'El plan embajador no admite rol user (allowed_roles)');
    }

    public function test_ambassador_slug_collisions_get_numeric_suffix(): void
    {
        Mail::fake();

        // Primer embajador
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Laura Embajadora',
            'email' => 'laura1@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => 'ambassador',
        ])->assertStatus(202);

        // Segundo embajador con mismo nombre
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Laura Embajadora',
            'email' => 'laura2@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => 'ambassador',
        ])->assertStatus(202);

        $u2 = User::query()->where('email', 'laura2@example.com')->firstOrFail();
        $this->assertSame('laura-embajadora-1', $u2->ambassador_slug);
    }
}
