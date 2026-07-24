<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Efectos colaterales del borrado suave (papelera) de un usuario.
 *
 * Un `User` es a la vez modelo de autenticación, Billable de Cashier y dueño de
 * contenido público (Linktree, tatuajes, QRs). Enviarlo a la papelera sin cuidado
 * rompería cosas en producción, así que aquí se cubren los riesgos detectados en la
 * auditoría previa. El borrado DEFINITIVO (forceDelete) no pasa por aquí: el cascade
 * de claves foráneas de la BD limpia todo lo asociado.
 */
final class UserObserver
{
    public function deleting(User $user): void
    {
        // El borrado definitivo lo resuelve el cascade de la BD; no tocamos nada.
        if ($user->isForceDeleting()) {
            return;
        }

        // 1. Revocar tokens de API (Sanctum) para que no revivan al restaurar.
        //    Las sesiones web las cubre el global scope de SoftDeletes: un usuario
        //    en papelera no puede autenticarse en el próximo request.
        $user->tokens()->delete();

        // 2. Cancelar la suscripción Stripe activa para que deje de generar cargos.
        //    No-op si el usuario no tiene cuenta de Stripe (caso de las cuentas de prueba).
        if ($user->hasStripeId()) {
            try {
                $subscription = $user->subscription('default');
                if ($subscription !== null && $subscription->valid()) {
                    $subscription->cancelNow();
                }
            } catch (\Throwable $e) {
                Log::warning('No se pudo cancelar la suscripción Stripe al enviar a papelera', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3. Liberar el email único (reversible al restaurar). Sin esto, registrar
        //    de nuevo ese email chocaría con el índice unique de la tabla y daría 500.
        $prefix = User::TRASH_EMAIL_PREFIX.$user->id.'_';
        if (! str_starts_with((string) $user->email, $prefix)) {
            $user->forceFill(['email' => $prefix.$user->email])->saveQuietly();
        }

        // 4. Despublicar el contenido público: deja de resolver mientras esté en papelera.
        $user->linkPage()->update(['is_published' => false]);
        $user->tattoos()->update(['is_active' => false]);
    }

    public function restoring(User $user): void
    {
        // Revertir el renombrado del email.
        $prefix = User::TRASH_EMAIL_PREFIX.$user->id.'_';
        if (str_starts_with((string) $user->email, $prefix)) {
            $user->email = substr((string) $user->email, strlen($prefix));
        }
    }

    public function restored(User $user): void
    {
        // Volver a publicar el contenido.
        $user->linkPage()->update(['is_published' => true]);
        $user->tattoos()->update(['is_active' => true]);
    }
}
