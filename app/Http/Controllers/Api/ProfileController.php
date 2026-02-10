<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get full user profile: picture, name, gender, birthday, addresses,
     * order history, in-progress orders with tracking, order notifications,
     * settings (sessions), wishlist.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load(['addresses', 'favorites.meal.category', 'favorites.meal.subcategory']);

            $addresses = $user->addresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn (Address $a) => $this->formatAddress($a));

            $allOrders = Order::where('user_id', $user->id)
                ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
                ->orderBy('created_at', 'desc')
                ->get();

            $orderHistory = $allOrders->map(fn (Order $o) => $this->formatOrderSummary($o));

            $inProgressOrders = $allOrders->whereNotIn('status', ['cancelled', 'delivered']);
            $inProgressWithTracking = $inProgressOrders->map(fn (Order $o) => $this->formatOrderWithTracking($o))->values();

            $orderNotifications = $user->notifications()
                ->where(function ($q) {
                    $q->where('data->type', 'order_confirmation')
                        ->orWhere('data->type', 'order_shipped')
                        ->orWhere('data->type', 'delivery_updates');
                })
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get()
                ->map(fn ($n) => $this->formatNotification($n));

            $sessions = $this->formatSessions($user);
            $wishlist = $user->favorites->map(fn ($f) => $this->formatWishlistItem($f))->values();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'me' => [
                        'id' => $user->id,
                        'profile_picture' => $user->profile_image_url,
                        'name' => $user->full_name,
                        'username' => $user->username,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'gender' => $user->gender,
                        'birthday' => $user->birthday?->format('Y-m-d'),
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'country_code' => $user->country_code,
                        'email_verified' => $user->email_verified,
                        'phone_verified' => $user->phone_verified,
                        'preferred_languages' => $user->preferred_languages ?? [],
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'addresses' => $addresses,
                    'order_history' => [
                        'orders' => $orderHistory,
                        'ordered_at' => $orderHistory->map(fn ($o) => $o['placed_at'] ?? $o['created_at'])->values(),
                    ],
                    'in_progress_orders' => $inProgressWithTracking,
                    'order_notifications' => $orderNotifications,
                    'settings' => [
                        'privacy_and_security' => [
                            'active_sessions' => $sessions,
                            'change_password' => ['available' => true],
                            'change_username' => ['available' => true],
                        ],
                    ],
                    'wishlist' => $wishlist,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update profile image
     */
    public function updateImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            // Delete old image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $image = $request->file('image');
            $path = $image->store('profile-images', 'public');

            // Update user
            $user->update(['profile_image' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'data' => [
                    'profile_image' => $user->profile_image,
                    'profile_image_url' => $user->profile_image_url,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update profile information
     */
    public function updateInfo(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id), 'alpha_dash'],
                'firstname' => ['sometimes', 'string', 'max:255'],
                'lastname' => ['sometimes', 'string', 'max:255'],
                'gender' => ['sometimes', 'nullable', 'string', 'max:20', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
                'birthday' => ['sometimes', 'nullable', 'date', 'before:today'],
                'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone' => ['sometimes', 'string', 'max:20', Rule::unique('users')->ignore($user->id), 'regex:/^\+?[1-9]\d{1,14}$/'],
                'country_code' => ['sometimes', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
                'preferred_languages' => ['sometimes', 'array'],
                'preferred_languages.*' => ['string', 'max:10'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Update only provided fields
            $data = $request->only(['username', 'firstname', 'lastname', 'gender', 'birthday', 'email', 'phone', 'country_code', 'preferred_languages']);
            
            // Handle preferred_languages separately (can be empty array)
            if ($request->has('preferred_languages')) {
                $data['preferred_languages'] = $request->preferred_languages ?? [];
            }
            
            // Remove empty values (except preferred_languages which can be empty array)
            $data = array_filter($data, function ($value, $key) {
                if ($key === 'preferred_languages') {
                    return true; // Always include preferred_languages even if empty
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update',
                ], 400);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'full_name' => $user->full_name,
                    'gender' => $user->gender,
                    'birthday' => $user->birthday?->format('Y-m-d'),
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country_code' => $user->country_code,
                    'preferred_languages' => $user->preferred_languages ?? [],
                    'profile_image_url' => $user->profile_image_url,
                    'updated_at' => $user->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete profile image
     */
    public function deleteImage(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->profile_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile image to delete',
                ], 404);
            }

            // Delete image from storage
            if (Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Update user
            $user->update(['profile_image' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List active sessions/devices (Sanctum tokens). User can logout from each.
     */
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $request->user()->currentAccessToken()?->id();

        $tokens = $user->tokens()->get()->map(function ($token) use ($currentTokenId) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'is_current' => (string) $token->id === (string) $currentTokenId,
                'created_at' => $token->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Sessions retrieved successfully',
            'data' => $tokens,
        ]);
    }

    /**
     * Revoke a session/device (logout from that token).
     */
    public function destroySession(Request $request, string $tokenId): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id();

        if ((string) $tokenId === (string) $currentTokenId) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot revoke your current session from this request. Use logout instead.',
            ], 400);
        }

        $token = $user->tokens()->find($tokenId);
        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session revoked successfully',
        ]);
    }

    private function formatAddress(Address $address): array
    {
        return [
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
            'full_address' => $address->full_address ?? null,
            'is_default' => $address->is_default,
            'created_at' => $address->created_at,
            'updated_at' => $address->updated_at,
        ];
    }

    private function formatOrderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_description' => $order->status_description,
            'total' => (float) $order->total,
            'placed_at' => $order->placed_at?->toIso8601String(),
            'created_at' => $order->created_at?->toIso8601String(),
            'item_count' => $order->items->count(),
        ];
    }

    /**
     * Order with tracking: stages "arriving", "out for delivery", "delivered".
     */
    private function formatOrderWithTracking(Order $order): array
    {
        $trackingStage = match ($order->status) {
            'shipping' => 'arriving',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            default => 'processing',
        };

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_description' => $order->status_description,
            'tracking' => [
                'stage' => $trackingStage,
                'stage_label' => match ($trackingStage) {
                    'arriving' => 'Arriving',
                    'out_for_delivery' => 'Out for delivery',
                    'delivered' => 'Delivered',
                    default => 'Processing',
                },
                'positions' => [
                    ['stage' => 'arriving', 'label' => 'Arriving', 'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']), 'timestamp' => $order->shipping_at?->toIso8601String()],
                    ['stage' => 'out_for_delivery', 'label' => 'Out for delivery', 'completed' => in_array($order->status, ['out_for_delivery', 'delivered']), 'timestamp' => $order->out_for_delivery_at?->toIso8601String()],
                    ['stage' => 'delivered', 'label' => 'Delivered', 'completed' => $order->status === 'delivered', 'timestamp' => $order->delivered_at?->toIso8601String()],
                ],
            ],
            'total' => (float) $order->total,
            'placed_at' => $order->placed_at?->toIso8601String(),
            'estimated_delivery_time' => $order->estimated_delivery_time?->toIso8601String(),
            'address' => $order->address ? $this->formatAddress($order->address) : null,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'meal' => ['id' => $item->meal->id, 'title' => $item->meal->title, 'image_url' => $item->meal->image_url],
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
        ];
    }

    private function formatNotification($notification): array
    {
        $data = $notification->data ?? [];
        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'order',
            'title' => $data['title'] ?? 'Order update',
            'body' => $data['body'] ?? '',
            'is_read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'action_url' => $data['action_url'] ?? null,
        ];
    }

    private function formatSessions($user): array
    {
        $currentTokenId = $user->currentAccessToken()?->id();
        return $user->tokens()->get()->map(function ($token) use ($currentTokenId) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'is_current' => (string) $token->id === (string) $currentTokenId,
            ];
        })->all();
    }

    private function formatWishlistItem($favorite): array
    {
        $meal = $favorite->meal;
        return [
            'id' => $meal->id,
            'title' => $meal->title,
            'slug' => $meal->slug,
            'image_url' => $meal->image_url,
            ...$meal->getApiPriceAttributes(),
            'has_offer' => $meal->hasOffer(),
            'category' => $meal->category ? ['id' => $meal->category->id, 'name' => $meal->category->name] : null,
            'is_favorited' => true,
            'favorited_at' => $favorite->created_at?->toIso8601String(),
        ];
    }
}
