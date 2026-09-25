<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'full_name',
        'phone',
        'country_code',
        'street_address',
        'building_number',
        'floor',
        'apartment',
        'landmark',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
        'is_default',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When setting an address as default, unset all other defaults for this user
        static::saving(function ($address) {
            if ($address->is_default) {
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full address as a string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->building_number ? "Building {$this->building_number}" : null,
            $this->floor ? "Floor {$this->floor}" : null,
            $this->apartment ? "Apt {$this->apartment}" : null,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted phone. Avoids duplicating country code if phone already includes it.
     */
    public function getFormattedPhoneAttribute(): string
    {
        $phone = trim((string) $this->phone);
        $code = trim((string) ($this->country_code ?? ''));
        if ($phone === '') {
            return $code;
        }
        // If phone already starts with + or with this country code, do not prepend again
        if (str_starts_with($phone, '+') || ($code !== '' && str_starts_with($phone, $code))) {
            return $phone;
        }
        return $code !== '' ? $code . $phone : $phone;
    }
}
