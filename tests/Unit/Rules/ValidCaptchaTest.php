<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\ValidCaptcha;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ValidCaptchaTest extends TestCase
{
    public function test_passes_when_secret_is_empty(): void
    {
        config(['services.turnstile.secret' => '']);

        $failed = false;
        (new ValidCaptcha())->validate('captcha_token', '', function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_fails_when_secret_set_but_token_missing(): void
    {
        config(['services.turnstile.secret' => 'test-secret']);

        $failed = false;
        (new ValidCaptcha())->validate('captcha_token', '', function () use (&$failed): void {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_passes_when_provider_returns_success(): void
    {
        config([
            'services.turnstile.secret' => 'test-secret',
            'services.turnstile.verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        ]);
        Http::fake(['*' => Http::response(['success' => true], 200)]);

        $failed = false;
        (new ValidCaptcha())->validate('captcha_token', 'any-token', function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_fails_when_provider_returns_failure(): void
    {
        config([
            'services.turnstile.secret' => 'test-secret',
            'services.turnstile.verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        ]);
        Http::fake(['*' => Http::response(['success' => false], 200)]);

        $failed = false;
        (new ValidCaptcha())->validate('captcha_token', 'bad-token', function () use (&$failed): void {
            $failed = true;
        });

        $this->assertTrue($failed);
    }
}
