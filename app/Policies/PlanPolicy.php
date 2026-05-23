<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Plan $plan): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('No tienes permisos para crear planes.');
    }

    public function update(User $user, Plan $plan): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('No tienes permisos para editar planes.');
    }

    public function delete(User $user, Plan $plan): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('No tienes permisos para eliminar planes.');
    }
}
