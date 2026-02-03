<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_number',
        'payment_method',
        'payment_method_id',
        'delivery_type',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'placed_at',
        'processing_at',
        'shipping_at',
        'out_for_delivery_at',
        'delivered_at',
        'cancelled_at',
        'estimated_delivery_time',
        'schedule_delivery',
        'delivery_speed',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'placed_at' => 'datetime',
        'processing_at' => 'datetime',
        'shipping_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'estimated_delivery_time' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
        } while (static::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address for the order.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the order items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the status position (1, 2, 3, 4, 5).
     */
    public function getStatusPositionAttribute(): int
    {
        return match($this->status) {
            'placed' => 1,
            'processing' => 2,
            'shipping' => 3,
            'out_for_delivery' => 4,
            'delivered' => 5,
            'cancelled' => 0,
            default => 0,
        };
    }

    /**
     * Get status description.
     */
    public function getStatusDescriptionAttribute(): string
    {
        return match($this->status) {
            'placed' => 'Order placed',
            'processing' => 'Processing',
            'shipping' => 'Shipping',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Scope a query to only include placed orders.
     */
    public function scopePlaced($query)
    {
        return $query->where('status', 'placed');
    }

    /**
     * Scope a query to only include pending orders (for backward compatibility).
     */
    public function scopePending($query)
    {
        return $query->where('status', 'placed');
    }

    /**
     * Scope a query to only include active orders (not cancelled or delivered).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'delivered']);
    }
    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }
    public function specialNote(): BelongsTo
    {
        return $this->belongsTo(SpecialNote::class);
    }

    public function getSpecialNoteAttribute(): ?string
    {
        // Check if the main order 'notes' field exists and return it if present.
        if (!empty($this->notes)) {
            return $this->notes;
        }

        // Check if there is at least one related OrderNote
        $orderNote = $this->notes()->first();
        if ($orderNote) {
            // If the OrderNote has a related SpecialNote, return its name
            if ($orderNote->specialNote) {
                return $orderNote->specialNote->name;
            }
            // Otherwise return the notes field from OrderNote if present
            if (!empty($orderNote->notes)) {
                return $orderNote->notes;
            }
        }

        // Nothing found
        return null;
    }
}
