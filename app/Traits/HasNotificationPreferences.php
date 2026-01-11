<?php

namespace App\Traits;

trait HasNotificationPreferences
{
    /**
     * Check if a specific notification type is enabled
     */
    public function isNotificationEnabled(string $type): bool
    {
        $settings = $this->notificationSettings;
        
        if (!$settings) {
            return false;
        }

        return $settings->{$type} ?? false;
    }

    /**
     * Get enabled notification channels
     */
    public function getEnabledChannels(): array
    {
        $settings = $this->notificationSettings;
        $channels = [];

        if (!$settings) {
            return $channels;
        }

        if ($settings->email_notifications) {
            $channels[] = 'mail';
        }

        if ($settings->push_notifications) {
            $channels[] = 'database';
        }

        if ($settings->sms_notifications) {
            $channels[] = 'sms'; // Requires SMS channel setup
        }

        return $channels;
    }

    /**
     * Disable all notifications
     */
    public function disableAllNotifications()
    {
        $settings = $this->notificationSettings;
        
        if ($settings) {
            $settings->update([
                'order_confirmation' => false,
                'order_shipped' => false,
                'delivery_updates' => false,
                'out_of_stock_alerts' => false,
                'weekly_discounts' => false,
                'exclusive_member_offers' => false,
                'seasonal_campaigns' => false,
                'cart_reminders' => false,
                'payment_billing' => false,
            ]);
        }
    }

    /**
     * Enable all notifications
     */
    public function enableAllNotifications()
    {
        $settings = $this->initializeNotificationSettings();
        
        $settings->update([
            'order_confirmation' => true,
            'order_shipped' => true,
            'delivery_updates' => true,
            'out_of_stock_alerts' => true,
            'weekly_discounts' => true,
            'exclusive_member_offers' => true,
            'seasonal_campaigns' => true,
            'cart_reminders' => true,
            'payment_billing' => true,
        ]);
    }
}