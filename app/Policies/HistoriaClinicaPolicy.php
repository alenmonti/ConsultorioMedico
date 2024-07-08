<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\HistoriaClinica;
use App\Models\User;

class HistoriaClinicaPolicy
{
    /**
     * Create a new policy instance.
     */

    function viewAny(User $user)
    {
        // return $user->rol === Roles::Admin;
        return true;
    }

    
}
