<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteProfileImageAction
{
    public function execute(User $user): bool
    {
        if (!$user->profile_image) {
            return false;
        }

        if (Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update(['profile_image' => null]);

        return true;
    }
}