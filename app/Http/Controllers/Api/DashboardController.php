<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Meal;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics and insights.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'overview' => $this->getOverview($user),
                    'shopping_insights' => $this->getShoppingInsights($user),
                    'category_distribution' => $this->getCategoryDistribution($user),
                    'recent_orders' => $this->getRecentOrders($user),
                    'top_purchases' => $this->getTopPurchases($user),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get overview statistics.
     */
    private function getOverview($user): array
    {
        // Active order tracking
        $activeOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();

        $trackingOrder = null;
        if ($activeOrder) {
            $trackingOrder = [
                'id' => $activeOrder->id,
                'order_number' => $activeOrder->order_number,
                'status' => $activeOrder->status,
                'status_description' => $activeOrder->status_description,
                'status_position' => $activeOrder->status_position,
            ];
        }

        // Current cart
        $cart = $user->activeCart()->with('items')->first();
        $cartData = null;
        if ($cart) {
            $cart->calculateTotals();
            $cartData = [
                'items_count' => $cart->items->sum('quantity'),
                'total' => (float) $cart->total,
                'last_updated' => $cart->updated_at,
            ];
        } else {
            $cartData = [
                'items_count' => 0,
                'total' => 0,
                'last_updated' => null,
            ];
        }

        // Upcoming delivery
        $upcomingDelivery = Order::where('user_id', $user->id)
            ->whereIn('status', ['placed', 'processing', 'shipping', 'out_for_delivery'])
            ->whereNotNull('estimated_delivery_time')
            ->orderBy('estimated_delivery_time', 'asc')
            ->first();

        $upcomingDeliveryData = null;
        if ($upcomingDelivery) {
            $upcomingDeliveryData = [
                'order_id' => $upcomingDelivery->id,
                'order_number' => $upcomingDelivery->order_number,
                'date' => $upcomingDelivery->estimated_delivery_time?->format('Y-m-d'),
                'time' => $upcomingDelivery->estimated_delivery_time?->format('H:i'),
                'estimated_delivery_time' => $upcomingDelivery->estimated_delivery_time,
            ];
        }

        return [
            'tracking_order' => $trackingOrder,
            'loyalty_points' => (int) ($user->loyalty_points ?? 0),
            'store_credits' => (float) ($user->store_credits ?? 0),
            'current_cart' => $cartData,
            'upcoming_delivery' => $upcomingDeliveryData,
        ];
    }

    /**
     * Get shopping insights.
     */
    private function getShoppingInsights($user): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Monthly spend
        $monthlySpend = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Orders this month
        $ordersThisMonth = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->get();

        $ordersCount = $ordersThisMonth->count();

        // Average days between orders
        $averageDaysBetweenOrders = 0;
        if ($ordersCount > 1) {
            $orderDates = $ordersThisMonth->pluck('created_at')->sort();
            $totalDays = 0;
            $intervals = 0;

            for ($i = 1; $i < $orderDates->count(); $i++) {
                $days = $orderDates[$i]->diffInDays($orderDates[$i - 1]);
                $totalDays += $days;
                $intervals++;
            }

            $averageDaysBetweenOrders = $intervals > 0 ? round($totalDays / $intervals, 1) : 0;
        }

        // Total savings (from discounts in orders)
        $totalSavings = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->sum('discount');

        // Also calculate savings from meal discount prices
        $mealSavings = OrderItem::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', '!=', 'cancelled');
            })
            ->with('meal')
            ->get()
            ->sum(function ($item) {
                if ($item->meal && $item->meal->discount_price) {
                    return ($item->meal->price - $item->meal->discount_price) * $item->quantity;
                }
                return 0;
            });

        $totalSavings += $mealSavings;

        // Average order value
        $averageOrderValue = 0;
        if ($ordersCount > 0) {
            $averageOrderValue = (float) ($monthlySpend / $ordersCount);
        }

        return [
            'monthly_spend' => (float) $monthlySpend,
            'orders_this_month' => [
                'count' => $ordersCount,
                'average_days_between_orders' => $averageDaysBetweenOrders,
            ],
            'total_savings' => (float) $totalSavings,
            'average_order_value' => round($averageOrderValue, 2),
        ];
    }

    /**
     * Get category distribution percentage.
     */
    private function getCategoryDistribution($user): array
    {
        $orderItems = OrderItem::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', '!=', 'cancelled');
            })
            ->with('meal.category')
            ->get();

        $categoryTotals = [];
        $totalItems = 0;

        foreach ($orderItems as $item) {
            if ($item->meal && $item->meal->category) {
                $categoryId = $item->meal->category->id;
                $categoryName = $item->meal->category->name;
                $quantity = $item->quantity;

                if (!isset($categoryTotals[$categoryId])) {
                    $categoryTotals[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'total_quantity' => 0,
                    ];
                }

                $categoryTotals[$categoryId]['total_quantity'] += $quantity;
                $totalItems += $quantity;
            }
        }

        // Calculate percentages
        $distribution = [];
        foreach ($categoryTotals as $categoryId => $data) {
            $percentage = $totalItems > 0 ? round(($data['total_quantity'] / $totalItems) * 100, 1) : 0;
            $distribution[] = [
                'category_id' => $data['category_id'],
                'category_name' => $data['category_name'],
                'total_quantity' => $data['total_quantity'],
                'percentage' => $percentage,
            ];
        }

        // Sort by percentage descending
        usort($distribution, function ($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        return $distribution;
    }

    /**
     * Get recent orders.
     */
    private function getRecentOrders($user, int $limit = 5): array
    {
        $orders = Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_description' => $order->status_description,
                'total' => (float) $order->total,
                'created_at' => $order->created_at,
                'items_count' => $order->items->sum('quantity'),
            ];
        })->toArray();
    }

    /**
     * Get top purchases (most purchased meals).
     */
    private function getTopPurchases($user, int $limit = 10): array
    {
        $topMeals = OrderItem::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', '!=', 'cancelled');
            })
            ->with('meal.category', 'meal.subcategory')
            ->select('meal_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_spent'))
            ->groupBy('meal_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        return $topMeals->map(function ($item) {
            $meal = $item->meal;
            return [
                'meal_id' => $meal?->id,
                'title' => $meal?->title,
                'image_url' => $meal?->image_url ?? null,
                'category' => $meal?->category ? [
                    'id' => $meal->category->id,
                    'name' => $meal->category->name,
                ] : null,
                'total_quantity_purchased' => (int) $item->total_quantity,
                'total_spent' => (float) $item->total_spent,
            ];
        })->toArray();
    }
}
