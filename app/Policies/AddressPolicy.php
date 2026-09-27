<?php

namespace App\Policies;

use App\Models\User;
use App\Models\address;

class AddressPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function show(User $user, address $address)
    {
        return $user->id === $address->user->id;
    }
}
