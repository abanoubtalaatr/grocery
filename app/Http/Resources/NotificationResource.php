<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    protected bool $detailed;
    protected $meals;
    protected $orders;

    public function __construct($resource, bool $detailed = false, $meals = null, $orders = null)
    {
        parent::__construct($resource);
        $this->detailed = $detailed;
        $this->meals = $meals;
        $this->orders = $orders;
    }

    public function toArray(Request $request): array
    {
        $data = $this->parseData($this->data);
        $type = is_string($data['type'] ?? null) ? $data['type'] : 'unknown';

        $response = [
            'id'               => $this->id,
            'type'             => $type,
            'title'            => is_string($data['title'] ?? null) ? $data['title'] : 'Notification',
            'body'             => is_string($data['body'] ?? null) ? $data['body'] : '',
            'action_url'       => $data['action_url'] ?? null,
            'action_label'     => is_string($data['action_label'] ?? null) ? $data['action_label'] : 'View',
            'is_read'          => !is_null($this->read_at),
            'read_at'          => $this->read_at?->toISOString(),
            'created_at'       => $this->created_at?->toISOString() ?? '',
            'created_at_human' => $this->created_at?->diffForHumans() ?? '',
            'icon'             => $this->getIconForType($type),
            'priority'         => is_string($data['priority'] ?? null) ? $data['priority'] : 'normal',
        ];

        if ($this->detailed) {
            $response['data']       = $data;
            $response['channels']   = $data['channels'] ?? ['database'];
            $response['metadata']   = $data['metadata'] ?? [];
            $response['expires_at']  = $data['expires_at'] ?? null;
        }

        if ($this->meals !== null || $this->orders !== null) {
            $response['resources'] = $this->attachResources($data);
        }

        return $response;
    }

    private function parseData(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function attachResources(array $data): array
    {
        $resources = [];

        if (!empty($data['meal_id']) && is_numeric($data['meal_id']) && $this->meals) {
            $meal = $this->meals->get((int) $data['meal_id']);
            if ($meal) {
                $resources['meal'] = [
                    'id'          => $meal->id,
                    'title'       => $meal->title,
                    'slug'        => $meal->slug,
                    'image_url'   => $meal->image_url,
                    ...$meal->getApiPriceAttributes(),
                    'has_offer'   => $meal->hasOffer(),
                    'category'    => $meal->category ? [
                        'id'   => $meal->category->id,
                        'name' => $meal->category->name,
                    ] : null,
                ];
            }
        }

        if (!empty($data['order_id']) && is_numeric($data['order_id']) && $this->orders) {
            $order = $this->orders->get((int) $data['order_id']);
            if ($order) {
                $resources['order'] = [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'status'       => $order->status,
                    'total'        => (string) $order->total,
                    'placed_at'    => $order->placed_at?->toIso8601String(),
                    'created_at'   => $order->created_at?->toIso8601String(),
                ];
            }
        }

        return $resources;
    }

    private function getIconForType(string $type): string
    {
        $icons = [
            'order_confirmation'  => 'shopping-bag',
            'order_shipped'       => 'truck',
            'delivery_updates'    => 'package',
            'out_of_stock_alerts' => 'alert-triangle',
            'weekly_discounts'    => 'percent',
            'exclusive_member_offers' => 'crown',
            'seasonal_campaigns'  => 'gift',
            'cart_reminders'      => 'shopping-cart',
            'payment_billing'     => 'credit-card',
            'system'              => 'bell',
            'account'             => 'user',
            'security'            => 'shield',
        ];

        return $icons[$type] ?? 'bell';
    }
}