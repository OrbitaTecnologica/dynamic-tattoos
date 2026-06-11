<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\TatuadorSolicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TatuadorSolicitudMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public TatuadorSolicitud $solicitud) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva solicitud de homologación · '.$this->solicitud->studio_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tatuador-solicitud',
            with: ['s' => $this->solicitud],
        );
    }
}
