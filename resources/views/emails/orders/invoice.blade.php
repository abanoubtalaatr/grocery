<x-mail::message>
# Order Invoice / Receipt

Hi **{{ $order->user->name }}**,

Thank you for your order! Here is your invoice details for Order **#{{ $order->order_number }}**.

<x-mail::table>
| Item | Qty | Unit Price | Subtotal |
| :--- | :---: | :---: | :---: |
@foreach($order->items as $item)
| {{ $item->meal->title }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 2) }} | ${{ number_format($item->subtotal, 2) }} |
@endforeach
</x-mail::table>

<x-mail::panel>
**Subtotal:** ${{ number_format($order->subtotal, 2) }}  
**Tax:** ${{ number_format($order->tax, 2) }}  
**Discount:** -${{ number_format($order->discount, 2) }}  
**Shipping Fee:** ${{ number_format($order->shipping_fee, 2) }}  
***
**Grand Total:** ${{ number_format($order->total, 2) }}
</x-mail::panel>

**Payment Method:** {{ strtoupper($order->payment_method) }}  
**Delivery Type:** {{ ucfirst($order->delivery_type) }}  

@if($order->address)
**Shipping Address:**  
{{ $order->address->full_address ?? $order->address->street_address }}
@endif

<x-mail::button :url="config('app.url') . '/api/v1/payments/receipt/' . $order->id">
View Receipt Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>