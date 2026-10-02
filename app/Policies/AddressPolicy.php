<?php

namespace App\Policies;

use App\Models\User;

class AddressPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function show(User $user, Address $address)
    {
        return $user->id === $address->user->id;
    }
}
