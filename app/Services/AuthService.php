<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected OtpService $otpService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Create user
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'agree_terms' => $data['agree_terms'],
        ]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Send welcome notification
        if ($user->email) {
            $this->notificationService->sendWelcomeEmail($user->email, $user->username);
        }

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user
     */
    public function login(string $identifier, string $password): array
    {
        // Find user by email or phone
        $user = User::findByIdentifier($identifier);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Your account has been deactivated.'],
            ]);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): bool
    {
        // Revoke all tokens for the user
        $user->tokens()->delete();

        return true;
    }

    /**
     * Initiate forgot password process
     */
    public function forgotPassword(string $identifier): bool
    {
        // Find user
        $user = User::findByIdentifier($identifier);

        if (!$user) {
            // Don't reveal if user exists or not for security
            return true;
        }

        // Generate OTP
        $otp = $this->otpService->generate($identifier, 'password_reset');

        // Send OTP via email or SMS
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $this->notificationService->sendOtpEmail($identifier, $otp, 'password_reset');
        } else {
            $this->notificationService->sendOtpSms($identifier, $otp, 'password_reset');
        }

        return true;
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        return $this->otpService->verify($identifier, $otp, 'password_reset');
    }

    /**
     * Reset password
     */
    public function resetPassword(string $identifier, string $otp, string $newPassword): bool
    {
        // Verify OTP
        if (!$this->otpService->isValid($identifier, $otp, 'password_reset')) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        // Find user
        $user = User::findByIdentifier($identifier);

        if (!$user) {
            throw ValidationException::withMessages([
                'identifier' => ['User not found.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Mark OTP as used
        $this->otpService->verify($identifier, $otp, 'password_reset');

        // Revoke all existing tokens
        $user->tokens()->delete();

        return true;
    }

    /**
     * Delete user account
     */
    public function deleteAccount(User $user): bool
    {
        $user->delete();

        // Revoke all tokens for the user
        $user->tokens()->delete();

        return true;
    }
}
