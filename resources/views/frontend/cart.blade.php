@extends('frontend.layouts.app')

@push('css')
    {{-- Select2 CSS for guest address --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <style>
        .cart-page {
            padding: 50px 0;
            background-color: #F5FAFF;
            min-height: 70vh;
        }

        .cart-header {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .cart-header h1 {
            font-size: 32px;
            font-weight: 600;
            color: #203A72;
            margin: 0;
        }

        .cart-items-container {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #EEEEEE;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            flex-shrink: 0;
            border-radius: 8px;
            background: #F5FAFF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .cart-item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .cart-item-details {
            flex: 1;
            min-width: 0;
        }

        .cart-item-details h3 {
            font-size: 18px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 8px;
        }

        .cart-item-details h3 a {
            color: #203A72;
            text-decoration: none;
            transition: color 0.3s;
        }

        .cart-item-details h3 a:hover {
            color: #1a2d5a;
        }

        .cart-item-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .cart-item-price {
            font-size: 20px;
            font-weight: 600;
            color: #203A72;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .quantity-group {
            display: flex;
            align-items: center;
            border: 1px solid #D9D9D9;
            border-radius: 8px;
            overflow: hidden;
        }

        .quantity-group button {
            width: 44px;
            height: 44px;
            border: none;
            background: #F5F5F5;
            color: #203A72;
            font-size: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quantity-group button:hover {
            background: #EEEEEE;
        }

        .quantity-group input {
            width: 60px;
            height: 44px;
            border: none;
            border-left: 1px solid #D9D9D9;
            border-right: 1px solid #D9D9D9;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
        }

        .remove-item-btn {
            background: none;
            border: none;
            color: #D30606;
            font-size: 20px;
            cursor: pointer;
            padding: 10px;
            transition: color 0.3s;
        }

        .remove-item-btn:hover {
            color: #a00505;
        }

        .cart-summary {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 20px;
        }

        .cart-summary h2 {
            font-size: 24px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #EEEEEE;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-size: 20px;
            font-weight: 600;
            color: #203A72;
        }

        .summary-label {
            font-size: 16px;
            color: #666;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 600;
            color: #203A72;
        }

        .checkout-btn {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            margin-top: 20px;
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-cart-icon {
            font-size: 80px;
            color: #9CADC0;
            margin-bottom: 30px;
        }

        .empty-cart h2 {
            font-size: 28px;
            color: #203A72;
            margin-bottom: 15px;
        }

        .empty-cart p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }

        /* Checkout Form Styles */
        .checkout-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-bottom: 20px;
        }

        .checkout-card h2 {
            font-size: 24px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #F5FAFF;
        }

        .checkout-card .form-label {
            font-weight: 500;
            color: #203A72;
            margin-bottom: 6px;
        }

        .checkout-card .form-control,
        .checkout-card .form-select {
            border: 1px solid #D9D9D9;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 15px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .checkout-card .form-control:focus,
        .checkout-card .form-select:focus {
            border-color: #203A72;
            box-shadow: 0 0 0 3px rgba(32, 58, 114, 0.1);
        }

        .address-radio-card {
            border: 2px solid #E0E0E0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .address-radio-card:hover {
            border-color: #203A72;
            background: #F5FAFF;
        }

        .address-radio-card.selected {
            border-color: #203A72;
            background: #F5FAFF;
        }

        .address-radio-card input[type="radio"] {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 20px;
            height: 20px;
            accent-color: #203A72;
        }

        .address-radio-card .address-name {
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 8px;
        }

        .address-radio-card .address-detail {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        .section-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #9CADC0;
            font-size: 14px;
        }

        .section-divider::before,
        .section-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #E0E0E0;
        }

        .section-divider span {
            padding: 0 15px;
        }

        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #D9D9D9;
            border-radius: 8px;
            min-height: 48px;
            padding: 6px;
        }

        .text-danger {
            color: #D30606 !important;
            font-size: 12px;
        }

        #checkoutMessage .alert {
            margin-bottom: 0;
        }

        /* Coupon Section Styles - Zomato/Swiggy Inspired */
        .coupon-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .coupon-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .coupon-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .coupon-header i {
            font-size: 24px;
            color: #fff;
        }

        .coupon-header h3 {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .coupon-input-group {
            display: flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 8px;
        }

        .coupon-input-group input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 12px 15px;
            font-size: 15px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .coupon-input-group input:focus {
            outline: none;
        }

        .coupon-input-group input::placeholder {
            text-transform: none;
            letter-spacing: 0;
            color: #999;
        }

        .apply-coupon-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .apply-coupon-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .apply-coupon-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .view-offers-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #fff;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
            width: 100%;
        }

        .view-offers-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Applied Coupon Display */
        .applied-coupon {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .applied-coupon::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 60%;
            height: 60%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .applied-coupon-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        .applied-coupon-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .applied-coupon-info i {
            font-size: 28px;
            color: #fff;
        }

        .applied-coupon-details h4 {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 4px 0;
        }

        .applied-coupon-details .savings {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 500;
        }

        .remove-coupon-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-coupon-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Offers Modal */
        .offers-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .offers-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .offers-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 24px 24px 0 0;
            max-height: 80vh;
            z-index: 1051;
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
            display: flex;
            flex-direction: column;
        }

        .offers-overlay.active .offers-panel {
            transform: translateY(0);
        }

        .offers-header {
            padding: 20px 24px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .offers-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: #203A72;
            margin: 0;
        }

        .close-offers-btn {
            background: #f5f5f5;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 18px;
            color: #666;
        }

        .close-offers-btn:hover {
            background: #eee;
        }

        .offers-list {
            padding: 20px 24px;
            overflow-y: auto;
            flex: 1;
        }

        /* Coupon Card */
        .coupon-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
            transition: all 0.3s;
        }

        .coupon-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
        }

        .coupon-card.eligible {
            border-color: #11998e;
            background: linear-gradient(to right, rgba(17, 153, 142, 0.03), rgba(56, 239, 125, 0.03));
        }

        .coupon-card.ineligible {
            opacity: 0.7;
        }

        .coupon-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .coupon-code-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .coupon-card.eligible .coupon-code-badge {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .coupon-discount {
            text-align: right;
        }

        .coupon-discount .amount {
            font-size: 20px;
            font-weight: 700;
            color: #11998e;
        }

        .coupon-discount .label {
            font-size: 12px;
            color: #666;
        }

        .coupon-card-body h4 {
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
            margin: 0 0 8px 0;
        }

        .coupon-card-body p {
            font-size: 14px;
            color: #666;
            margin: 0 0 12px 0;
            line-height: 1.5;
        }

        .coupon-min-order {
            font-size: 13px;
            color: #999;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .coupon-progress {
            margin-top: 12px;
        }

        .coupon-progress-bar {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .coupon-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
            transition: width 0.3s;
        }

        .coupon-progress-text {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .apply-offer-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
        }

        .apply-offer-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .coupon-card.eligible .apply-offer-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        /* Discount Row in Summary */
        .discount-row {
            color: #11998e !important;
        }

        .discount-row .summary-value {
            color: #11998e !important;
        }

        /* Animations */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .shake {
            animation: shake 0.4s ease-in-out;
        }

        @keyframes confetti {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: scale(1) rotate(180deg);
                opacity: 0;
            }
        }

        .confetti-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 9999;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            animation: confetti 0.8s ease-out forwards;
        }

        /* Free Item Styling */
        .free-item {
            border: 2px dashed #11998e !important;
            background: linear-gradient(to right, rgba(17, 153, 142, 0.03), rgba(56, 239, 125, 0.03)) !important;
            position: relative;
            border-radius: 25px;
        }

        .free-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .free-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: #fff;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px rgba(17, 153, 142, 0.3);
        }

        .free-badge i {
            font-size: 16px;
        }

        .free-item-label {
            position: absolute;
            top: 12px;
            right: 12px;
        }

        .free-item .cart-item-price {
            text-decoration: line-through;
            opacity: 0.5;
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
                        <li><a href="{{ route('cart') }}">Cart</a></li>
                        <li><a href="#" class="text-truncate"> Checkout </a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="cart-page">
        <div class="container">

            @if(count($cartItems) > 0)
                <div class="row">
                    <div class="col-lg-8">
                        <div class="cart-items-container">
                            @foreach($cartItems as $item)
                                <div class="cart-item" data-item-id="{{ $item['id'] }}">
                                    <div class="cart-item-image">
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['product']->name }}"
                                            onerror="this.src='{{ asset('no-image-found.jpg') }}'">
                                    </div>
                                    <div class="cart-item-details">
                                        <h3>
                                            <a
                                                href="{{ route('product.detail', ['id' => $item['product_id'], 'slug' => $item['product']->slug]) }}">
                                                {{ $item['product']->name }}
                                            </a>
                                            @if($item['is_bundle'])
                                                <span
                                                    style="font-size: 10px; background: #E3F2FD; color: #1976D2; padding: 2px 8px; border-radius: 4px; font-weight: 500; margin-left: 8px; vertical-align: middle;">BUNDLE</span>
                                            @endif
                                        </h3>
                                        <div class="cart-item-meta">
                                            @if($item['variant'])
                                                <p style="margin: 0;"><strong>Variant:</strong> {{ $item['variant']->name }}</p>
                                            @endif
                                            @if($item['unit'] && $item['unit']->unit)
                                                <p style="margin: 0;"><strong>Unit:</strong> {{ $item['unit']->unit->name }}</p>
                                            @endif
                                            @if($item['product']->brand)
                                                <p style="margin: 0;"><strong>Brand:</strong> {{ $item['product']->brand->name }}</p>
                                            @endif
                                            @if(!empty($item['tax_slab']))
                                                <p style="margin: 0;">
                                                    <strong>Tax rate:</strong>
                                                    {{ rtrim(rtrim(number_format($item['tax_slab']['percentage'], 2, '.', ''), '0'), '.') }}%
                                                </p>
                                            @endif
                                        </div>

                                        {{-- Bundle Items --}}
                                        @if($item['is_bundle'] && !empty($item['bundle_items']))
                                            <div class="bundle-items-container"
                                                style="margin-top: 12px; padding: 12px; background: #F5FAFF; border-radius: 8px; border: 1px solid #E3F2FD;">
                                                <p
                                                    style="font-size: 12px; font-weight: 600; color: #666; margin: 0 0 10px 0; text-transform: uppercase;">
                                                    Bundle Contains:</p>
                                                @foreach($item['bundle_items'] as $bundleItem)
                                                    <div class="bundle-item d-flex align-items-center gap-3 mb-2" style="font-size: 13px;">
                                                        <img src="{{ $bundleItem['image_url'] }}" alt="{{ $bundleItem['product_name'] }}"
                                                            onerror="this.src='{{ asset('no-image-found.jpg') }}'"
                                                            style="width: 36px; height: 36px; object-fit: contain; border-radius: 4px; background: #fff; padding: 2px; border: 1px solid #E0E0E0;">
                                                        <span style="flex: 1; color: #333;">{{ $bundleItem['product_name'] }}</span>
                                                        <span
                                                            style="color: #666; font-weight: 500;">x{{ $bundleItem['quantity'] }}{{ $bundleItem['unit_name'] ? ' ' . $bundleItem['unit_name'] : '' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="cart-item-price">
                                            {{ currency_format($item['total']) }}
                                            <span
                                                style="font-size: 14px; color: #666; font-weight: normal;">({{ currency_format($item['price']) }}
                                                each)</span>
                                        </div>
                                    </div>
                                    <div class="cart-item-actions">
                                        <div class="quantity-group">
                                            <button type="button" class="qty-decrease"
                                                onclick="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})">−</button>
                                            <input type="text" class="qty-input" value="{{ $item['quantity'] }}"
                                                id="qty_{{ $item['id'] }}" readonly>
                                            <button type="button" class="qty-increase"
                                                onclick="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})">+</button>
                                        </div>
                                        <button type="button" class="remove-item-btn" onclick="removeCartItem({{ $item['id'] }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Free Item from Buy X Get Y Promotion --}}
                            @if($freeItem)
                                <div class="cart-item free-item" data-item-id="free-{{ $freeItem['product_id'] }}">
                                    <span class="free-item-label">
                                        <span class="free-badge"><i class="bi bi-gift-fill"></i> FREE</span>
                                    </span>
                                    <div class="cart-item-image">
                                        <img src="{{ $freeItem['image_url'] }}" alt="{{ $freeItem['product_name'] }}"
                                            onerror="this.src='{{ asset('no-image-found.jpg') }}'">
                                    </div>
                                    <div class="cart-item-details">
                                        <h3>
                                            {{ $freeItem['product_name'] }}
                                            <span
                                                style="font-size: 11px; background: #E8F5E9; color: #2E7D32; padding: 2px 8px; border-radius: 4px; font-weight: 500; margin-left: 8px; vertical-align: middle;">With
                                                {{ $freeItem['promotion_code'] }}</span>
                                        </h3>
                                        <div class="cart-item-meta">
                                            @if($freeItem['variant_name'])
                                                <p style="margin: 0;"><strong>Variant:</strong> {{ $freeItem['variant_name'] }}</p>
                                            @endif
                                            @if($freeItem['unit_name'])
                                                <p style="margin: 0;"><strong>Unit:</strong> {{ $freeItem['unit_name'] }}</p>
                                            @endif
                                        </div>
                                        <div class="cart-item-price" style="text-decoration: line-through; opacity: 0.5;">
                                            {{ currency_format($freeItem['price'] * $freeItem['quantity']) }}
                                        </div>
                                        <div style="margin-top: 4px; font-size: 18px; font-weight: 700; color: #11998e;">
                                            FREE
                                        </div>
                                    </div>
                                    <div class="cart-item-actions" style="margin-right:27px;">
                                        <div class="quantity-group" style="opacity: 0.7; pointer-events: none;">
                                            {{-- <button type="button" class="qty-decrease" disabled>−</button> --}}
                                            <input type="text" class="qty-input" value="{{ $freeItem['quantity'] }}" readonly>
                                            {{-- <button type="button" class="qty-increase" disabled>+</button> --}}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        {{-- Checkout Form --}}
                        <div class="checkout-card">
                            <h2><i class="bi bi-person-check me-2"></i>Checkout</h2>
                            <form id="checkoutForm">
                                @csrf

                                {{-- Customer Info --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" id="checkout_name"
                                            value="{{ auth('customer')->check() ? auth('customer')->user()->name : '' }}"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" id="checkout_email"
                                            value="{{ auth('customer')->check() ? auth('customer')->user()->email : '' }}"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="phone" id="checkout_phone"
                                            value="{{ auth('customer')->check() ? auth('customer')->user()->phone_number : '' }}"
                                            required>
                                    </div>
                                </div>

                                @auth('customer')
                                    {{-- Authenticated: Show saved addresses --}}
                                    @if($addresses->count() > 0)
                                        <div class="section-divider"><span>Select Shipping Address</span></div>
                                        <div class="addresses-list mb-3">
                                            @foreach($addresses as $address)
                                                <label class="address-radio-card {{ $loop->first ? 'selected' : '' }}">
                                                    <input type="radio" name="address_id" value="{{ $address->id }}" {{ $loop->first ? 'checked' : '' }}>
                                                    <div class="address-name">{{ $address->name }}</div>
                                                    <div class="address-detail">
                                                        {{ $address->address_line_1 }}
                                                        @if($address->address_line_2), {{ $address->address_line_2 }}@endif<br>
                                                        {{ $address->city?->name }}, {{ $address->state?->name }},
                                                        {{ $address->country?->name }} {{ $address->zipcode }}<br>
                                                        <i class="bi bi-telephone"></i> {{ $address->contact_number }}
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>No saved addresses. <a
                                                href="{{ route('customer.addresses') }}">Add one here</a>.
                                        </div>
                                        {{-- Show manual address fields for authenticated user without addresses --}}
                                        @include('frontend.partials.checkout-address-fields')
                                    @endif
                                @else
                                    {{-- Guest: Show full address form --}}
                                    <div class="section-divider"><span>Shipping Address</span></div>
                                    @include('frontend.partials.checkout-address-fields')
                                @endauth

                                <div class="mb-3">
                                    <label class="form-label">Order Notes (Optional)</label>
                                    <textarea class="form-control" name="notes" rows="2"
                                        placeholder="Any special instructions..."></textarea>
                                </div>

                                <div id="checkoutMessage"></div>
                            </form>
                        </div>

                        {{-- Payment Section --}}
                        <div class="checkout-card">
                            <h2><i class="bi bi-credit-card me-2"></i>Payment</h2>

                            {{-- Payment Method --}}
                            <div class="mb-4">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <div class="payment-method-card"
                                    style="border: 2px solid #E0E0E0; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.3s ease;">
                                    <input type="radio" name="payment_method" value="cash_on_delivery" id="cod_payment" checked
                                        style="width: 20px; height: 20px; accent-color: #203A72; margin-right: 12px;">
                                    <label for="cod_payment"
                                        style="cursor: pointer; margin: 0; font-size: 16px; font-weight: 500; color: #203A72;">
                                        <i class="bi bi-cash-coin me-2"></i>Cash on Delivery
                                    </label>
                                    <p style="margin: 8px 0 0 32px; font-size: 13px; color: #666;">Pay with cash when you
                                        receive your order</p>
                                </div>
                            </div>

                            @auth('customer')
                                {{-- Credit Balance For Authenticated Users --}}
                                @php
                                    $creditBalance = auth('customer')->user()->credit_balance ?? 0;
                                @endphp
                                @if($creditBalance > 0)
                                    <div class="mb-3">
                                        <div
                                            style="background: #F5FAFF; padding: 16px; border-radius: 12px; border: 1px solid #E3F2FD; margin-bottom: 15px;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span style="font-weight: 600; color: #203A72;">Available Credit Balance:</span>
                                                <span style="font-size: 20px; font-weight: 700; color: #28a745;"
                                                    id="availableCredit">{{ currency_format($creditBalance) }}</span>
                                            </div>

                                            <div class="form-check" style="margin-top: 12px;">
                                                <input class="form-check-input" type="checkbox" name="use_credit" id="use_credit"
                                                    value="1" style="width: 20px; height: 20px; accent-color: #203A72; cursor: pointer;"
                                                    onchange="updateOrderTotal()">
                                                <label class="form-check-label" for="use_credit"
                                                    style="font-size: 15px; font-weight: 500; color: #333; cursor: pointer; margin-left: 8px;">
                                                    Use credit balance for this order
                                                </label>
                                            </div>

                                            <div id="creditUsageInfo"
                                                style="display: none; margin-top: 12px; padding: 12px; background: #fff; border-radius: 8px; border-left: 4px solid #28a745;">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span style="color: #666;">Credit Applied:</span>
                                                    <span style="font-weight: 600; color: #28a745;" id="creditApplied">$0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span style="color: #666;">Remaining Balance After Order:</span>
                                                    <span style="font-weight: 600; color: #203A72;"
                                                        id="remainingCredit">{{ currency_format($creditBalance) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info" style="font-size: 14px;">
                                        <i class="bi bi-info-circle me-2"></i><strong>Credit Balance:</strong> $0.00
                                    </div>
                                @endif
                            @endauth
                        </div>

                        {{-- Coupon Section --}}
                        @if($appliedCoupon)
                            {{-- Applied Coupon Display --}}
                            <div class="applied-coupon" id="appliedCouponSection">
                                <div class="applied-coupon-content">
                                    <div class="applied-coupon-info">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <div class="applied-coupon-details">
                                            <h4>{{ $appliedCoupon->code }} Applied!</h4>
                                            <span class="savings">You're saving {{ currency_format($discountAmount) }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="remove-coupon-btn" onclick="removeCoupon()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Coupon Input Section --}}
                            <div class="coupon-section" id="couponSection">
                                <div class="coupon-header">
                                    <i class="bi bi-ticket-perforated-fill"></i>
                                    <h3>Apply Coupon</h3>
                                </div>
                                <div class="coupon-input-group">
                                    <input type="text" id="couponCodeInput" placeholder="Enter coupon code" maxlength="50">
                                    <button type="button" class="apply-coupon-btn" id="applyCouponBtn" onclick="applyCouponCode()">
                                        Apply
                                    </button>
                                </div>
                                @if(count($availableCoupons) > 0)
                                    <button type="button" class="view-offers-btn" onclick="openOffersPanel()">
                                        <i class="bi bi-gift"></i>
                                        View {{ count($availableCoupons) }} Available Offer{{ count($availableCoupons) > 1 ? 's' : '' }}
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                @endif
                            </div>
                        @endif

                        {{-- Order Summary --}}
                        <div class="cart-summary">
                            <h2>Order Summary</h2>
                            <div class="summary-row">
                                <span class="summary-label">Subtotal</span>
                                <span class="summary-value" id="cartSubtotal">{{ currency_format($subtotal) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Tax</span>
                                <span class="summary-value" id="cartTax">{{ currency_format($taxTotal ?? 0) }}</span>
                            </div>
                            @if($discountAmount > 0)
                                <div class="summary-row discount-row" id="discountRow">
                                    <span class="summary-label"><i class="bi bi-tag-fill me-1"></i>Coupon Discount</span>
                                    <span class="summary-value">-{{ currency_format($discountAmount) }}</span>
                                </div>
                            @endif
                            <div class="summary-row">
                                <span class="summary-label">Shipping</span>
                                <span class="summary-value">Calculated at checkout</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Total</span>
                                <span class="summary-value"
                                    id="cartTotal">{{ currency_format(($grandTotal ?? $subtotal) - $discountAmount) }}</span>
                            </div>
                            <button type="button" class="btn cart-btn-css checkout-btn" id="placeOrderBtn"
                                onclick="placeOrder()">
                                <i class="bi bi-lock me-2"></i>Place Order
                            </button>
                            <a href="{{ route('products') }}" class="btn btn-outline-secondary d-block text-center mt-3"
                                style="padding: 12px; border-radius: 8px;">
                                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="cart-items-container">
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="bi bi-cart-x"></i>
                        </div>
                        <h2>Your cart is empty</h2>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="{{ route('products') }}" class="btn cart-btn-css"
                            style="padding: 14px 30px; border-radius: 8px;">
                            <i class="bi bi-arrow-left me-2"></i>Start Shopping
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    {{-- Offers Panel Modal --}}
    <div class="offers-overlay" id="offersOverlay" onclick="closeOffersPanel(event)">
        <div class="offers-panel" onclick="event.stopPropagation()">
            <div class="offers-header">
                <h3><i class="bi bi-gift me-2"></i>Available Offers</h3>
                <button type="button" class="close-offers-btn" onclick="closeOffersPanel()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="offers-list" id="offersList">
                @foreach($availableCoupons ?? [] as $coupon)
                    <div class="coupon-card {{ $coupon['is_eligible'] ? 'eligible' : 'ineligible' }}">
                        <div class="coupon-card-header">
                            <span class="coupon-code-badge">{{ $coupon['code'] }}</span>
                            <div class="coupon-discount">
                                @if($coupon['is_eligible'] && $coupon['potential_discount'] > 0)
                                    <span class="amount">Save {{ currency_format($coupon['potential_discount']) }}</span>
                                @else
                                    <span
                                        class="amount">{{ $coupon['discount_type'] ? currency_format($coupon['discount_amount']) : $coupon['discount_amount'] . '%' }}
                                        OFF</span>
                                @endif
                            </div>
                        </div>
                        <div class="coupon-card-body">
                            <h4>{{ $coupon['name'] }}</h4>
                            <p>{{ $coupon['description'] ?: $coupon['type_label'] }}</p>
                            @if($coupon['cart_minimum_amount'])
                                <span class="coupon-min-order">
                                    <i class="bi bi-info-circle"></i>
                                    Min. order: {{ currency_format($coupon['cart_minimum_amount']) }}
                                </span>
                            @endif
                            @if(!$coupon['is_eligible'])
                                <div class="coupon-progress">
                                    <div class="coupon-progress-bar">
                                        <div class="coupon-progress-fill" style="width: {{ $coupon['progress'] }}%"></div>
                                    </div>
                                    <span class="coupon-progress-text">{{ $coupon['eligibility_message'] }}</span>
                                </div>
                            @else
                                <button type="button" class="apply-offer-btn" onclick="applyOfferCode('{{ $coupon['code'] }}')">
                                    <i class="bi bi-check2 me-1"></i>Apply This Offer
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
                @if(empty($availableCoupons))
                    <div class="text-center py-5">
                        <i class="bi bi-ticket-perforated" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-3" style="color: #666;">No offers available at the moment</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <script>
        // Coupon Functions
        function applyCouponCode() {
            const input = document.getElementById('couponCodeInput');
            const btn = document.getElementById('applyCouponBtn');
            const code = input.value.trim();

            if (!code) {
                input.classList.add('shake');
                setTimeout(() => input.classList.remove('shake'), 400);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

            fetch('{{ route("api.cart.apply-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code: code }),
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showConfetti();
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        input.classList.add('shake');
                        setTimeout(() => input.classList.remove('shake'), 400);
                        alert(data.message || 'Failed to apply coupon');
                        btn.disabled = false;
                        btn.innerHTML = 'Apply';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to apply coupon');
                    btn.disabled = false;
                    btn.innerHTML = 'Apply';
                });
        }

        function applyOfferCode(code) {
            document.getElementById('couponCodeInput').value = code;
            closeOffersPanel();
            setTimeout(() => applyCouponCode(), 300);
        }

        function removeCoupon() {
            if (!confirm('Remove this coupon?')) return;

            fetch('{{ route("api.cart.remove-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to remove coupon');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove coupon');
                });
        }

        function openOffersPanel() {
            document.getElementById('offersOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeOffersPanel(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('offersOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        function showConfetti() {
            const colors = ['#667eea', '#764ba2', '#11998e', '#38ef7d', '#ff6b6b', '#ffd93d'];
            const container = document.createElement('div');
            container.className = 'confetti-container';
            document.body.appendChild(container);

            for (let i = 0; i < 30; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = (Math.random() - 0.5) * 200 + 'px';
                confetti.style.top = (Math.random() - 0.5) * 200 + 'px';
                confetti.style.animationDelay = Math.random() * 0.3 + 's';
                container.appendChild(confetti);
            }

            setTimeout(() => container.remove(), 1000);
        }

        // Enter key to apply coupon
        document.getElementById('couponCodeInput')?.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCouponCode();
            }
        });

    </script>

    <script>
        function updateCartQuantity(itemId, newQuantity) {
            if (newQuantity < 1) {
                removeCartItem(itemId);
                return;
            }

            fetch('{{ route("api.cart.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: itemId,
                    quantity: newQuantity
                }),
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('qty_' + itemId).value = newQuantity;
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to update quantity');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update quantity');
                });
        }

        function removeCartItem(itemId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            fetch('{{ route("api.cart.remove") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: itemId
                }),
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                        if (itemElement) {
                            itemElement.remove();
                        }
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to remove item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove item');
                });
        }

        // Address selection highlight
        $(document).ready(function () {
            $('.address-radio-card input[type="radio"]').on('change', function () {
                $('.address-radio-card').removeClass('selected');
                $(this).closest('.address-radio-card').addClass('selected');
            });

            // Initialize Select2 for guest address fields
            @guest('customer')
                initAddressDropdowns();
            @endguest

            @auth('customer')
                @if($addresses->count() == 0)
                    initAddressDropdowns();
                @endif
            @endauth
                        });

        function initAddressDropdowns() {
            $('#guest_country_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Country',
                allowClear: true
            });

            $('#guest_state_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select State',
                allowClear: true,
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            country_id: $('#guest_country_id').val(),
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.items,
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });

            $('#guest_city_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select City',
                allowClear: true,
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            state_id: $('#guest_state_id').val(),
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.items,
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });

            $('#guest_country_id').on('change', function () {
                $('#guest_state_id').val(null).trigger('change');
                $('#guest_city_id').val(null).trigger('change');
            });
        }

        // Update order total with credit
        function updateOrderTotal() {
            const useCredit = document.getElementById('use_credit');
            const creditUsageInfo = document.getElementById('creditUsageInfo');

            if (!useCredit) return;

            const currentTotalBeforeCredit = {{ ($grandTotal ?? $subtotal) - $discountAmount }};
            const availableCredit = {{ auth('customer')->check() ? (auth('customer')->user()->credit_balance ?? 0) : 0 }};

            if (useCredit.checked && availableCredit > 0) {
                // Calculate credit to apply
                const creditToApply = Math.min(availableCredit, currentTotalBeforeCredit);
                const remainingCredit = availableCredit - creditToApply;
                const finalTotal = currentTotalBeforeCredit - creditToApply;

                // Update display
                document.getElementById('creditApplied').textContent = window.formatCurrency(creditToApply);
                document.getElementById('remainingCredit').textContent = window.formatCurrency(remainingCredit);
                document.getElementById('cartTotal').textContent = window.formatCurrency(finalTotal);

                // Show credit usage info
                creditUsageInfo.style.display = 'block';
            } else {
                // Reset to original total
                document.getElementById('cartTotal').textContent = window.formatCurrency(currentTotalBeforeCredit);
                creditUsageInfo.style.display = 'none';
            }
        }

        function placeOrder() {
            const form = document.getElementById('checkoutForm');
            const formData = new FormData(form);

            // Validate required fields
            const name = formData.get('name');
            const email = formData.get('email');
            const phone = formData.get('phone');

            if (!name || !email || !phone) {
                $('#checkoutMessage').html('<div class="alert alert-danger">Please fill in all required fields.</div>');
                return;
            }

            // Check address selection or guest fields
            const addressId = formData.get('address_id');
            const isGuest = {{ auth('customer')->check() ? 'false' : 'true' }};
            const hasNoAddresses = {{ auth('customer')->check() && $addresses->count() == 0 ? 'true' : 'false' }};

            if (!addressId && (isGuest || hasNoAddresses)) {
                const addressLine1 = formData.get('address_line_1');
                const countryId = formData.get('country_id');
                const stateId = formData.get('state_id');
                const zipcode = formData.get('zipcode');

                if (!addressLine1 || !countryId || !stateId || !zipcode) {
                    $('#checkoutMessage').html('<div class="alert alert-danger">Please fill in all address fields.</div>');
                    return;
                }
            }

            const btn = $('#placeOrderBtn');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Processing...');
            $('#checkoutMessage').html('');

            $.ajax({
                url: "{{ route('checkout.place-order') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success) {
                        $('#checkoutMessage').html('<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + response.message + '</div>');
                        setTimeout(function () {
                            window.location.href = response.redirect || '{{ route("home") }}';
                        }, 2000);
                    } else {
                        $('#checkoutMessage').html('<div class="alert alert-danger">' + (response.message || 'Failed to place order.') + '</div>');
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function (xhr) {
                    let errorMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    $('#checkoutMessage').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    btn.prop('disabled', false).html(originalText);
                }
            });
        }
    </script>
@endpush