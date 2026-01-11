<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook',
        'linkedin',
        'instagram',
        'twitter',
        'email',
        'phone',
        'address',
        'logo',
        'favicon',
        'site_name',
        'site_description',
        'copyright_text',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get single instance (singleton pattern)
     */
    public static function getSettings()
    {
        $settings = self::first();
        if (!$settings) {
            $settings = self::create();
        }
        return $settings;
    }
}