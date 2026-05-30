<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class TeamMemberPolicy
{
    public function manage(User $user, TeamMember $member): Response
    {
        if ($user->id !== $member->owner_id) {
            return Response::deny('No tienes permiso para gestionar este miembro.');
        }

        if ($member->role === 'owner') {
            return Response::deny('No puedes modificar al propietario.');
        }

        return Response::allow();
    }

    public function update(User $user, TeamMember $member): Response
    {
        return $this->manage($user, $member);
    }

    public function delete(User $user, TeamMember $member): Response
    {
        return $this->manage($user, $member);
    }
}
