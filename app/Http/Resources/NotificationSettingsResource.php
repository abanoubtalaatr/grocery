<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return self::defaultStructure();
        }

        return [
            'order_delivery_updates' => [
                'category' => 'Order & Delivery Updates',
                'enabled'  => $this->order_confirmation || $this->order_shipped || $this->delivery_updates || $this->out_of_stock_alerts,
                'settings' => [
                    'order_confirmation'  => (bool) $this->order_confirmation,
                    'order_shipped'       => (bool) $this->order_shipped,
                    'delivery_updates'    => (bool) $this->delivery_updates,
                    'out_of_stock_alerts' => (bool) $this->out_of_stock_alerts,
                ],
            ],
            'deals_promotions' => [
                'category' => 'Deals & Promotions',
                'enabled'  => $this->weekly_discounts || $this->exclusive_member_offers || $this->seasonal_campaigns,
                'settings' => [
                    'weekly_discounts'        => (bool) $this->weekly_discounts,
                    'exclusive_member_offers' => (bool) $this->exclusive_member_offers,
                    'seasonal_campaigns'     => (bool) $this->seasonal_campaigns,
                ],
            ],
            'account_reminders' => [
                'category' => 'Account & Reminders',
                'enabled'  => $this->cart_reminders || $this->payment_billing,
                'settings' => [
                    'cart_reminders'  => (bool) $this->cart_reminders,
                    'payment_billing' => (bool) $this->payment_billing,
                ],
            ],
            'channels' => [
                'category' => 'Notification Channels',
                'enabled'  => $this->email_notifications || $this->push_notifications || $this->sms_notifications,
                'settings' => [
                    'email_notifications' => (bool) $this->email_notifications,
                    'push_notifications'  => (bool) $this->push_notifications,
                    'sms_notifications'   => (bool) $this->sms_notifications,
                ],
            ],
        ];
    }

    public static function defaultStructure(): array
    {
        return [
            'order_delivery_updates' => [
                'category' => 'Order & Delivery Updates',
                'enabled'  => true,
                'settings' => [
                    'order_confirmation'  => true,
                    'order_shipped'       => true,
                    'delivery_updates'    => true,
                    'out_of_stock_alerts' => true,
                ],
            ],
            'deals_promotions' => [
                'category' => 'Deals & Promotions',
                'enabled'  => true,
                'settings' => [
                    'weekly_discounts'        => true,
                    'exclusive_member_offers' => true,
                    'seasonal_campaigns'     => true,
                ],
            ],
            'account_reminders' => [
                'category' => 'Account & Reminders',
                'enabled'  => true,
                'settings' => [
                    'cart_reminders'  => true,
                    'payment_billing' => true,
                ],
            ],
            'channels' => [
                'category' => 'Notification Channels',
                'enabled'  => true,
                'settings' => [
                    'email_notifications' => true,
                    'push_notifications'  => true,
                    'sms_notifications'   => false,
                ],
            ],
        ];
    }
}