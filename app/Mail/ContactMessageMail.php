<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public ?string $subjectLine,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        $subject = ($this->subjectLine !== null && $this->subjectLine !== '')
            ? $this->subjectLine
            : 'Nuevo mensaje de contacto';

        return new Envelope(
            subject: 'Contacto · '.$subject,
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
            with: [
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'subjectLine' => $this->subjectLine,
                'body' => $this->body,
            ],
        );
    }
}
