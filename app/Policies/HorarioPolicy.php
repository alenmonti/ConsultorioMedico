<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\Horario;
use App\Models\User;

class HorarioPolicy
{
    /**
     * Create a new policy instance.
     */

    function viewAny(User $user)
    {
        return $user->rol === Roles::Medico || $user->rol === Roles::Admin;
    }
}
