<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_confirmation',
        'order_shipped',
        'delivery_updates',
        'out_of_stock_alerts',
        'weekly_discounts',
        'exclusive_member_offers',
        'seasonal_campaigns',
        'cart_reminders',
        'payment_billing',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
    ];

    protected $casts = [
        'order_confirmation' => 'boolean',
        'order_shipped' => 'boolean',
        'delivery_updates' => 'boolean',
        'out_of_stock_alerts' => 'boolean',
        'weekly_discounts' => 'boolean',
        'exclusive_member_offers' => 'boolean',
        'seasonal_campaigns' => 'boolean',
        'cart_reminders' => 'boolean',
        'payment_billing' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}