<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected OtpService $otpService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'agree_terms' => $data['agree_terms'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        if ($user->email) {
            $this->notificationService->sendWelcomeEmail(
                $user->email,
                $user->username
            );
        }

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user.
     */
    public function login(string $identifier, string $password): array
    {
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'login' => ['Unable to sign in. Please try again.'],
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password you entered is incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user.
     */
    public function logout(User $user): bool
    {
        $user->tokens()->delete();

        return true;
    }

    /**
     * Initiate forgot password process.
     */
    public function forgotPassword(string $identifier): bool
    {
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['User not found.'],
            ]);
        }

        $otp = $this->otpService->generate(
            $identifier,
            'password_reset'
        );

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $this->notificationService->sendOtpEmail(
                $identifier,
                $otp,
                'password_reset'
            );
        } else {
            $this->notificationService->sendOtpSms(
                $identifier,
                $otp,
                'password_reset'
            );
        }

        return true;
    }

    /**
     * Verify OTP.
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        return $this->otpService->verify(
            $identifier,
            $otp,
            'password_reset'
        );
    }

    /**
     * Reset password.
     */
    public function resetPassword(
        string $identifier,
        string $otp,
        string $newPassword
    ): bool {
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['User not found.'],
            ]);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        $this->otpService->verify(
            $identifier,
            $otp,
            'password_reset'
        );

        $user->tokens()->delete();

        return true;
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(User $user): bool
    {
        $user->delete();
        $user->tokens()->delete();

        return true;
    }

    /**
     * Change password for authenticated user.
     */
    public function changePassword(User $user, string $password): bool
    {
        $user->update([
            'password' => $password,
        ]);

        return true;
    }
}