<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LinkPageLink;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class LinkPageLinkPolicy
{
    public function update(User $user, LinkPageLink $link): Response
    {
        return $this->owns($user, $link)
            ? Response::allow()
            : Response::deny('No tienes permiso para modificar este enlace.');
    }

    public function delete(User $user, LinkPageLink $link): Response
    {
        return $this->owns($user, $link)
            ? Response::allow()
            : Response::deny('No tienes permiso para eliminar este enlace.');
    }

    private function owns(User $user, LinkPageLink $link): bool
    {
        return $user->id === $link->page->user_id;
    }
}
