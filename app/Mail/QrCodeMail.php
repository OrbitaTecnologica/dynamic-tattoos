<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $qrBase64,
        public readonly string $targetUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu código QR · Dynamic Tattoos',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.qr-code',
        );
    }

    public function attachments(): array
    {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->qrBase64));

        return [
            Attachment::fromData(fn () => $imageData, 'qr-code.png')
                ->withMime('image/png'),
        ];
    }
}
