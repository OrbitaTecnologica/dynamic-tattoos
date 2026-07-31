<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AdminLoginAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public string $ip,
        public string $userAgent,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo acceso al panel de administración · Dynamic Tattoos',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-login-alert',
            with: [
                'name' => $this->user->name,
                'ip' => $this->ip,
                'userAgent' => $this->userAgent,
                'when' => now()->format('d/m/Y H:i'),
            ],
        );
    }
}
