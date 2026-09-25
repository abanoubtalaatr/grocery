<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderNote;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

class StoreOrderAction
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function handle(User $user, array $validated): Order
    {
        $cart = $user->activeCart()->with('items.meal')->first();

        if (! $cart || $cart->isEmpty()) {
            throw new \RuntimeException('Your cart is empty. Please add items to your cart before placing an order.');
        }

        $itemsResult = $this->orderService->validateAndProcessCartItems($cart->items);
        if (! $itemsResult['success']) {
            throw new \RuntimeException($itemsResult['response']['message'] ?? 'Unable to validate cart items.');
        }

        $items = $itemsResult['items'];
        $cart->calculateTotals();

        $totals = $this->orderService->calculateTotals((float) $cart->subtotal, $validated['delivery_type']);

        $paymentResult = ['success' => true, 'stripe_payment_intent_id' => null];
        if (($validated['payment_method'] ?? null) === 'card') {
            $paymentResult = $this->orderService->processPayment($user, $validated, (float) $totals['total']);
            if (! $paymentResult['success']) {
                throw new \RuntimeException($paymentResult['response']['message'] ?? 'Payment processing failed.');
            }
        }

        DB::beginTransaction();

        try {
            $order = $this->orderService->createOrder(
                $user,
                $validated,
                (float) $totals['subtotal'],
                $totals,
                $paymentResult['stripe_payment_intent_id'] ?? null,
            );

            $this->orderService->createOrderItems($order, $items);
            $this->orderService->clearUserCart($user);

            if (isset($validated['special_note_id'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => $validated['special_note_id'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            if (isset($validated['notes']) && ! isset($validated['special_note_id'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => null,
                    'notes' => $validated['notes'],
                ]);
            }

            DB::commit();

            return $order->load(['items.meal', 'address']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
