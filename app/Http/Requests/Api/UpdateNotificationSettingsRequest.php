<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationSettingsRequest extends FormRequest
{
    /**
     * Only allow true, false, 0, or 1 for boolean fields.
     * Rejects invalid values (e.g. 4, "yes", "on") so they are not cast to true.
     */
    private const BOOLEAN_VALUES = [true, false, 0, 1];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $booleanRule = ['sometimes', Rule::in(self::BOOLEAN_VALUES)];

        return [
            'order_confirmation' => $booleanRule,
            'order_shipped' => $booleanRule,
            'delivery_updates' => $booleanRule,
            'out_of_stock_alerts' => $booleanRule,
            'weekly_discounts' => $booleanRule,
            'exclusive_member_offers' => $booleanRule,
            'seasonal_campaigns' => $booleanRule,
            'cart_reminders' => $booleanRule,
            'payment_billing' => $booleanRule,
            'email_notifications' => $booleanRule,
            'push_notifications' => $booleanRule,
            'sms_notifications' => $booleanRule,
        ];
    }

    public function messages(): array
    {
        $msg = 'The :attribute field must be true, false, 0, or 1.';

        return [
            'order_confirmation.in' => $msg,
            'order_shipped.in' => $msg,
            'delivery_updates.in' => $msg,
            'out_of_stock_alerts.in' => $msg,
            'weekly_discounts.in' => $msg,
            'exclusive_member_offers.in' => $msg,
            'seasonal_campaigns.in' => $msg,
            'cart_reminders.in' => $msg,
            'payment_billing.in' => $msg,
            'email_notifications.in' => $msg,
            'push_notifications.in' => $msg,
            'sms_notifications.in' => $msg,
        ];
    }

    /**
     * Normalize validated data to strict booleans for storage.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if ($key !== null) {
            return is_bool($data) ? $data : (bool) $data;
        }

        foreach (array_keys($this->rules()) as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = (bool) $data[$field];
            }
        }

        return $data;
    }
}
