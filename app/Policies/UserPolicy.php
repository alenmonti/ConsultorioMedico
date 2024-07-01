<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */

    function viewAny(User $user)
    {
        return $user->rol === Roles::Admin;
    }
}
