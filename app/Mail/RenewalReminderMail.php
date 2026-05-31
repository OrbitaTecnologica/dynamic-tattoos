<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class RenewalReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu plan se renovará pronto · Dynamic Tattoos',
        );
    }

    public function content(): Content
    {
        $plan = $this->user->plan;

        return new Content(
            view: 'emails.renewal-reminder',
            with: [
                'name' => $this->user->first_name ?: $this->user->name,
                'planName' => $plan?->name ?? 'tu plan',
                'amount' => $plan !== null ? number_format((float) $plan->price, 2, ',', '.').' €' : null,
                'renewsAt' => $this->user->renews_at?->format('d/m/Y'),
            ],
        );
    }
}
