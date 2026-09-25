<?php

namespace App\Actions\Notification;

use App\Models\User;
use App\Models\UserNotificationSetting;

class UpdateNotificationCategoryAction
{
    private const CATEGORIES = [
        'order_delivery'   => ['order_confirmation', 'order_shipped', 'delivery_updates', 'out_of_stock_alerts'],
        'deals_promotions' => ['weekly_discounts', 'exclusive_member_offers', 'seasonal_campaigns'],
        'account_reminders'=> ['cart_reminders', 'payment_billing'],
        'channels'         => ['email_notifications', 'push_notifications', 'sms_notifications'],
    ];

    public function execute(User $user, string $category, bool $enabled): ?UserNotificationSetting
    {
        $fields = self::CATEGORIES[$category] ?? [];

        if (empty($fields)) {
            return null;
        }

        $settings = $user->initializeNotificationSettings();

        $updateData = [];
        foreach ($fields as $field) {
            $updateData[$field] = $enabled;
        }

        $settings->update($updateData);

        return $settings->fresh();
    }
}