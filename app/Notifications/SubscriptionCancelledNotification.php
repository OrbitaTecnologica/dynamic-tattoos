<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SubscriptionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Tu suscripcion fue cancelada')
            ->greeting('La suscripcion se marco como cancelada')
            ->line('Podras reactivarla en cualquier momento desde el panel de facturacion.')
            ->action('Ver Facturacion', route('billing'));
    }
}
