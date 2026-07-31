<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\AdminLoginAlertMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;

/**
 * Registra cada login web en el activity log y avisa por email al admin cuando
 * el acceso llega desde una IP no vista antes. El histórico de IPs se deriva
 * del propio activity log (security/login), que persiste en ambos canales.
 */
final class AdminLoginNotifier
{
    public function record(User $user, Request $request): void
    {
        $ip = (string) $request->ip();

        $knownIp = Activity::query()
            ->where('log_name', 'security')
            ->where('event', 'login')
            ->where('causer_id', $user->id)
            ->where('properties->ip', $ip)
            ->exists();

        activity('security')
            ->causedBy($user)
            ->event('login')
            ->withProperties(['ip' => $ip, 'detail' => 'Panel web · '.$ip])
            ->log('Inicio de sesión');

        if (! $user->isAdmin() || $knownIp) {
            return;
        }

        // El fallo del email de alerta nunca debe bloquear el login.
        try {
            Mail::to($user->email)->send(new AdminLoginAlertMail(
                user: $user,
                ip: $ip,
                userAgent: (string) $request->userAgent(),
            ));
        } catch (\Throwable) {
        }
    }
}
