<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('No pudimos procesar tu pago')
            ->greeting('Detectamos un problema con tu ultimo cobro')
            ->line('Actualiza tu metodo de pago para evitar interrupciones en tu suscripcion.')
            ->action('Actualizar Facturacion', rtrim((string) config('app.frontend_url'), '/').'/mi-cuenta');
    }
}
