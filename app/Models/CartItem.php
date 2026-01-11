<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'meal_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($cartItem) {
            $cartItem->calculateSubtotal();
        });

        static::saved(function ($cartItem) {
            $cartItem->cart->calculateTotals();
        });

        static::deleted(function ($cartItem) {
            $cartItem->cart->calculateTotals();
        });
    }

    /**
     * Get the cart that owns the cart item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the meal that owns the cart item.
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Calculate subtotal for this cart item.
     */
    public function calculateSubtotal(): void
    {
        $this->subtotal = ($this->unit_price * $this->quantity) - $this->discount_amount;
    }

    /**
     * Update quantity.
     */
    public function updateQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->save();
    }

    /**
     * Increment quantity.
     */
    public function incrementQuantity(int $amount = 1): void
    {
        $this->quantity += $amount;
        $this->save();
    }

    /**
     * Decrement quantity.
     */
    public function decrementQuantity(int $amount = 1): void
    {
        $this->quantity = max(1, $this->quantity - $amount);
        $this->save();
    }
}
