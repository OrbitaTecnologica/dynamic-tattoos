<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\EmailVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class EmailVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_sends_mail_and_stores_hashed_code(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create();

        $issued = app(EmailVerificationService::class)->issue($user);

        $this->assertTrue($issued);
        Mail::assertSent(EmailVerificationCodeMail::class);
        $this->assertDatabaseCount('email_verification_codes', 1);
        $this->assertNotEquals('', EmailVerificationCode::first()->code_hash);
    }

    public function test_issue_respects_resend_cooldown(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create();
        $service = app(EmailVerificationService::class);

        $this->assertTrue($service->issue($user));
        $this->assertFalse($service->issue($user)); // dentro del cooldown
        Mail::assertSentCount(1);
    }

    public function test_verify_marks_email_verified_with_correct_code(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create();
        $service = app(EmailVerificationService::class);
        $service->issue($user);

        EmailVerificationCode::query()->where('user_id', $user->id)->update([
            'code_hash' => hash('sha256', '123456'),
            'consumed_at' => null,
        ]);

        $ok = $service->verify($user->fresh(), '123456');

        $this->assertTrue($ok);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_fails_with_wrong_code(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create();
        $service = app(EmailVerificationService::class);
        $service->issue($user);
        EmailVerificationCode::query()->where('user_id', $user->id)->update([
            'code_hash' => hash('sha256', '123456'),
        ]);

        $this->assertFalse($service->verify($user->fresh(), '000000'));
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_verify_fails_after_max_attempts(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create();
        $service = app(EmailVerificationService::class);
        $service->issue($user);
        EmailVerificationCode::query()->where('user_id', $user->id)->update([
            'code_hash' => hash('sha256', '123456'),
            'attempts' => (int) config('verification.max_attempts'),
        ]);

        $this->assertFalse($service->verify($user->fresh(), '123456'));
    }
}
