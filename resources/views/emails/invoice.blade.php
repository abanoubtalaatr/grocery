<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
</head>
<body>

    <h2>Order Invoice</h2>

    <p>
        <strong>Invoice Number:</strong>
        {{ $invoice['invoice_number'] }}
    </p>

    <p>
        <strong>Order Number:</strong>
        {{ $invoice['receipt_number'] }}
    </p>

    <p>
        <strong>Date:</strong>
        {{ $invoice['date'] }}
    </p>

    <hr>

    <h3>Customer Information</h3>

    <p>
        <strong>Name:</strong>
        {{ $invoice['customer']['name'] }}
    </p>

    <p>
        <strong>Email:</strong>
        {{ $invoice['customer']['email'] }}
    </p>

    <p>
        <strong>Phone:</strong>
        {{ $invoice['customer']['phone'] }}
    </p>

    <hr>

    <h3>Order Items</h3>

    <ul>
        @foreach ($invoice['items'] as $item)
            <li>
                {{ $item['meal']['title'] }}
                -
                Quantity: {{ $item['quantity'] }}
                -
                Price: {{ $item['unit_price'] }}
                -
                Subtotal: {{ $item['subtotal'] }}
            </li>
        @endforeach
    </ul>

    <hr>

    <h3>Pricing</h3>

    <p>
        <strong>Subtotal:</strong>
        {{ $invoice['pricing']['subtotal'] }}
    </p>

    <p>
        <strong>Tax:</strong>
        {{ $invoice['pricing']['tax'] }}
    </p>

    <p>
        <strong>Discount:</strong>
        {{ $invoice['pricing']['discount'] }}
    </p>

    <p>
        <strong>Total:</strong>
        {{ $invoice['pricing']['total'] }}
    </p>

    <hr>

    <p>
        <strong>Payment Method:</strong>
        {{ $invoice['payment']['method_display'] }}
    </p>

    <p>Thank you for your order.</p>

</body>
</html>