<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user 
                            {--username= : Username for the admin user}
                            {--email= : Email for the admin user}
                            {--password= : Password for the admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user for the Filament dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->option('username') ?: $this->ask('Username');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password');

        // Validate inputs
        $validator = Validator::make([
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ], [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  - ' . $error);
            }
            return 1;
        }

        // Create admin user
        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
            'is_active' => true,
            'email_verified' => true,
            'phone_verified' => false,
            'agree_terms' => true,
            'email_verified_at' => now(),
            'country_code' => '+20',
        ]);

        $this->info("✅ Admin user created successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Username', $user->username],
                ['Email', $user->email],
                ['Admin', $user->is_admin ? 'Yes' : 'No'],
                ['Active', $user->is_active ? 'Yes' : 'No'],
            ]
        );

        $this->info("\nYou can now login to the admin dashboard at: /admin");
        $this->info("Login with: {$username} / {$password}");

        return 0;
    }
}
