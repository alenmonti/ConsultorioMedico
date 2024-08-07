<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\User;

class HistoriaClinicaPolicy
{
    /**
     * Create a new policy instance.
     */

    function viewAny(User $user)
    {
        return in_array($user->rol, [Roles::Admin, Roles::Medico]);
    }
}
