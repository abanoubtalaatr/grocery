<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Models\Meal;
use App\Services\ShippingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;
    
    /**
     * Get user's cart
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        $deliveryType = $request->query('delivery_type');
        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingService = app(ShippingService::class);
            $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $totalWithShipping = (float) $cart->total + $shippingFee;
        } else {
            $shippingFee = null;
            $totalWithShipping = null;
        }

        return $this->success('Cart retrieved successfully', 200, 
            CartResource::make($cart)->withShipping($shippingFee, $totalWithShipping)
        );
    }

   /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $validated = $request->validate([
            'meal_id' => ['required', 'exists:meals,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxPerProduct],
        ], [
            'quantity.max' => "Maximum {$maxPerProduct} units per product allowed.",
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $meal = Meal::findOrFail($validated['meal_id']);

        // Check if meal is available
        if (!$meal->is_available) {
            return $this->error('This meal is currently unavailable', 400);
        }

        // Check if meal is in stock
        if (!$meal->isInStock()) {
            return $this->error('This meal is out of stock', 400);
        }

        // Check if meal has expired
        // if ($meal->isExpired()) {
        //     return $this->error('This meal has expired', 400);
        // }

        // Check stock quantity
        if ($meal->stock_quantity < $validated['quantity']) {
            return $this->error("Only {$meal->stock_quantity} items available in stock", 400);
        }

        // Check if item already exists in cart
        $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

        if ($cartItem) {
            // Update quantity (enforce max per product per user)
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            $effectiveMax = min($maxPerProduct, $meal->stock_quantity);
            if ($newQuantity > $effectiveMax) {
                return $this->error("Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.", 400);
            }
            if ($meal->stock_quantity < $newQuantity) {
                return $this->error("Only {$meal->stock_quantity} items available in stock", 400);
            }

            $cartItem->update([
                'quantity' => $newQuantity,
            ]);
        } else {
            // Create new cart item
            $discountAmount = 0;
            if ($meal->resolved_discount_price) {
                $discountAmount = ($meal->price - $meal->resolved_discount_price) * $validated['quantity'];
            }

            $cartItem = $cart->items()->create([
                'meal_id' => $meal->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $meal->final_price,
                'discount_amount' => $discountAmount,
                'subtotal' => $meal->final_price * $validated['quantity'],
            ]);
        }

        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $this->success('Item added to cart successfully', 200, CartResource::make($cart));
    }

   /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, string $itemId): JsonResponse
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxPerProduct],
            ], 
        ['quantity.max' => "Maximum {$maxPerProduct} units per product allowed.",
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();
        
        $cartItem = $cart->items()->findOrFail($itemId);
        $meal = $cartItem->meal;

        // Check stock quantity
        if ($meal->stock_quantity < $validated['quantity']) {
            return $this->error("Only {$meal->stock_quantity} items available in stock", 400);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
        ]);

        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $this->success('Cart item updated successfully', 200, CartResource::make($cart));
    }

   /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        
        $cartItem = $cart->items()->findOrFail($itemId);

        $cartItem->delete();

        $cart->calculateTotals();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        return $this->success('Item removed from cart successfully', 200, CartResource::make($cart));
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
     
            $user = $request->user();
            $cart = $user->getOrCreateCart();

          
            $cart->items()->delete();
            $cart->calculateTotals();

    
            return $this->success('Cart cleared successfully', 200, CartResource::make($cart));
           
       
    }
}
