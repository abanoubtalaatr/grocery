<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Get payment history for the authenticated user.
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $orders = Order::where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->with(['items.meal.category', 'address'])
                ->orderBy('created_at', 'desc')
                ->get();

            $paymentHistory = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_method' => $order->payment_method,
                    'payment_method_id' => $order->payment_method_id,
                    'amount' => (float) $order->total,
                    'subtotal' => (float) $order->subtotal,
                    'tax' => (float) $order->tax,
                    'discount' => (float) $order->discount,
                    'status' => $order->status,
                    'status_description' => $order->status_description,
                    'payment_date' => $order->placed_at ?? $order->created_at,
                    'created_at' => $order->created_at,
                    'items_count' => $order->items->sum('quantity'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment history retrieved successfully',
                'data' => $paymentHistory,
                'total_count' => $paymentHistory->count(),
                'total_amount' => (float) $orders->sum('total'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get receipt/invoice for a specific order.
     */
    public function receipt(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Verify order belongs to user
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);

            $receipt = $this->formatReceipt($order);

            return response()->json([
                'success' => true,
                'message' => 'Receipt retrieved successfully',
                'data' => $receipt,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve receipt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invoice for a specific order (alias for receipt).
     */
    public function invoice(Request $request, Order $order): JsonResponse
    {
        return $this->receipt($request, $order);
    }

    /**
     * Format order as receipt/invoice.
     */
    private function formatReceipt(Order $order): array
    {
        $user = $order->user;
        $address = $order->address;

        return [
            'receipt_number' => $order->order_number,
            'invoice_number' => 'INV-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
            'type' => 'receipt', // or 'invoice'
            'date' => $order->placed_at ?? $order->created_at,
            'payment_date' => $order->placed_at ?? $order->created_at,
            'status' => $order->status,
            'status_description' => $order->status_description,
            
            // Customer Information
            'customer' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->username ?? 'Customer',
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
            ],

            // Delivery Address
            'delivery_address' => $address ? [
                'id' => $address->id,
                'label' => $address->label,
                'full_name' => $address->full_name,
                'phone' => $address->phone,
                'country_code' => $address->country_code,
                'street_address' => $address->street_address,
                'building_number' => $address->building_number,
                'floor' => $address->floor,
                'apartment' => $address->apartment,
                'landmark' => $address->landmark,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'full_address' => $address->full_address,
            ] : null,

            // Payment Information
            'payment' => [
                'method' => $order->payment_method,
                'method_id' => $order->payment_method_id,
                'method_display' => $this->getPaymentMethodDisplay($order->payment_method),
            ],

            // Order Items
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meal' => [
                        'id' => $item->meal->id,
                        'title' => $item->meal->title,
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
                    'unit_price' => (float) $item->unit_price,
                    'discount_amount' => (float) $item->discount_amount,
                    'subtotal' => (float) $item->subtotal,
                ];
            }),

            // Pricing Summary
            'pricing' => [
                'subtotal' => (float) $order->subtotal,
                'tax' => (float) $order->tax,
                'tax_rate' => $order->subtotal > 0 ? round(($order->tax / $order->subtotal) * 100, 2) : 0,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
            ],

            // Delivery Information
            'delivery' => [
                'type' => $order->delivery_type,
                'estimated_delivery_time' => $order->estimated_delivery_time,
                'placed_at' => $order->placed_at,
                'processing_at' => $order->processing_at,
                'shipping_at' => $order->shipping_at,
                'out_for_delivery_at' => $order->out_for_delivery_at,
                'delivered_at' => $order->delivered_at,
            ],

            // Additional Information
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    /**
     * Get payment method display name.
     */
    private function getPaymentMethodDisplay(string $method): string
    {
        return match($method) {
            'card' => 'Credit/Debit Card',
            'cash_on_delivery' => 'Cash on Delivery',
            'cash' => 'Cash',
            'stripe' => 'Stripe',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }
}
