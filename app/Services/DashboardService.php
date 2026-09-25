<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get aggregated metrics for the user dashboard.
     */
    public function getDashboardData(User $user): array
    {
        return [
            'overview'              => $this->getOverview($user),
            'shopping_insights'     => $this->getShoppingInsights($user),
            'category_distribution' => $this->getCategoryDistribution($user),
            'recent_orders'         => $this->getRecentOrders($user),
            'top_purchases'         => $this->getTopPurchases($user),
        ];
    }

    private function getOverview(User $user): array
    {
        $activeOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->orderBy('created_at', 'desc')
            ->first();

        $cart = $user->activeCart()->with('items')->first();
        if ($cart) {
            $cart->calculateTotals();
        }

        $upcomingDelivery = Order::where('user_id', $user->id)
            ->whereIn('status', ['placed', 'processing', 'shipping', 'out_for_delivery'])
            ->whereNotNull('estimated_delivery_time')
            ->orderBy('estimated_delivery_time', 'asc')
            ->first();

        return [
            'tracking_order' => $activeOrder ? [
                'id'                 => $activeOrder->id,
                'order_number'       => $activeOrder->order_number,
                'status'             => $activeOrder->status,
                'status_description' => $activeOrder->status_description,
                'status_position'    => $activeOrder->status_position,
            ] : null,
            'loyalty_points' => (int) ($user->loyalty_points ?? 0),
            'store_credits'  => (float) ($user->store_credits ?? 0),
            'current_cart'   => $cart ? [
                'items_count'  => $cart->items->sum('quantity'),
                'total'        => (float) $cart->total,
                'last_updated' => $cart->updated_at,
            ] : [
                'items_count'  => 0,
                'total'        => 0.0,
                'last_updated' => null,
            ],
            'upcoming_delivery' => $upcomingDelivery ? [
                'order_id'                => $upcomingDelivery->id,
                'order_number'            => $upcomingDelivery->order_number,
                'date'                    => $upcomingDelivery->estimated_delivery_time?->format('Y-m-d'),
                'time'                    => $upcomingDelivery->estimated_delivery_time?->format('H:i'),
                'estimated_delivery_time' => $upcomingDelivery->estimated_delivery_time,
            ] : null,
        ];
    }

    private function getShoppingInsights(User $user): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $monthlyOrdersQuery = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled');

        $monthlySpend = (float) $monthlyOrdersQuery->sum('total');
        $ordersCount = $monthlyOrdersQuery->count();

        // حساب متوسط الأيام بين الطلبات
        $averageDaysBetweenOrders = 0.0;
        if ($ordersCount > 1) {
            $orderDates = $monthlyOrdersQuery->pluck('created_at')->sort()->values();
            $totalDays = 0;
            $intervals = $orderDates->count() - 1;

            for ($i = 1; $i <= $intervals; $i++) {
                $totalDays += $orderDates[$i]->diffInDays($orderDates[$i - 1]);
            }

            $averageDaysBetweenOrders = $intervals > 0 ? round($totalDays / $intervals, 1) : 0.0;
        }

        // إجمالي التوفير من الخصومات
        $orderDiscounts = (float) Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->sum('discount');

        $mealSavings = (float) OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', '!=', 'cancelled');
        })
        ->whereHas('meal', function ($query) {
            $query->whereNotNull('discount_price');
        })
        ->join('meals', 'order_items.meal_id', '=', 'meals.id')
        ->sum(DB::raw('(meals.price - meals.discount_price) * order_items.quantity'));

        $totalSavings = $orderDiscounts + $mealSavings;
        $averageOrderValue = $ordersCount > 0 ? round($monthlySpend / $ordersCount, 2) : 0.0;

        return [
            'monthly_spend' => $monthlySpend,
            'orders_this_month' => [
                'count'                       => $ordersCount,
                'average_days_between_orders' => $averageDaysBetweenOrders,
            ],
            'total_savings'       => $totalSavings,
            'average_order_value' => $averageOrderValue,
        ];
    }

    private function getCategoryDistribution(User $user): array
    {
        $categoryTotals = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', '!=', 'cancelled');
        })
        ->join('meals', 'order_items.meal_id', '=', 'meals.id')
        ->join('categories', 'meals.category_id', '=', 'categories.id')
        ->select(
            'categories.id as category_id',
            'categories.name as category_name',
            DB::raw('SUM(order_items.quantity) as total_quantity')
        )
        ->groupBy('categories.id', 'categories.name')
        ->get();

        $totalItems = $categoryTotals->sum('total_quantity');

        return $categoryTotals->map(function ($item) use ($totalItems) {
            return [
                'category_id'    => $item->category_id,
                'category_name'  => $item->category_name,
                'total_quantity' => (int) $item->total_quantity,
                'percentage'     => $totalItems > 0 ? round(($item->total_quantity / $totalItems) * 100, 1) : 0,
            ];
        })->sortByDesc('percentage')->values()->toArray();
    }

    private function getRecentOrders(User $user, int $limit = 5): array
    {
        return Order::where('user_id', $user->id)
            ->withCount('items as items_count')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($order) => [
                'id'                 => $order->id,
                'order_number'       => $order->order_number,
                'status'             => $order->status,
                'status_description' => $order->status_description,
                'total'              => (float) $order->total,
                'created_at'         => $order->created_at,
                'items_count'        => (int) $order->items_count,
            ])
            ->toArray();
    }

    private function getTopPurchases(User $user, int $limit = 10): array
    {
        return OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', '!=', 'cancelled');
        })
        ->with('meal.category')
        ->select(
            'meal_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(subtotal) as total_spent')
        )
        ->groupBy('meal_id')
        ->orderBy('total_quantity', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($item) {
            $meal = $item->meal;
            return [
                'meal_id'   => $meal?->id,
                'title'     => $meal?->title,
                'image_url' => $meal?->image_url,
                'category'  => $meal?->category ? [
                    'id'   => $meal->category->id,
                    'name' => $meal->category->name,
                ] : null,
                'total_quantity_purchased' => (int) $item->total_quantity,
                'total_spent'              => (float) $item->total_spent,
            ];
        })
        ->toArray();
    }
}