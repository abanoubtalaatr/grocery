<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order invoice</title>
</head>
<body>
    <h1>{{ config('app.name') }} order invoice</h1>
    <p>Hello {{ $order->user->full_name ?? $order->user->username ?? 'Customer' }},</p>
    <p>Invoice: INV-{{ str_pad((string) $order->id, 8, '0', STR_PAD_LEFT) }}</p>
    <p>Order: {{ $order->order_number }}</p>
    <p>Date: {{ ($order->placed_at ?? $order->created_at)->format('F j, Y g:i A') }}</p>

    <table style="width: 100%; border-collapse: collapse;" border="1" cellpadding="8">
        <thead>
            <tr>
                <th align="left">Item</th>
                <th align="right">Quantity</th>
                <th align="right">Unit price</th>
                <th align="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->meal?->title ?? 'Item' }}</td>
                    <td align="right">{{ $item->quantity }}</td>
                    <td align="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td align="right">{{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Subtotal: {{ number_format((float) $order->subtotal, 2) }}</p>
    <p>Tax: {{ number_format((float) $order->tax, 2) }}</p>
    <p>Discount: {{ number_format((float) $order->discount, 2) }}</p>
    <p>Shipping: {{ number_format((float) $order->shipping_fee, 2) }}</p>
    <p><strong>Total: {{ number_format((float) $order->total, 2) }}</strong></p>

    @if ($order->address)
        <p>Delivery address: {{ $order->address->full_address }}</p>
    @endif
</body>
</html>
