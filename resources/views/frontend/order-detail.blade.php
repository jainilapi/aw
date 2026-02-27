@extends('frontend.layouts.app')

@push('css')
    <style>
        .order-detail-page {
            padding: 40px 0;
            background: #F8FAFC;
            min-height: 80vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .order-title {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .order-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #203A72;
            margin: 0;
        }

        .order-badges {
            display: flex;
            gap: 8px;
        }

        .order-meta {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #203A72;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-btn:hover {
            color: #1a2d5a;
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-invoice {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #203A72;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-invoice:hover {
            background: #1a2d5a;
            color: #fff;
        }

        /* Cards */
        .detail-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Status Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 9px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #E0E0E0;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -25px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #E0E0E0;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #E0E0E0;
        }

        .timeline-item.active .timeline-dot {
            background: #28A745;
            box-shadow: 0 0 0 2px #28A745;
        }

        .timeline-item.current .timeline-dot {
            background: #203A72;
            box-shadow: 0 0 0 2px #203A72;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 2px #203A72;
            }

            50% {
                box-shadow: 0 0 0 5px rgba(32, 58, 114, 0.3);
            }
        }

        .timeline-content {
            padding-left: 8px;
        }

        .timeline-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .timeline-date {
            font-size: 12px;
            color: #999;
        }

        /* Items Table */
        .items-table {
            width: 100%;
        }

        .items-table th {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            padding: 12px 10px;
            border-bottom: 2px solid #F0F0F0;
        }

        .items-table td {
            padding: 16px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #F0F0F0;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: contain;
            background: #F5F5F5;
            padding: 4px;
        }

        .item-details {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .item-name {
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }

        .item-meta {
            font-size: 12px;
            color: #999;
        }

        .free-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-left: 8px;
        }

        .bundle-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #E3F2FD;
            color: #1976D2;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            margin-left: 8px;
        }

        /* Summary */
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #F0F0F0;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #203A72;
            padding-top: 16px;
            border-top: 2px solid #203A72;
            margin-top: 8px;
        }

        .summary-label {
            color: #666;
        }

        .summary-value {
            font-weight: 500;
            color: #333;
        }

        .discount-value {
            color: #11998e;
        }

        /* Addresses */
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 768px) {
            .address-grid {
                grid-template-columns: 1fr;
            }
        }

        .address-box {
            padding: 20px;
            background: #F8FAFC;
            border-radius: 12px;
            border-left: 4px solid #203A72;
        }

        .address-type {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .address-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .address-line {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        /* Row Layout */
        .detail-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 992px) {
            .detail-row {
                grid-template-columns: 1fr;
            }
        }

        .badge {
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Promotion Info */
        .promo-info {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: linear-gradient(to right, rgba(17, 153, 142, 0.05), rgba(56, 239, 125, 0.05));
            border: 1px dashed #11998e;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .promo-info i {
            color: #11998e;
            font-size: 18px;
        }

        .promo-info span {
            color: #11998e;
            font-weight: 500;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="bred-pro">
            <div class="container">
                <div class="breadcrumb-container">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('customer.orders') }}">My Orders</a></li>
                        <li class="active">{{ $order->order_number }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="order-detail-page">
        <div class="container">
            <div class="page-header">
                <div>
                    <div class="order-title">
                        <h1><i class="bi bi-receipt"></i> Order {{ $order->order_number }}</h1>
                        <div class="order-badges">
                            {!! $order->status_badge !!}
                            {!! $order->payment_status_badge !!}
                        </div>
                    </div>
                    <p class="order-meta">
                        <i class="bi bi-calendar3 me-1"></i>Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}
                    </p>
                </div>
                <div class="order-actions">
                    <a href="{{ route('customer.orders') }}" class="back-btn">
                        <i class="bi bi-arrow-left"></i> Back to Orders
                    </a>
                    <a href="{{ route('order.invoice', $order->id) }}" class="btn-invoice" target="_blank">
                        <i class="bi bi-download"></i> Download Invoice
                    </a>
                </div>
            </div>

            <div class="detail-row">
                <div>
                    <!-- Order Items -->
                    <div class="detail-card">
                        <h4 class="card-title"><i class="bi bi-box-seam"></i> Order Items ({{ count($orderItems) }})</h4>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="item-details">
                                                <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}"
                                                    class="item-image" onerror="this.src='{{ asset('no-image-found.jpg') }}'">
                                                <div>
                                                    <div class="item-name">
                                                        {{ $item['product_name'] }}
                                                        @if($item['is_free_item'])
                                                            <span class="free-badge"><i class="bi bi-gift-fill"></i> FREE</span>
                                                        @endif
                                                        @if($item['is_bundle'])
                                                            <span class="bundle-badge">BUNDLE</span>
                                                        @endif
                                                    </div>
                                                    <div class="item-meta">
                                                        SKU: {{ $item['sku'] }}
                                                        @if($item['variant_name'])
                                                            | Variant: {{ $item['variant_name'] }}
                                                        @endif
                                                        @if($item['unit_name'])
                                                            | Unit: {{ $item['unit_name'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $item['quantity'] }}</td>
                                        <td class="text-end">
                                            @if($item['is_free_item'])
                                                <span
                                                    style="text-decoration: line-through; color: #999;">{{ currency_format($item['unit_price']) }}</span>
                                            @else
                                                {{ currency_format($item['unit_price']) }}
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($item['is_free_item'])
                                                <span style="color: #11998e; font-weight: 600;">FREE</span>
                                            @else
                                                <strong>{{ currency_format($item['total']) }}</strong>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Addresses -->
                    <div class="detail-card">
                        <h4 class="card-title"><i class="bi bi-geo-alt"></i> Delivery Information</h4>
                        <div class="address-grid">
                            <div class="address-box">
                                <div class="address-type"><i class="bi bi-truck me-1"></i> Shipping Address</div>
                                <div class="address-name">{{ $order->shipping_name }}</div>
                                <div class="address-line">
                                    {{ $order->shipping_address_line_1 }}<br>
                                    @if($order->shipping_address_line_2)
                                        {{ $order->shipping_address_line_2 }}<br>
                                    @endif
                                    {{ $order->shippingCity?->name }}, {{ $order->shippingState?->name }}
                                    {{ $order->shipping_zipcode }}<br>
                                    {{ $order->shippingCountry?->name }}
                                </div>
                                @if($order->shipping_phone)
                                    <div class="address-line mt-2">
                                        <i class="bi bi-telephone me-1"></i> {{ $order->shipping_phone }}
                                    </div>
                                @endif
                            </div>
                            <div class="address-box">
                                <div class="address-type"><i class="bi bi-receipt me-1"></i> Billing Address</div>
                                <div class="address-name">{{ $order->billing_name ?? $order->shipping_name }}</div>
                                <div class="address-line">
                                    {{ $order->billing_address_line_1 ?? $order->shipping_address_line_1 }}<br>
                                    @if($order->billing_address_line_2 ?? $order->shipping_address_line_2)
                                        {{ $order->billing_address_line_2 ?? $order->shipping_address_line_2 }}<br>
                                    @endif
                                    {{ $order->billingCity?->name ?? $order->shippingCity?->name }},
                                    {{ $order->billingState?->name ?? $order->shippingState?->name }}
                                    {{ $order->billing_zipcode ?? $order->shipping_zipcode }}<br>
                                    {{ $order->billingCountry?->name ?? $order->shippingCountry?->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <!-- Order Summary -->
                    <div class="detail-card">
                        <h4 class="card-title"><i class="bi bi-calculator"></i> Order Summary</h4>

                        @if($order->promotion_code)
                            <div class="promo-info">
                                <i class="bi bi-tag-fill"></i>
                                <span>Coupon Applied: {{ $order->promotion_code }}</span>
                            </div>
                        @endif

                        <div class="summary-row">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value">{{ currency_format($order->sub_total) }}</span>
                        </div>
                        @if($order->tax_total > 0)
                            <div class="summary-row">
                                <span class="summary-label">Tax</span>
                                <span class="summary-value">{{ currency_format($order->tax_total) }}</span>
                            </div>
                        @endif
                        <div class="summary-row">
                            <span class="summary-label">Shipping</span>
                            <span class="summary-value">
                                @if($order->shipping_total > 0)
                                    {{ currency_format($order->shipping_total) }}
                                @else
                                    <span class="text-success">Free</span>
                                @endif
                            </span>
                        </div>
                        @if($order->discount_total > 0)
                            <div class="summary-row">
                                <span class="summary-label">Discount</span>
                                <span class="summary-value discount-value">-{{ currency_format($order->discount_total) }}</span>
                            </div>
                        @endif
                        @if($order->credit_utilization > 0)
                            <div class="summary-row">
                                <span class="summary-label">Credit Used</span>
                                <span
                                    class="summary-value discount-value">-{{ currency_format($order->credit_utilization) }}</span>
                            </div>
                        @endif
                        <div class="summary-row total">
                            <span>Grand Total</span>
                            <span>{{ currency_format($order->grand_total) }}</span>
                        </div>

                        @if($order->amount_paid > 0 || $order->amount_due > 0)
                            <div class="summary-row"
                                style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #F0F0F0;">
                                <span class="summary-label">Amount Paid</span>
                                <span class="summary-value">{{ currency_format($order->amount_paid) }}</span>
                            </div>
                            @if($order->amount_due > 0)
                                <div class="summary-row">
                                    <span class="summary-label">Amount Due</span>
                                    <span class="summary-value"
                                        style="color: #DC3545; font-weight: 600;">{{ currency_format($order->amount_due) }}</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Order Timeline -->
                    <div class="detail-card">
                        <h4 class="card-title"><i class="bi bi-clock-history"></i> Order Timeline</h4>
                        <div class="timeline">
                            @php
                                $statusOrder = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered'];
                                $currentStatusIndex = array_search($order->status, $statusOrder);
                            @endphp

                            @foreach($statusOrder as $index => $status)
                                @php
                                    $isActive = $index <= $currentStatusIndex && $currentStatusIndex !== false;
                                    $isCurrent = $status === $order->status;
                                    $statusLabels = \App\Models\AwOrder::getStatuses();
                                @endphp
                                <div class="timeline-item {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">{{ $statusLabels[$status] ?? ucfirst($status) }}</div>
                                        @if($isCurrent)
                                            <div class="timeline-date">Current Status</div>
                                        @elseif($isActive)
                                            <div class="timeline-date">Completed</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if(in_array($order->status, ['cancelled', 'rejected', 'returned']))
                                <div class="timeline-item current">
                                    <div class="timeline-dot" style="background: #DC3545; box-shadow: 0 0 0 2px #DC3545;"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-title" style="color: #DC3545;">{{ ucfirst($order->status) }}</div>
                                        <div class="timeline-date">
                                            {{ $order->{$order->status . '_at'}?->format('M d, Y h:i A') ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($order->notes)
                        <div class="detail-card">
                            <h4 class="card-title"><i class="bi bi-sticky"></i> Order Notes</h4>
                            <p style="color: #666; margin: 0;">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection