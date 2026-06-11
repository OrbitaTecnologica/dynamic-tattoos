<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_unverified_user_without_token_and_sends_code(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Nuevo Cliente',
            'email' => 'Nuevo@Example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'device_name' => 'frontend-web',
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('data.requires_verification', true)
            ->assertJsonPath('data.email', 'nuevo@example.com');

        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com', 'role' => 'user', 'email_verified_at' => null]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Mail::assertSent(EmailVerificationCodeMail::class);
    }

    public function test_registration_rejects_duplicate_verified_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']); // factory => verificado

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Otro',
            'email' => 'taken@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['email']]]);
    }

    public function test_reregister_of_unverified_email_resends_code(): void
    {
        Mail::fake();
        User::factory()->unverified()->create(['email' => 'pending@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Pending',
            'email' => 'pending@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])
            ->assertStatus(202)
            ->assertJsonPath('data.requires_verification', true);

        Mail::assertSent(EmailVerificationCodeMail::class);
    }

    public function test_reregister_of_unverified_email_updates_name_and_password(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'name' => 'Nombre Viejo',
            'password' => 'OldSecret123!',
        ]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Nombre Nuevo',
            'email' => 'pending@example.com',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ])->assertStatus(202);

        $user->refresh();
        $this->assertSame('Nombre Nuevo', $user->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewSecret123!', (string) $user->password));
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Otro',
            'email' => 'mismatch@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Different1!',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['password']]]);
    }
}
