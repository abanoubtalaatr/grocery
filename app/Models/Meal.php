<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Meal extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'subcategory_id',
        'title',
        'slug',
        'description',
        'image',
        'offer_title',
        'price',
        'discount_price',
        'rating',
        'rating_count',
        'size',
        'expiry_date',
        'includes',
        'how_to_use',
        'features',
        'brand',
        'stock_quantity',
        'sold_count',
        'is_featured',
        'is_available',
        'is_hot',
        'available_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'rating_count' => 'integer',
        'stock_quantity' => 'integer',
        'sold_count' => 'integer',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'is_hot' => 'boolean',
        'available_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the category that owns the meal.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the subcategory that owns the meal.
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get the cart items for the meal.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the favorites for the meal.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the users who favorited this meal.
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Scope a query to only include today's meals.
     */
    public function scopeToday($query)
    {
        return $query->where('available_date', Carbon::today())
            ->orWhere(function ($q) {
                $q->whereNull('available_date')
                    ->where('is_featured', true);
            });
    }

    /**
     * Scope a query to only include featured meals.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: only Ready-to-eat / Hot meals (for Hot Meals API).
     */
    public function scopeHot($query)
    {
        return $query->where('is_hot', true);
    }

    /**
     * Scope a query to only include meals with active discounts (today's deals).
     * Either has discount_price set or has offer_title (percentage discount can be calculated).
     */
    public function scopeWithActiveDiscount($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('discount_price')
                ->orWhereNotNull('offer_title')
                ->where('offer_title', '!=', '');
        });
    }

    /**
     * Scope a query to only include available meals.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Get the image URL attribute.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // If it's already a full URL, return as is
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        // Otherwise, generate URL from storage
        return asset('storage/' . $this->image);
    }

    /**
     * Check if meal has an offer
     */
    public function hasOffer(): bool
    {
        return !empty($this->offer_title) || $this->getRawDiscountPrice() !== null;
    }

    /**
     * Raw discount_price from database (nullable).
     */
    public function getRawDiscountPrice(): ?float
    {
        $value = $this->attributes['discount_price'] ?? null;
        return $value !== null ? (float) $value : null;
    }

    /**
     * Try to derive discount price from offer_title (e.g. "20% OFF" -> price * 0.8).
     * Returns null if no percentage pattern is found.
     */
    public function calculateDiscountPriceFromOfferTitle(): ?float
    {
        if (empty($this->offer_title)) {
            return null;
        }
        if (preg_match('/(\d+)\s*%\s*(?:off)?/i', (string) $this->offer_title, $m)) {
            $percent = (int) $m[1];
            if ($percent > 0 && $percent < 100) {
                $price = (float) $this->price;
                return round($price * (1 - $percent / 100), 2);
            }
        }
        return null;
    }

    /**
     * Discount price for API: stored value or calculated from offer_title when offer is set.
     * Use this when returning meal data so meals with active offers always have a valid discount price.
     */
    public function getResolvedDiscountPriceAttribute(): ?float
    {
        $stored = $this->getRawDiscountPrice();
        if ($stored !== null) {
            return $stored;
        }
        return $this->calculateDiscountPriceFromOfferTitle();
    }

    /**
     * Get the final price (discount price if available, otherwise regular price)
     */
    public function getFinalPriceAttribute(): float
    {
        $discount = $this->resolved_discount_price;
        return $discount !== null ? $discount : (float) $this->price;
    }

    /**
     * Price fields as numeric values for API responses (avoids string quotes in JSON).
     */
    public function getApiPriceAttributes(): array
    {
        return [
            'price' => (float) $this->price,
            'discount_price' => $this->resolved_discount_price !== null ? (float) $this->resolved_discount_price : null,
            'final_price' => (float) $this->final_price,
        ];
    }

    /**
     * Check if the product is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if the product has expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return Carbon::parse($this->expiry_date)->isPast();
    }

    /**
     * Get days until expiry
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return Carbon::now()->diffInDays(Carbon::parse($this->expiry_date), false);
    }

    /**
     * Scope a query to only include in-stock meals.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to only include out-of-stock meals.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meal) {
            if (!$meal->slug) {
                $meal->slug = Str::slug($meal->title);
            }
        });

        static::updating(function ($meal) {
            if ($meal->isDirty('title') && !$meal->isDirty('slug')) {
                $meal->slug = Str::slug($meal->title);
            }
        });
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
