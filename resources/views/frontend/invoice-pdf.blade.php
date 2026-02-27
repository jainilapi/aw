<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #203A72;
        }

        .header h1 {
            color: #203A72;
            font-size: 32px;
            margin: 0 0 10px 0;
        }

        .company-info {
            font-size: 12px;
            color: #666;
        }

        .invoice-details {
            margin: 30px 0;
            overflow: hidden;
        }

        .invoice-details-left {
            float: left;
            width: 50%;
        }

        .invoice-details-right {
            float: right;
            width: 45%;
            text-align: right;
        }

        .invoice-details h3 {
            color: #203A72;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .invoice-details p {
            margin: 5px 0;
            font-size: 13px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .invoice-table th {
            background: #203A72;
            color: #fff;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .invoice-table tr:last-child td {
            border-bottom: none;
        }

        .invoice-table .text-right {
            text-align: right;
        }

        .invoice-table .text-center {
            text-align: center;
        }

        .totals-section {
            margin-top: 30px;
            float: right;
            width: 300px;
        }

        .totals-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .totals-label {
            display: table-cell;
            text-align: left;
            font-weight: 600;
        }

        .totals-value {
            display: table-cell;
            text-align: right;
        }

        .totals-grand {
            background: #203A72;
            color: #fff;
            padding: 12px;
            margin-top: 10px;
            font-size: 18px;
            font-weight: 700;
        }

        .footer {
            margin-top: 80px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
            clear: both;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <h1>INVOICE</h1>
        <div class="company-info">
            <strong>Anjo Wholesale</strong><br>
            P.O. Box 104 St. John's, Antigua & Barbuda<br>
            Phone: (268) 480-3080 | Email: info@anjowholesale.com
        </div>
    </div>

    {{-- Invoice Details --}}
    <div class="invoice-details">
        <div class="invoice-details-left">
            <h3>Bill To:</h3>
            <p><strong>{{ $order->billing_name }}</strong></p>
            <p>{{ $order->billing_address_line_1 }}</p>
            @if($order->billing_address_line_2)
                <p>{{ $order->billing_address_line_2 }}</p>
            @endif
            <p>
                {{ $order->billingCity?->name }}, {{ $order->billingState?->name }}<br>
                {{ $order->billingCountry?->name }} {{ $order->billing_zipcode }}
            </p>
            <p>Phone: {{ $order->billing_contact_number }}</p>
            @if($order->billing_email)
                <p>Email: {{ $order->billing_email }}</p>
            @endif
        </div>

        <div class="invoice-details-right">
            <h3>Invoice Details:</h3>
            <p><strong>Invoice Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Invoice Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
        </div>
    </div>

    <div class="clearfix"></div>

    {{-- Shipping Address --}}
    <div style="margin: 30px 0; padding: 15px; background: #F5FAFF; border-radius: 5px;">
        <h3 style="color: #203A72; margin-top: 0;">Shipping Address:</h3>
        <p style="margin: 5px 0;"><strong>{{ $order->recipient_name }}</strong></p>
        <p style="margin: 5px 0;">{{ $order->shipping_address_line_1 }}</p>
        @if($order->shipping_address_line_2)
            <p style="margin: 5px 0;">{{ $order->shipping_address_line_2 }}</p>
        @endif
        <p style="margin: 5px 0;">
            {{ $order->shippingCity?->name }}, {{ $order->shippingState?->name }}<br>
            {{ $order->shippingCountry?->name }} {{ $order->shipping_zipcode }}
        </p>
        <p style="margin: 5px 0;">Phone: {{ $order->recipient_contact_number }}</p>
    </div>

    {{-- Items Table --}}
    <table class="invoice-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item Description</th>
                <th class="text-center" style="width: 15%;">SKU</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 12%;">Unit Price</th>
                <th class="text-right" style="width: 13%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->variant)
                            <br><small style="color: #666;">Variant: {{ $item->variant->name }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->sku }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals-section">
        <div class="totals-row">
            <div class="totals-label">Subtotal:</div>
            <div class="totals-value">${{ number_format($order->sub_total, 2) }}</div>
        </div>

        @if($order->discount_total > 0)
            <div class="totals-row">
                <div class="totals-label">Discount:</div>
                <div class="totals-value">-${{ number_format($order->discount_total, 2) }}</div>
            </div>
        @endif

        @if($order->tax_total > 0)
            <div class="totals-row">
                <div class="totals-label">Tax:</div>
                <div class="totals-value">${{ number_format($order->tax_total, 2) }}</div>
            </div>
        @endif

        @if($order->shipping_total > 0)
            <div class="totals-row">
                <div class="totals-label">Shipping:</div>
                <div class="totals-value">${{ number_format($order->shipping_total, 2) }}</div>
            </div>
        @endif

        <div class="totals-grand">
            <div class="totals-row" style="border: none; padding: 0;">
                <div class="totals-label">Grand Total:</div>
                <div class="totals-value">${{ number_format($order->grand_total, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    @if($order->notes)
        <div style="margin-top: 40px; padding: 15px; background: #F5FAFF; border-left: 4px solid #203A72;">
            <strong>Order Notes:</strong><br>
            {{ $order->notes }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>For inquiries, please contact us at info@anjowholesale.com or call (268) 480-3080</p>
        <p style="margin-top: 10px; font-size: 11px;">This is a computer-generated invoice and does not require a
            signature.</p>
    </div>
</body>

</html>