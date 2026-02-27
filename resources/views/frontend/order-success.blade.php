@extends('frontend.layouts.app')

@push('css')
    <style>
        .order-hero {
            background: linear-gradient(135deg, #F5FAFF 0%, #E3F2FD 100%);
            padding: 60px 0;
        }

        .order-hero-content {
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
        }

        .order-hero-icon {
            width: 120px;
            height: 120px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 4px 12px rgba(32, 58, 114, 0.1);
        }

        .order-hero-icon i {
            font-size: 60px;
            color: #28a745;
        }

        .order-hero h1 {
            font-size: 48px;
            font-weight: 700;
            color: #203A72;
            margin-bottom: 15px;
        }

        .order-hero p {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
        }

        .order-info-cards {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .order-info-card {
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .order-info-card label {
            font-size: 14px;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }

        .order-info-card h3 {
            font-size: 22px;
            font-weight: 700;
            color: #203A72;
            margin: 0;
        }

        .order-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .order-actions .btn {
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .order-summary-section {
            padding: 60px 0;
            background: #fff;
        }

        .summary-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-bottom: 20px;
            border: 1px solid #EEEEEE;
        }

        .summary-card h2 {
            font-size: 24px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #F5FAFF;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #EEEEEE;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            background: #F5FAFF;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .order-item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-details h3 {
            font-size: 18px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 5px;
        }

        .order-item-details p {
            font-size: 14px;
            color: #666;
            margin: 2px 0;
        }

        .order-item-price {
            font-size: 20px;
            font-weight: 700;
            color: #203A72;
        }

        .order-totals {
            padding: 20px 0;
            border-top: 2px solid #F5FAFF;
        }

        .order-totals-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 16px;
            color: #666;
        }

        .order-totals-row.grand-total {
            font-size: 22px;
            font-weight: 700;
            color: #203A72;
            padding-top: 15px;
            border-top: 2px solid #203A72;
        }

        .order-status-tracker {
            padding: 60px 0;
            background: #F5FAFF;
        }

        .status-steps {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            max-width: 800px;
            margin: 50px auto;
        }

        .status-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .status-icon {
            width: 70px;
            height: 70px;
            background: #E0E0E0;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .status-step.active .status-icon {
            background: #203A72;
        }

        .status-icon i {
            font-size: 30px;
            color: #fff;
        }

        .status-step h4 {
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 5px;
        }

        .status-step p {
            font-size: 13px;
            color: #666;
        }

        .status-line {
            position: absolute;
            top: 35px;
            left: 0;
            right: 0;
            height: 3px;
            background: #E0E0E0;
            z-index: 1;
        }

        .shipping-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .shipping-info-list li {
            padding: 8px 0;
            font-size: 15px;
            color: #333;
        }

        .shipping-info-list li strong {
            color: #203A72;
            display: block;
            font-weight: 600;
            margin-bottom: 3px;
        }
    </style>
@endpush

@section('content')
    {{-- Order Hero Section --}}
    <section class="order-hero">
        <div class="container">
            <div class="order-hero-content">
                <div class="order-hero-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1>Thank You for Your Order!</h1>
                <p>Your order has been successfully placed and is being processed.</p>

                <div class="order-info-cards">
                    <div class="order-info-card">
                        <label>Order Number</label>
                        <h3>{{ $order->order_number }}</h3>
                    </div>
                    <div class="order-info-card">
                        <label>Order Date</label>
                        <h3>{{ $order->created_at->format('M d, Y') }}</h3>
                    </div>
                </div>

                <div class="order-actions">
                    <a href="{{ route('order.invoice', $order->id) }}" class="btn cart-btn-css" target="_blank">
                        <i class="bi bi-download me-2"></i>Download Invoice
                    </a>
                    <a href="{{ route('products') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Order Summary Section --}}
    <section class="order-summary-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    {{-- Order Items --}}
                    <div class="summary-card">
                        <h2><i class="bi bi-box-seam me-2"></i>Items Ordered ({{ $order->items->count() }} items)</h2>
                        @foreach($order->items as $item)
                            <div class="order-item">
                                <div class="order-item-image">
                                    @if($item->product && $item->product->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}"
                                            alt="{{ $item->product_name }}">
                                    @else
                                        <img src="{{ asset('no-image-found.jpg') }}" alt="{{ $item->product_name }}">
                                    @endif
                                </div>
                                <div class="order-item-details">
                                    <h3>{{ $item->product_name }}</h3>
                                    <p><strong>SKU:</strong> {{ $item->sku }}</p>
                                    <p><strong>Quantity:</strong> {{ $item->quantity }}</p>
                                    @if($item->variant)
                                        <p><strong>Variant:</strong> {{ $item->variant->name }}</p>
                                    @endif
                                </div>
                                <div class="order-item-price">
                                    {{ currency_format($item->total) }}
                                </div>
                            </div>
                        @endforeach

                        {{-- Order Totals --}}
                        <div class="order-totals">
                            <div class="order-totals-row">
                                <span>Subtotal</span>
                                <span>{{ currency_format($order->sub_total) }}</span>
                            </div>
                            @if($order->discount_total > 0)
                                <div class="order-totals-row">
                                    <span>Discount</span>
                                    <span>-{{ currency_format($order->discount_total) }}</span>
                                </div>
                            @endif
                            @if($order->tax_total > 0)
                                <div class="order-totals-row">
                                    <span>Tax</span>
                                    <span>{{ currency_format($order->tax_total) }}</span>
                                </div>
                            @endif
                            @if($order->shipping_total > 0)
                                <div class="order-totals-row">
                                    <span>Shipping</span>
                                    <span>{{ currency_format($order->shipping_total) }}</span>
                                </div>
                            @endif
                            <div class="order-totals-row grand-total">
                                <span>Total Paid</span>
                                <span>{{ currency_format($order->grand_total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    {{-- Shipping Information --}}
                    <div class="summary-card">
                        <h2><i class="bi bi-truck me-2"></i>Shipping Information</h2>
                        <ul class="shipping-info-list">
                            <li>
                                <strong>Delivery Address</strong>
                                {{ $order->recipient_name }}<br>
                                {{ $order->shipping_address_line_1 }}<br>
                                @if($order->shipping_address_line_2){{ $order->shipping_address_line_2 }}<br>@endif
                                {{ $order->shippingCity?->name }}, {{ $order->shippingState?->name }},
                                {{ $order->shippingCountry?->name }} {{ $order->shipping_zipcode }}
                            </li>
                            <li>
                                <strong>Phone</strong>
                                {{ $order->recipient_contact_number }}
                            </li>
                            @if($order->recipient_email)
                                <li>
                                    <strong>Email</strong>
                                    {{ $order->recipient_email }}
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Payment Information --}}
                    <div class="summary-card">
                        <h2><i class="bi bi-credit-card me-2"></i>Payment Method</h2>
                        <p style="text-transform: capitalize;">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                        <p><strong>Payment Status:</strong> <span
                                style="text-transform: capitalize;">{{ $order->payment_status }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Order Status Tracker --}}
    <section class="order-status-tracker">
        <div class="container">
            <h2 class="text-center" style="font-size: 30px; font-weight: 700; color: #203A72; margin-bottom: 10px;">Order
                Status</h2>

            <div class="status-steps">
                <div class="status-line"></div>

                <div class="status-step active">
                    <div class="status-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h4>Order Placed</h4>
                    <p>{{ $order->created_at->format('M d') }}</p>
                </div>

                <div class="status-step {{ $order->confirmed_at ? 'active' : '' }}">
                    <div class="status-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <h4>Processing</h4>
                    <p>{{ $order->confirmed_at ? $order->confirmed_at->format('M d') : 'Pending' }}</p>
                </div>

                <div class="status-step {{ $order->shipped_at ? 'active' : '' }}">
                    <div class="status-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h4>Shipped</h4>
                    <p>{{ $order->shipped_at ? $order->shipped_at->format('M d') : 'Pending' }}</p>
                </div>

                <div class="status-step {{ $order->delivered_at ? 'active' : '' }}">
                    <div class="status-icon">
                        <i class="bi bi-house-check"></i>
                    </div>
                    <h4>Delivered</h4>
                    <p>{{ $order->delivered_at ? $order->delivered_at->format('M d') : 'Pending' }}</p>
                </div>
            </div>

            <div class="text-center mt-4">
                <p style="font-size: 18px; font-weight: 600; color: #203A72; margin-bottom: 5px;">Your order is being
                    processed</p>
                <p style="color: #666;">We'll send you an email with tracking information once your order ships.</p>
            </div>
        </div>
    </section>
@endsection