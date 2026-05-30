<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\TeamMember;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TeamInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public TeamMember $member) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Te han invitado a un equipo · Dynamic Tattoos',
        );
    }

    public function content(): Content
    {
        $base = (string) config('app.frontend_url', config('app.url'));

        return new Content(
            view: 'emails.team-invitation',
            with: [
                'role' => $this->member->role,
                'acceptUrl' => $base.'/mi-cuenta?invitation='.$this->member->invitation_token,
            ],
        );
    }
}
