<?php
   
  
/**
 * Get the current authenticated user.
 *
 * @return \App\Models\User
 */
if (! function_exists('user')) {
    function user()
    {
        return auth()->user();
    }
}


/**
 * Check if the current authenticated user has the given role.
 *
 * @return string
 */
if (! function_exists('role')) {
    function role($role)
    {
        return user()->rol == $role;
    }
}
  