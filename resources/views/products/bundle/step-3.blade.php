@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@push('product-css')
    <style>
        .product-preview-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .product-image-placeholder {
            width: 100%;
            height: 300px;
            background: #f5f5f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
        }

        .product-image-placeholder img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .product-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .product-meta {
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }

        .product-meta strong {
            color: #333;
            margin-right: 5px;
        }

        .product-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }

        .tag-pill {
            padding: 6px 14px;
            background: #f0f0f0;
            border-radius: 20px;
            font-size: 13px;
            color: #333;
            border: 1px solid #e0e0e0;
        }

        .info-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            height: 100%;
        }

        .info-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .info-item {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-item-label {
            font-weight: 600;
            color: #333;
            margin-right: 8px;
        }

        .info-item-value {
            color: #666;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .bundle-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .bundle-items-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        .bundle-items-table td {
            padding: 12px;
            font-size: 13px;
            color: #666;
            border-bottom: 1px solid #f0f0f0;
        }

        .bundle-items-table tr:hover {
            background: #f8f9fa;
        }

        .type-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .type-badge.simple {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-badge.variable {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .pricing-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }

        .pricing-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #e0e0e0;
        }

        .pricing-row:last-child {
            border-bottom: none;
        }

        .pricing-row.total {
            font-weight: 700;
            font-size: 18px;
            color: #007bff;
            padding-top: 12px;
            margin-top: 8px;
            border-top: 2px solid #007bff;
            border-bottom: none;
        }

        .pricing-label {
            color: #666;
        }

        .pricing-value {
            color: #333;
            font-weight: 500;
        }

        .pricing-mode-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 10px;
        }

        .pricing-mode-sum {
            background: #dbeafe;
            color: #1e40af;
        }

        .pricing-mode-fixed {
            background: #fef3c7;
            color: #92400e;
        }

        .discount-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            background: #dcfce7;
            color: #166534;
        }
    </style>
@endpush

@section('product-content')
    @php
        $productTags = $product->tags;

        $bundleItemsCount = $bundle && $bundle->items ? $bundle->items->count() : 0;

        $sumTotal = 0;
        if ($bundle && $bundle->items) {
            foreach ($bundle->items as $item) {
                $productUnit = \App\Models\AwProductUnit::find($item->unit_id);
                if ($productUnit) {
                    $priceQuery = \App\Models\AwPrice::where('product_id', $item->product_id)
                        ->where('unit_id', $item->unit_id);

                    if ($item->variant_id) {
                        $priceQuery->where('variant_id', $item->variant_id);
                    } else {
                        $priceQuery->whereNull('variant_id');
                    }

                    $price = $priceQuery->first();
                    if ($price) {
                        $sumTotal += ($price->base_price * $item->quantity);
                    }
                }
            }
        }

        $finalPrice = $bundle->total;
    @endphp

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="product-preview-card">
                    <div class="product-image-placeholder">
                        @if($mainImage)
                            <img src="{{ asset('storage/' . $mainImage->image_path) }}" alt="{{ $product->name }}">
                        @else
                            <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="1">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        @endif
                    </div>
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-meta">
                        <strong>Brand:</strong> {{ $product->brand->name ?? 'N/A' }}
                    </div>
                    <div class="product-meta">
                        <strong>Type:</strong> {{ ucfirst($product->product_type) }}
                    </div>
                    <div class="product-meta">
                        <strong>Status:</strong>
                        <span class="status-badge status-{{ $product->status }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                    <div class="product-meta">
                        <strong>Bundle Items:</strong> {{ $bundleItemsCount }} products
                    </div>
                    @if($productTags->count() > 0)
                        <div class="product-tags">
                            @foreach($productTags->take(3) as $tag)
                                <span class="tag-pill">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-8">
                <div class="info-card">
                    <div class="info-card-header">
                        <h4 class="info-card-title">Basic Information</h4>
                        <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt(1), 'id' => encrypt($product->id)]) }}"
                            class="btn btn-primary">Edit</a>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Product Name:</span>
                        <span class="info-item-value">{{ $product->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Brand:</span>
                        <span class="info-item-value">{{ $product->brand->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Product Type:</span>
                        <span class="info-item-value">{{ ucfirst($product->product_type) }} Product</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Status:</span>
                        <span class="status-badge status-{{ $product->status }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Short Description:</span>
                        <span
                            class="info-item-value">{!! \Illuminate\Support\Str::limit(strip_tags($product->short_description ?? ''), 150) !!}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="info-card">
                    <div class="info-card-header">
                        <h4 class="info-card-title">Bundle Items ({{ $bundleItemsCount }})</h4>
                        <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt(2), 'id' => encrypt($product->id)]) }}"
                            class="btn btn-primary">Edit</a>
                    </div>
                    @if($bundle && $bundle->items && $bundle->items->count() > 0)
                        <table class="bundle-items-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bundle->items as $item)
                                    @php
                                        $itemProduct = $item->product;
                                        $itemVariant = $item->variant;
                                        $itemUnit = $item->unit;

                                        $displayName = $itemProduct->name ?? 'Unknown Product';
                                        if ($itemVariant) {
                                            $attrNames = $itemVariant->attributes->pluck('value')->implode(' / ');
                                            $displayName = $itemProduct->name . ' - ' . $attrNames;
                                        }

                                        $unitName = 'N/A';
                                        if ($itemUnit && $itemUnit->unit) {
                                            $unitName = $itemUnit->unit->name;
                                        }

                                        $priceQuery = \App\Models\AwPrice::where('product_id', $item->product_id)
                                            ->where('unit_id', $item->unit_id);

                                        if ($item->variant_id) {
                                            $priceQuery->where('variant_id', $item->variant_id);
                                        } else {
                                            $priceQuery->whereNull('variant_id');
                                        }

                                        $price = $priceQuery->first();
                                        $unitPrice = $price ? $price->base_price : 0;
                                        $subtotal = $unitPrice * $item->quantity;

                                        $typeClass = $item->variant_id ? 'variable' : 'simple';
                                        $typeName = $item->variant_id ? 'Variable' : 'Simple';
                                    @endphp
                                    <tr>
                                        <td><span class="type-badge {{ $typeClass }}">{{ $typeName }}</span></td>
                                        <td>{{ $displayName }}</td>
                                        <td>{{ $itemProduct->sku ?? 'N/A' }}</td>
                                        <td>{{ $unitName }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($unitPrice, 2) }}</td>
                                        <td><strong>${{ number_format($subtotal, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="info-item-value">No bundle items configured</div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="info-card-header">
                        <h4 class="info-card-title">Bundle Pricing</h4>
                        <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt(2), 'id' => encrypt($product->id)]) }}"
                            class="btn btn-primary">Edit</a>
                    </div>
                    @if($bundle)
                        @if($bundle->pricing_mode === 'fixed')
                            <span class="pricing-mode-badge pricing-mode-fixed">Fixed Bundle Price</span>
                        @else
                            <span class="pricing-mode-badge pricing-mode-sum">Sum of Product Prices</span>
                        @endif
                        <div class="pricing-summary">
                            <div class="pricing-row">
                                <span class="pricing-label">Products Subtotal:</span>
                                <span class="pricing-value">${{ number_format($sumTotal, 2) }}</span>
                            </div>
                            @if($bundle->pricing_mode === 'fixed')
                                <div class="pricing-row">
                                    <span class="pricing-label">Fixed Bundle Price:</span>
                                    <span class="pricing-value">${{ number_format($bundle->discount_value ?? 0, 2) }}</span>
                                </div>
                            @else
                                @if($bundle->discount_type && $bundle->discount_value > 0)
                                    <div class="pricing-row">
                                        <span class="pricing-label">
                                            Discount
                                            <span class="discount-badge">
                                                @if($bundle->discount_type === 'percentage')
                                                    {{ $bundle->discount_value }}%
                                                @else
                                                    Fixed
                                                @endif
                                            </span>
                                        </span>
                                        <span class="pricing-value" style="color: #dc3545;">
                                            @if($bundle->discount_type === 'percentage')
                                                -${{ number_format($sumTotal * $bundle->discount_value / 100, 2) }}
                                            @else
                                                -${{ number_format($bundle->discount_value, 2) }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            @endif
                            <div class="pricing-row total">
                                <span class="pricing-label">Final Price:</span>
                                <span class="pricing-value">${{ number_format($finalPrice, 2) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="info-item-value">No pricing configured</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('product-js')
    <script>

    </script>
@endpush