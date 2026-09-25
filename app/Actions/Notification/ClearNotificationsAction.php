<?php

namespace App\Actions\Notification;

use App\Models\User;

class ClearNotificationsAction
{
    /**
     * Delete all notifications or read notifications for the given user.
     *
     * @param User $user
     * @param bool $onlyRead If true, deletes only read notifications. If false, deletes all notifications.
     * @return int The number of deleted notifications.
     */
    public function execute(User $user, bool $onlyRead = false): int
    {
        $query = $user->notifications();

        if ($onlyRead) {
            $query->whereNotNull('read_at');
        }

        return $query->delete();
    }
}