<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasNotificationPreferences;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasName
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasNotificationPreferences;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'firstname',
        'lastname',
        'email',
        'phone',
        'country_code',
        'profile_image',
        'password',
        'email_verified',
        'phone_verified',
        'email_verified_at',
        'phone_verified_at',
        'agree_terms',
        'is_active',
        'is_admin',
        'stripe_customer_id',
        'google_id',
        'avatar',
        'loyalty_points',
        'store_credits',
        'preferred_languages',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'agree_terms' => 'boolean',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'loyalty_points' => 'integer',
        'store_credits' => 'decimal:2',
        'preferred_languages' => 'array',
    ];

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmin($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): ?string
    {
        if ($this->firstname && $this->lastname) {
            return trim($this->firstname . ' ' . $this->lastname);
        }
        return $this->firstname ?? $this->lastname ?? null;
    }

    /**
     * Get the user's name for Filament.
     * Filament expects a 'name' attribute, but we use 'username'.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name ?? $this->username ?? $this->email ?? 'User';
    }

    /**
     * Get the Filament name for the user.
     * This is required by the HasName contract.
     */
    public function getFilamentName(): string
    {
        return $this->username ?? $this->email ?? 'User';
    }

    /**
     * Find user by email or phone
     */
    public static function findByIdentifier(string $identifier): ?self
    {
        return static::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();
    }

    /**
     * Check if the user has verified their email
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified;
    }

    /**
     * Check if the user has verified their phone
     */
    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified;
    }

    /**
     * Get the user's carts.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the user's active cart.
     */
    public function activeCart()
    {
        return $this->hasOne(Cart::class)->where('status', 'active');
    }

    /**
     * Get or create the user's active cart.
     */
    public function getOrCreateCart(): Cart
    {
        $cart = $this->activeCart()->first();
        
        if (!$cart) {
            $cart = $this->carts()->create([
                'status' => 'active',
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
            ]);
        }
        
        return $cart;
    }

    /**
     * Get the user's favorites.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the user's favorite meals.
     */
    public function favoriteMeals()
    {
        return $this->belongsToMany(Meal::class, 'favorites')->withTimestamps();
    }

    /**
     * Check if user has favorited a meal.
     */
    public function hasFavorited(int $mealId): bool
    {
        return $this->favorites()->where('meal_id', $mealId)->exists();
    }

    /**
     * Get the user's addresses.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the user's todo list items.
     */
    public function todoLists()
    {
        return $this->hasMany(TodoList::class);
    }

    /**
     * Get the user's default address.
     */
    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    /**
     * Get profile image URL.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($this->profile_image, FILTER_VALIDATE_URL)) {
            return $this->profile_image;
        }

        // Otherwise, generate URL from storage
        return asset('storage/' . $this->profile_image);
    }

    public function notificationSettings()
    {
        return $this->hasOne(UserNotificationSetting::class);
    }

    public function initializeNotificationSettings()
    {
        
        if (!$this->notificationSettings) {
            $this->notificationSettings()->create();
        }
        return $this->notificationSettings;
    }

    /**
     * Get the user's orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
