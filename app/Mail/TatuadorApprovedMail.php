<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TatuadorApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $resetToken) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu solicitud de tatuador ha sido aprobada — Dynamic Tattoos',
        );
    }

    public function content(): Content
    {
        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $resetUrl = $frontend.'/reset-password?token='.$this->resetToken.'&email='.urlencode((string) $this->user->email);

        return new Content(
            view: 'mail.tatuador-approved',
            with: [
                'name' => $this->user->name,
                'resetUrl' => $resetUrl,
            ],
        );
    }
}
