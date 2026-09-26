<x-mail::message>
# Order Invoice

Hello {{ $order->user->full_name ?? $order->user->username ?? 'Customer' }},

Thank you for your order.

**Invoice Number:** INV-{{ str_pad($order->id, 8, '0', STR_PAD_LEFT) }}  
**Order Number:** {{ $order->order_number }}  
**Date:** {{ ($order->placed_at ?? $order->created_at)->format('F j, Y g:i A') }}  
**Status:** {{ $order->status_description }}

<x-mail::table>
| Item | Qty | Unit Price | Subtotal |
| :--- | ---: | ---: | ---: |
@foreach ($order->items as $item)
| {{ $item->meal->title }} | {{ $item->quantity }} | {{ number_format((float) $item->unit_price, 2) }} | {{ number_format((float) $item->subtotal, 2) }} |
@endforeach
</x-mail::table>

**Subtotal:** {{ number_format((float) $order->subtotal, 2) }}  
**Tax:** {{ number_format((float) $order->tax, 2) }}  
**Discount:** {{ number_format((float) $order->discount, 2) }}  
**Shipping:** {{ number_format((float) $order->shipping_fee, 2) }}  
**Total:** {{ number_format((float) $order->total, 2) }}

**Payment Method:** {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}

@if ($order->address)
**Delivery Address:**  
{{ $order->address->full_address }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
