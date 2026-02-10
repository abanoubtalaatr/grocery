<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const STORE_STATUS_OPEN = 'open';
    public const STORE_STATUS_CLOSED = 'closed';
    public const STORE_STATUS_MAINTENANCE = 'maintenance';

    protected $fillable = [
        'facebook',
        'linkedin',
        'instagram',
        'twitter',
        'whatsapp',
        'tiktok',
        'snapchat',
        'youtube',
        'email',
        'phone',
        'support_email',
        'support_phone',
        'address',
        'store_address',
        'logo',
        'favicon',
        'site_name',
        'site_description',
        'copyright_text',
        'store_status',
        'maintenance_mode',
        'store_hours',
        'currency_code',
        'currency_symbol',
        'tax_rate',
        'payment_methods',
        'shipping_note',
        'shipping_fee',
        'free_shipping_min_order',
        'locale',
        'timezone',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'tax_rate' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'free_shipping_min_order' => 'decimal:2',
        'payment_methods' => 'array',
    ];

    /**
     * Get single instance (singleton pattern).
     */
    public static function getSettings(): self
    {
        $settings = self::first();
        if (!$settings) {
            $settings = self::create([]);
        }
        return $settings;
    }

    public function isStoreOpen(): bool
    {
        return $this->store_status === self::STORE_STATUS_OPEN && !$this->maintenance_mode;
    }
}
