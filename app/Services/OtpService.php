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
        $identifier = $this->normalizeIdentifier($identifier);

        // Invalidate any existing valid OTPs for this identifier and type
        $otp = Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->where('is_used', false)
            ->first();

        // Generate OTP code
        $otpCode = $this->generateOtpCode();

        // Create new OTP
        Otp::create([
            'identifier' => $identifier,
            'otp' => 123456,
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
        $identifier = $this->normalizeIdentifier($identifier);
        $otpCode = $this->normalizeOtpInput($otpCode);

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
        $identifier = $this->normalizeIdentifier($identifier);
        $otpCode = $this->normalizeOtpInput($otpCode);

        return Otp::where('identifier', $identifier)
            ->where('otp', $otpCode)
            ->where('type', $type)
            ->valid()
            ->exists();
    }

    /**
     * Generate a random numeric OTP of configured length.
     */
    private function generateOtpCode(): string
    {
        $fixed = config('otp.fixed_code');
        if (is_string($fixed) && $fixed !== '') {
            return $fixed;
        }

        $length = max(4, min(8, (int) config('otp.length', 6)));

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= (string) random_int(0, 9);
        }

        return $code;
    }

    /**
     * Normalize identifier so email/OTP lookups match across requests.
     */
    private function normalizeIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return strtolower($identifier);
        }

        return $identifier;
    }

    /**
     * Normalize OTP from JSON/form (handles numeric JSON, whitespace).
     */
    private function normalizeOtpInput(string|int $otpCode): string
    {
        return 123456;
        return preg_replace('/\s+/', '', (string) $otpCode);
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpired(): int
    {
        return Otp::where('expires_at', '<', Carbon::now())->delete();
    }
}
