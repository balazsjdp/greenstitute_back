<?php

namespace App\Policies;

use App\Models\User;

class CertPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return $user->is_admin; // Only admin users can view posts
    }
}
