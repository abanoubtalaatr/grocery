<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfileImageAction
{
    public function execute(User $user, UploadedFile $image): User
    {
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $image->store('profile-images', 'public');
        $user->update(['profile_image' => $path]);

        return $user;
    }
}