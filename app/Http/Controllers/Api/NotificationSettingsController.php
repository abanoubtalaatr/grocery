<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsController extends Controller
{
    /**
     * Get user notification settings
     */
    public function index()
    {
        $user = Auth::user();
        $settings = $user->initializeNotificationSettings();

        return response()->json([
            'success' => true,
            'data' => $this->formatSettings($settings)
        ]);
    }

    /**
     * Update notification settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Order & Delivery Updates
            'order_confirmation' => 'sometimes|boolean',
            'order_shipped' => 'sometimes|boolean',
            'delivery_updates' => 'sometimes|boolean',
            'out_of_stock_alerts' => 'sometimes|boolean',
            
            // Deals & Promotions
            'weekly_discounts' => 'sometimes|boolean',
            'exclusive_member_offers' => 'sometimes|boolean',
            'seasonal_campaigns' => 'sometimes|boolean',
            
            // Account & Reminders
            'cart_reminders' => 'sometimes|boolean',
            'payment_billing' => 'sometimes|boolean',
            
            // Channels
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $settings = $user->initializeNotificationSettings();
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
            'data' => $this->formatSettings($settings->fresh())
        ]);
    }

    /**
     * Update specific category settings
     */
    public function updateCategory(Request $request, string $category)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = Auth::user();
        $settings = $user->initializeNotificationSettings();

        $fields = $this->getCategoryFields($category);
        
        if (empty($fields)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid category'
            ], 400);
        }

        $updateData = [];
        foreach ($fields as $field) {
            $updateData[$field] = $validated['enabled'];
        }

        $settings->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "{$category} notifications updated successfully",
            'data' => $this->formatSettings($settings->fresh())
        ]);
    }

    /**
     * Format settings for response
     */
    private function formatSettings(UserNotificationSetting $settings)
    {
        return [
            'order_delivery_updates' => [
                'category' => 'Order & Delivery Updates',
                'enabled' => $settings->order_confirmation || $settings->order_shipped || $settings->delivery_updates || $settings->out_of_stock_alerts,
                'settings' => [
                    'order_confirmation' => $settings->order_confirmation,
                    'order_shipped' => $settings->order_shipped,
                    'delivery_updates' => $settings->delivery_updates,
                    'out_of_stock_alerts' => $settings->out_of_stock_alerts,
                ]
            ],
            'deals_promotions' => [
                'category' => 'Deals & Promotions',
                'enabled' => $settings->weekly_discounts || $settings->exclusive_member_offers || $settings->seasonal_campaigns,
                'settings' => [
                    'weekly_discounts' => $settings->weekly_discounts,
                    'exclusive_member_offers' => $settings->exclusive_member_offers,
                    'seasonal_campaigns' => $settings->seasonal_campaigns,
                ]
            ],
            'account_reminders' => [
                'category' => 'Account & Reminders',
                'enabled' => $settings->cart_reminders || $settings->payment_billing,
                'settings' => [
                    'cart_reminders' => $settings->cart_reminders,
                    'payment_billing' => $settings->payment_billing,
                ]
            ],
            'channels' => [
                'category' => 'Notification Channels',
                'enabled' => $settings->email_notifications || $settings->push_notifications || $settings->sms_notifications,
                'settings' => [
                    'email_notifications' => $settings->email_notifications,
                    'push_notifications' => $settings->push_notifications,
                    'sms_notifications' => $settings->sms_notifications,
                ]
            ]
        ];
    }

    /**
     * Get fields for a category
     */
    private function getCategoryFields(string $category): array
    {
        $categories = [
            'order_delivery' => ['order_confirmation', 'order_shipped', 'delivery_updates', 'out_of_stock_alerts'],
            'deals_promotions' => ['weekly_discounts', 'exclusive_member_offers', 'seasonal_campaigns'],
            'account_reminders' => ['cart_reminders', 'payment_billing'],
            'channels' => ['email_notifications', 'push_notifications', 'sms_notifications'],
        ];

        return $categories[$category] ?? [];
    }
}