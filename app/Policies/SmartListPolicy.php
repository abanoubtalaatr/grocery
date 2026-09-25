<?php

namespace App\Policies;

use App\Models\SmartList;
use App\Models\User;

class SmartListPolicy
{
    /**
     * Determine whether the user can view or manage the smart list.
     */
    public function view(User $user, SmartList $smartList): bool
    {
        return $user->id === $smartList->user_id;
    }

    public function update(User $user, SmartList $smartList): bool
    {
        return $user->id === $smartList->user_id;
    }

    public function delete(User $user, SmartList $smartList): bool
    {
        return $user->id === $smartList->user_id;
    }
}