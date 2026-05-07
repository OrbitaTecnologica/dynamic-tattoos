<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SubscriptionActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Tu suscripcion esta activa')
            ->greeting('Tu plan se activo correctamente')
            ->line('Ya puedes administrar tu suscripcion y metodo de pago desde tu panel.')
            ->action('Ir a Facturacion', route('billing'));
    }
}
