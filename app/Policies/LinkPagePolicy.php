<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LinkPage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class LinkPagePolicy
{
    public function view(User $user, LinkPage $page): bool
    {
        return $user->id === $page->user_id;
    }

    public function update(User $user, LinkPage $page): Response
    {
        return $user->id === $page->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para modificar esta tarjeta de enlaces.');
    }

    public function delete(User $user, LinkPage $page): Response
    {
        return $user->id === $page->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar esta tarjeta de enlaces.');
    }
}
