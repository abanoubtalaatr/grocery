<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function ($user) {
            // Use firstOrCreate to avoid duplicate entries
            $user->notificationSettings()->firstOrCreate(
                ['user_id' => $user->id]
            );
        });
    }
}