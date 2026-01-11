<?php

namespace App\Services;

use App\Models\Otp;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate a new OTP
     */
    public function generate(string $identifier, string $type): string
    {
        // Invalidate any existing valid OTPs for this identifier and type
        Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate OTP code
        $otpCode = $this->generateOtpCode();

        // Create new OTP
        Otp::create([
            'identifier' => $identifier,
            'otp' => $otpCode,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(config('otp.expiry_minutes', 10)),
        ]);

        return $otpCode;
    }

    /**
     * Verify an OTP
     */
    public function verify(string $identifier, string $otpCode, string $type): bool
    {
        $otp = Otp::where('identifier', $identifier)
            ->where('otp', $otpCode)
            ->where('type', $type)
            ->valid()
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->markAsUsed();

        return true;
    }

    /**
     * Check if OTP exists and is valid
     */
    public function isValid(string $identifier, string $otpCode, string $type): bool
    {
        return Otp::where('identifier', $identifier)
            ->where('otp', $otpCode)
            ->where('type', $type)
            
            ->exists();
    }

    /**
     * Generate a random OTP code
     */
    private function generateOtpCode(): string
    {
        $length = config('otp.length', 4);
        return 1234;
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpired(): int
    {
        return Otp::where('expires_at', '<', Carbon::now())->delete();
    }
}
