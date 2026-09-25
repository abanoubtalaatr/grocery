<?php
// app/Http/Resources/NotificationResource.php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->resource;
        $data = $notification->data;
        
        return [
            'id' => $notification->id,
            'type' => $this->getType($data),
            'category' => $this->getCategory($data),
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? 'View',
            'is_read' => !is_null($notification->read_at),
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'created_at_human' => $notification->created_at->diffForHumans(),
            'icon' => $this->getIcon($data),
            'priority' => $data['priority'] ?? 'normal',
            'metadata' => $data['metadata'] ?? [],
            'expires_at' => $data['expires_at'] ?? null,
        ];
    }
    
    private function getType(array $data): string
    {
        return $data['type'] ?? 'unknown';
    }
    
    private function getCategory(array $data): string
    {
        $type = $this->getType($data);
        
        $categories = [
            'order_confirmation' => 'Order & Delivery',
            'order_shipped' => 'Order & Delivery',
            'delivery_updates' => 'Order & Delivery',
            'out_of_stock_alerts' => 'Order & Delivery',
            'weekly_discounts' => 'Deals & Promotions',
            'exclusive_member_offers' => 'Deals & Promotions',
            'seasonal_campaigns' => 'Deals & Promotions',
            'cart_reminders' => 'Account & Reminders',
            'payment_billing' => 'Account & Reminders',
        ];
        
        return $categories[$type] ?? 'System';
    }
    
    private function getIcon(array $data): string
    {
        $type = $this->getType($data);
        
        $icons = [
            'order_confirmation' => 'shopping-bag',
            'order_shipped' => 'truck',
            'delivery_updates' => 'package',
            'out_of_stock_alerts' => 'alert-triangle',
            'weekly_discounts' => 'percent',
            'exclusive_member_offers' => 'crown',
            'seasonal_campaigns' => 'gift',
            'cart_reminders' => 'shopping-cart',
            'payment_billing' => 'credit-card',
        ];
        
        return $icons[$type] ?? 'bell';
    }
}