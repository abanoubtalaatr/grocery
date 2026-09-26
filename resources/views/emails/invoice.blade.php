<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
</head>
<body>
    <h1>Order invoice</h1>
    <p>Hello {{ $order->user->full_name ?? $order->user->username }},</p>
    <p>Thank you for your order. Here are the details:</p>

    <dl>
        <dt>Order number</dt>
        <dd>{{ $order->order_number }}</dd>
        <dt>Order date</dt>
        <dd>{{ $order->created_at?->format('M j, Y H:i') }}</dd>
        <dt>Status</dt>
        <dd>{{ str_replace('_', ' ', ucfirst($order->status)) }}</dd>
        <dt>Customer</dt>
        <dd>{{ $order->user->full_name ?? $order->user->username }} ({{ $order->user->email }})</dd>
    </dl>

    <table>
        <thead>
            <tr>
                <th scope="col">Meal</th>
                <th scope="col">Quantity</th>
                <th scope="col">Unit price</th>
                <th scope="col">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->meal->title }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <dl>
        <dt>Subtotal</dt>
        <dd>{{ number_format((float) $order->subtotal, 2) }}</dd>
        <dt>Tax</dt>
        <dd>{{ number_format((float) $order->tax, 2) }}</dd>
        <dt>Discount</dt>
        <dd>{{ number_format((float) $order->discount, 2) }}</dd>
        <dt>Shipping fee</dt>
        <dd>{{ number_format((float) $order->shipping_fee, 2) }}</dd>
        <dt>Total</dt>
        <dd>{{ number_format((float) $order->total, 2) }}</dd>
    </dl>
</body>
</html>