<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Cart;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderItem;
use App\Models\OrderNote;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{

    public function show(Request $request, Order $order)
    {
        $order = $order->load(['items.meal', 'address']);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => $this->formatOrder($order),
        ]);
    }
    
    /**
     * Create a new order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Get user's active cart
            $cart = $user->activeCart()->with('items.meal')->first();

            if (!$cart || $cart->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Please add items to your cart before placing an order.',
                ], 400);
            }

            // Validate and process items from cart
            $itemsResult = $this->validateAndProcessCartItems($cart->items);
            if (!$itemsResult['success']) {
                return response()->json($itemsResult['response'], 400);
            }

            $items = $itemsResult['items'];

            // Calculate totals (use cart totals to match what user sees)
            $cart->calculateTotals();
            $totals = [
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'discount' => $cart->discount,
                'total' => $cart->total,
            ];
            $total = $cart->total;

            // Validate amount matches cart total
            // if (abs($total - $validated['amount']) > 0.01) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Amount mismatch. Please recalculate your order.',
            //         'calculated_total' => $total,
            //         'provided_amount' => $validated['amount'],
            //     ], 400);
            // }

            DB::beginTransaction();

            // Process payment if card payment
            $paymentResult = $this->processPayment($user, $validated, $total);
            if (!$paymentResult['success']) {
                DB::rollBack();
                return response()->json($paymentResult['response'], 400);
            }

            // Create order
            $order = $this->createOrder($user, $validated, $totals['subtotal'], $totals);

            // Create order items and update stock
            $this->createOrderItems($order, $items);

            // Clear user's active cart
            $this->clearUserCart($user);

            if($validated['special_note_id']) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => $validated['special_note_id'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
            if($validated['notes']) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => null,
                    'notes' => $validated['notes'],
                ]);
            }
            DB::commit();

            $order->load(['items.meal', 'address']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $this->formatOrder($order),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate and process order items from cart.
     */
    private function validateAndProcessCartItems($cartItems): array
    {
        $items = [];
        $subtotal = 0;

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (!$meal) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'One or more items in your cart are no longer available.',
                    ],
                ];
            }

            if (!$meal->is_available) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Meal '{$meal->title}' is currently unavailable",
                    ],
                ];
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Only {$meal->stock_quantity} items available for '{$meal->title}'",
                    ],
                ];
            }

            // Use cart item pricing (already calculated)
            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];

            $subtotal += $cartItem->subtotal;
        }

        return [
            'success' => true,
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Calculate order totals.
     */
    private function calculateTotals(float $subtotal): array
    {
        $tax = $subtotal * 0.1; // 10% tax
        $discount = 0;
        $total = $subtotal + $tax - $discount;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    /**
     * Process payment for card orders.
     */
    private function processPayment($user, array $validated, float $total): array
    {
        if ($validated['payment_method'] !== 'card') {
            return ['success' => true];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if (!$user->stripe_customer_id) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Stripe customer not found. Please add a payment method first.',
                ],
            ];
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($total * 100),
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $validated['payment_method_id'],
                'off_session' => true,
                'confirm' => true,
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'Payment failed: ' . $paymentIntent->status,
                    ],
                ];
            }

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Payment processing failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Create order record.
     */
    private function createOrder($user, array $validated, float $subtotal, array $totals): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'address_id' => $validated['delivery_type'] === 'delivery' ? $validated['address_id'] : null,
            'payment_method' => $validated['payment_method'],
            'payment_method_id' => $validated['payment_method_id'] ?? null,
            'delivery_type' => $validated['delivery_type'],
            'status' => 'placed',
            'subtotal' => $subtotal,
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'notes' => $validated['notes'] ?? null,
            'placed_at' => now(),
        ]);
    }

    /**
     * Create order items and update stock.
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'meal_id' => $item['meal']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'subtotal' => $item['subtotal'],
            ]);

            $item['meal']->decrement('stock_quantity', $item['quantity']);
        }
    }

    /**
     * Clear user's active cart.
     */
    private function clearUserCart($user): void
    {
        $cart = $user->activeCart()->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['status' => 'completed']);
        }
    }

    /**
     * Get all user orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $orders = Order::
                with(['items.meal.category', 'items.meal.subcategory', 'address'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) {
                    return $this->formatOrder($order);
                });

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders,
                'total_count' => $orders->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track the last order with status positions.
     */
    public function track(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $order = Order::where('user_id', $user->id)
                ->whereNotIn('status', ['cancelled', 'delivered'])
                ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active order found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order tracking retrieved successfully',
                'data' => [
                    'order' => $this->formatOrder($order),
                    'tracking' => [
                        'position' => $order->status_position,
                        'status' => $order->status,
                        'status_description' => $order->status_description,
                        'positions' => [
                            [
                                'position' => 1,
                                'status' => 'placed',
                                'label' => 'Order Placed',
                                'description' => 'Your order has been placed',
                                'completed' => in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered']),
                                'timestamp' => $order->placed_at,
                            ],
                            [
                                'position' => 2,
                                'status' => 'processing',
                                'label' => 'Processing',
                                'description' => 'Your order is being processed',
                                'completed' => in_array($order->status, ['processing', 'shipping', 'out_for_delivery', 'delivered']),
                                'timestamp' => $order->processing_at,
                            ],
                            [
                                'position' => 3,
                                'status' => 'shipping',
                                'label' => 'Shipping',
                                'description' => 'Your order is being shipped',
                                'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']),
                                'timestamp' => $order->shipping_at,
                            ],
                            [
                                'position' => 4,
                                'status' => 'out_for_delivery',
                                'label' => 'Out for Delivery',
                                'description' => 'Your order is on the way',
                                'completed' => in_array($order->status, ['out_for_delivery', 'delivered']),
                                'timestamp' => $order->out_for_delivery_at,
                            ],
                            [
                                'position' => 5,
                                'status' => 'delivered',
                                'label' => 'Delivered',
                                'description' => 'Your order has been delivered',
                                'completed' => $order->status === 'delivered',
                                'timestamp' => $order->delivered_at,
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format order data for response.
     */
    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method,
            'delivery_type' => $order->delivery_type,
            'status' => $order->status,
            'status_position' => $order->status_position,
            'status_description' => $order->status_description,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meal' => [
                        'id' => $item->meal->id,
                        'title' => $item->meal->title,
                        'slug' => $item->meal->slug,
                        'image_url' => $item->meal->image_url,
                        'price' => $item->meal->price,
                        'discount_price' => $item->meal->discount_price,
                        'final_price' => $item->meal->final_price,
                        'category' => $item->meal->category ? [
                            'id' => $item->meal->category->id,
                            'name' => $item->meal->category->name,
                        ] : null,
                        'subcategory' => $item->meal->subcategory ? [
                            'id' => $item->meal->subcategory->id,
                            'name' => $item->meal->subcategory->name,
                        ] : null,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'address' => $order->address ? [
                'id' => $order->address->id,
                'label' => $order->address->label,
                'full_name' => $order->address->full_name,
                'phone' => $order->address->phone,
                'country_code' => $order->address->country_code,
                'street_address' => $order->address->street_address,
                'building_number' => $order->address->building_number,
                'floor' => $order->address->floor,
                'apartment' => $order->address->apartment,
                'landmark' => $order->address->landmark,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country,
                'full_address' => $order->address->full_address,
                'latitude' => $order->address->latitude,
                'longitude' => $order->address->longitude,
            ] : null,
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'total' => $order->total,
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'placed_at' => $order->placed_at,
            'processing_at' => $order->processing_at,
            'shipping_at' => $order->shipping_at,
            'out_for_delivery_at' => $order->out_for_delivery_at,
            'delivered_at' => $order->delivered_at,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'special_note' => $order->special_note,
        ];
    }
}
