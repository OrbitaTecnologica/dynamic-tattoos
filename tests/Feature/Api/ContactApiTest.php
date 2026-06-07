<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\ContactMessageMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_form_sends_mail_to_admin(): void
    {
        Mail::fake();
        config(['contact.to' => 'admin@dynamic-tattoos.test']);

        $this->postJson('/api/v1/contact', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'subject' => 'Quiero un tatuaje dinámico',
            'message' => 'Hola, ¿cómo funciona?',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'ok');

        Mail::assertSent(ContactMessageMail::class, static function (ContactMessageMail $mail): bool {
            return $mail->hasTo('admin@dynamic-tattoos.test')
                && $mail->hasReplyTo('ada@example.com');
        });
    }

    public function test_contact_form_requires_name_email_and_message(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/contact', [
            'subject' => 'solo asunto',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['name', 'email', 'message']]]);

        Mail::assertNothingSent();
    }

    public function test_contact_form_rejects_invalid_email(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/contact', [
            'name' => 'Ada',
            'email' => 'no-es-un-email',
            'message' => 'Hola',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['email']]]);

        Mail::assertNothingSent();
    }
}
