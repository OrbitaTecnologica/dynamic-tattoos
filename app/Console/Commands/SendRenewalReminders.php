<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\RenewalReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Envía un recordatorio ~1 mes antes de la renovación anual. Pensado para
 * ejecutarse a diario; el flag renewal_reminded_at evita duplicados y el
 * webhook lo reinicia tras cada renovación.
 */
final class SendRenewalReminders extends Command
{
    protected $signature = 'subscriptions:send-renewal-reminders {--days=30 : Días de antelación}';

    protected $description = 'Avisa a los usuarios cuya suscripción se renueva en ~1 mes';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $users = User::query()
            ->whereNotNull('renews_at')
            ->whereNull('renewal_reminded_at')
            ->where('renews_at', '>', now())
            ->where('renews_at', '<=', now()->addDays($days))
            ->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new RenewalReminderMail($user));
            $user->forceFill(['renewal_reminded_at' => now()])->save();

            activity('billing')
                ->causedBy($user)
                ->event('renewal_reminder_sent')
                ->withProperties(['detail' => 'Renovación el '.$user->renews_at?->format('d/m/Y')])
                ->log('Recordatorio de renovación enviado');
        }

        $this->info("Recordatorios enviados: {$users->count()}");

        return self::SUCCESS;
    }
}
