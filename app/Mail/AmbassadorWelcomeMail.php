<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AmbassadorWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $referralLink,
        public readonly float $rewardPerReferral,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu enlace de embajador ya está listo! · Dynamic Tattoos',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.ambassador-welcome',
            with: [
                'name' => $this->name,
                'referralLink' => $this->referralLink,
                'rewardPerReferral' => $this->rewardPerReferral,
            ],
        );
    }
}
