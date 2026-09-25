<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send OTP via email
     */
    public function sendOtpEmail(string $email, string $otp, string $type): bool
    {
        try {
            $subject = $this->getEmailSubject($type);
            $message = $this->getEmailMessage($otp, $type);

            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP via SMS
     */
    public function sendOtpSms(string $phone, string $otp, string $type): bool
    {
        try {
            // This is a placeholder for SMS implementation
            // You would integrate with Twilio, Nexmo, or another SMS provider here
            
            $message = $this->getSmsMessage($otp, $type);
            
            // Example with Twilio (uncomment and configure when ready)
            /*
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
            
            $twilio->messages->create($phone, [
                'from' => config('services.twilio.phone'),
                'body' => $message
            ]);
            */

            // For now, just log the SMS (remove this in production)
            Log::info("SMS to {$phone}: {$message}");

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP SMS: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email subject based on OTP type
     */
    private function getEmailSubject(string $type): string
    {
        return match ($type) {
            'password_reset' => 'Password Reset OTP',
            'email_verification' => 'Email Verification OTP',
            'phone_verification' => 'Phone Verification OTP',
            default => 'Verification OTP',
        };
    }

    /**
     * Get email message based on OTP type
     */
    private function getEmailMessage(string $otp, string $type): string
    {
        $appName = config('app.name');
        $expiryMinutes = config('otp.expiry_minutes', 10);

        $typeText = match ($type) {
            'password_reset' => 'reset your password',
            'email_verification' => 'verify your email',
            'phone_verification' => 'verify your phone',
            default => 'verify your account',
        };

        return "Your {$appName} OTP to {$typeText} is: {$otp}\n\n"
            . "This code will expire in {$expiryMinutes} minutes.\n\n"
            . "If you did not request this code, please ignore this email.";
    }

    /**
     * Get SMS message based on OTP type
     */
    private function getSmsMessage(string $otp, string $type): string
    {
        $appName = config('app.name');
        
        return "{$appName}: Your verification code is {$otp}. Valid for " . config('otp.expiry_minutes', 10) . " minutes.";
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(string $email, string $username): bool
    {
        try {
            $appName = config('app.name');
            $message = "Welcome to {$appName}, {$username}!\n\n"
                . "Thank you for registering. We're excited to have you on board.\n\n"
                . "Start exploring our fresh meals and categories today!";

            Mail::raw($message, function ($mail) use ($email, $appName) {
                $mail->to($email)
                    ->subject("Welcome to {$appName}!");
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order confirmation notification
     */
    public function sendOrderConfirmation(User $user, $order)
    {
        if ($this->shouldSendNotification($user, 'order_confirmation')) {
            // $user->notify(new OrderConfirmationNotification($order));
        }
    }

    /**
     * Send order shipped notification
     */
    public function sendOrderShipped(User $user, $order, $trackingInfo)
    {
        if ($this->shouldSendNotification($user, 'order_shipped')) {
            // $user->notify(new OrderShippedNotification($order, $trackingInfo));
        }
    }

    /**
     * Send delivery update notification
     */
    public function sendDeliveryUpdate(User $user, $order, $update)
    {
        if ($this->shouldSendNotification($user, 'delivery_updates')) {
            // $user->notify(new DeliveryUpdateNotification($order, $update));
        }
    }

    /**
     * Send weekly discounts notification
     */
    public function sendWeeklyDiscounts(User $user, $discounts)
    {
        if ($this->shouldSendNotification($user, 'weekly_discounts')) {
            // $user->notify(new WeeklyDiscountsNotification($discounts));
        }
    }

    /**
     * Check if notification should be sent based on user preferences
     */
    private function shouldSendNotification(User $user, string $notificationType): bool
    {
        $settings = $user->notificationSettings;
        
        if (!$settings) {
            return false;
        }

        // Check if the specific notification type is enabled
        if (!$settings->{$notificationType}) {
            return false;
        }

        // Check if at least one channel is enabled
        return $settings->email_notifications || $settings->push_notifications || $settings->sms_notifications;
    }

    /**
     * Bulk send notifications to multiple users
     */
    public function bulkSendPromotion(array $userIds, $promotion, string $type = 'seasonal_campaigns')
    {
        $users = User::with('notificationSettings')
            ->whereIn('id', $userIds)
            ->get();

        foreach ($users as $user) {
            if ($this->shouldSendNotification($user, $type)) {
                // Send appropriate notification based on type
                // $user->notify(...);
            }
        }
    }
}
