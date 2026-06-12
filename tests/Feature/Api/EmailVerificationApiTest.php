<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class EmailVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedCode(User $user, string $code = '123456'): void
    {
        EmailVerificationCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(15),
            'attempts' => 0,
        ]);
    }

    public function test_verify_returns_token_and_marks_verified(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'pending@example.com']);
        $this->seedCode($user, '123456');

        $this->postJson('/api/v1/auth/email/verify', [
            'email' => 'pending@example.com',
            'code' => '123456',
            'device_name' => 'frontend-web',
        ])
            ->assertCreated()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_verify_rejects_wrong_code(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'pending@example.com']);
        $this->seedCode($user, '123456');

        $this->postJson('/api/v1/auth/email/verify', [
            'email' => 'pending@example.com',
            'code' => '000000',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['code']]]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_resend_sends_new_code(): void
    {
        Mail::fake();
        User::factory()->unverified()->create(['email' => 'pending@example.com']);

        $this->postJson('/api/v1/auth/email/resend', ['email' => 'pending@example.com'])
            ->assertStatus(202)
            ->assertJsonPath('data.requires_verification', true);

        $this->assertDatabaseCount('email_verification_codes', 1);
    }

    public function test_resend_returns_429_when_in_cooldown(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create(['email' => 'pending@example.com']);
        $this->seedCode($user); // crea un código reciente => cooldown activo

        $this->postJson('/api/v1/auth/email/resend', ['email' => 'pending@example.com'])
            ->assertStatus(429)
            ->assertJsonPath('error.code', 'resend_cooldown');
    }
}
