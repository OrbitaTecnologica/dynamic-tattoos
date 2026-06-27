<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\QrCode;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class QrCodePolicy
{
    public function view(User $user, QrCode $qrCode): bool
    {
        return $user->id === $qrCode->user_id;
    }

    public function update(User $user, QrCode $qrCode): bool
    {
        return $user->id === $qrCode->user_id;
    }

    public function delete(User $user, QrCode $qrCode): Response
    {
        return $user->id === $qrCode->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este código QR.');
    }
}
