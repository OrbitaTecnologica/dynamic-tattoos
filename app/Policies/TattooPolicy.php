<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tattoo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * TattooPolicy
 *
 * Laravel 11 auto-discovers policies that follow the Model → Policy naming
 * convention, so no manual registration in AuthServiceProvider is needed.
 */
final class TattooPolicy
{
    /** Any authenticated user can view the public QR page (no policy gate needed). */

    public function view(User $user, Tattoo $tattoo): bool
    {
        return $user->id === $tattoo->user_id;
    }

    public function update(User $user, Tattoo $tattoo): Response
    {
        return $user->id === $tattoo->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para modificar este tatuaje.');
    }

    public function delete(User $user, Tattoo $tattoo): Response
    {
        return $user->id === $tattoo->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este tatuaje.');
    }
}
