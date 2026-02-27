@extends('frontend.layouts.app')

@push('css')
    <style>
        .products-page {
            padding: 0;
            background-color: #F5FAFF;
        }

        .bred-pro {
            background: #EEEEEE;
            padding: 20px 0;
        }

        .mn-filter-block {
            padding: 50px 0;
        }

        .flter-left {
            padding: 25px;
            border: 1px solid #D9D9D9;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .flter-left::-webkit-scrollbar {
            width: 6px;
        }

        .flter-left::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .flter-left::-webkit-scrollbar-thumb {
            background: #203A72;
            border-radius: 10px;
        }

        .head-filetr {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F5F5F5;
        }

        .head-filetr .h-20 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 22px;
            font-weight: 600;
            color: #203A72;
        }

        .head-filetr .h-20 a {
            font-size: 16px;
            color: #203A72;
            text-decoration: none;
            font-weight: normal;
            transition: all 0.3s ease;
        }

        .head-filetr .h-20 a:hover {
            color: #1a2d5a;
            text-decoration: underline;
        }

        .form-section {
            padding-top: 0;
        }

        .inbx-fill {
            margin-bottom: 30px;
        }

        .inbx-fill .h-20 {
            font-size: 18px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .inbx-fill .h-20 i {
            font-size: 16px;
        }

        .chek-box {
            max-height: 250px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .chek-box::-webkit-scrollbar {
            width: 4px;
        }

        .chek-box::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chek-box::-webkit-scrollbar-thumb {
            background: #9CADC0;
            border-radius: 10px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 12px 0;
            padding: 8px;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .form-check:hover {
            background-color: #F5FAFF;
        }

        .form-check-label {
            font-size: 16px;
            color: #333;
            cursor: pointer;
            flex: 1;
        }

        .form-check-input[type="checkbox"],
        .form-check-input[type="radio"] {
            width: 20px;
            height: 20px;
            border: 2px solid #D9D9D9;
            border-radius: 4px;
            cursor: pointer;
            flex-shrink: 0;
            margin: 0;
        }

        .form-check-input[type="radio"] {
            border-radius: 50%;
        }

        .form-check-input:checked {
            background-color: #203A72;
            border-color: #203A72;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(32, 58, 114, 0.1);
        }

        .radio-grip .form-check {
            margin: 10px 0;
        }

        .price-range-inputs {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .price-range-inputs input {
            flex: 1;
            padding: 10px;
            border: 1px solid #D9D9D9;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
        }

        .price-range-inputs input:focus {
            border-color: #203A72;
            box-shadow: 0 0 0 3px rgba(32, 58, 114, 0.1);
        }

        .right__box {
            padding-left: 20px;
        }

        .filt_Rhead {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .f-hd h2.h-30 {
            font-size: 30px;
            color: #203A72;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .f-hd .p-20 {
            font-size: 18px;
            color: #666;
        }

        .sort-R {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-box {
            font-size: 18px;
        }

        .sort-box label {
            color: #203A72;
            font-weight: 500;
            margin: 0;
        }

        .sort-select {
            width: 180px;
            background-color: #F5F5F5;
            border: 1px solid #D9D9D9;
            border-radius: 6px;
            font-size: 16px;
            color: #000;
            padding: 10px 15px;
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
        }

        .sort-select:focus {
            border-color: #203A72;
            box-shadow: 0 0 0 3px rgba(32, 58, 114, 0.1);
            background-color: #fff;
        }

        .product-boxes {
            padding: 0;
        }

        .pro-inbox {
            margin-bottom: 30px;
            background: #fff;
            border: 1px solid #EEEEEE;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pro-inbox:hover {
            box-shadow: 0 4px 12px rgba(32, 58, 114, 0.15);
            transform: translateY(-3px);
        }

        .produc-imgbx {
            position: relative;
            width: 100%;
            height: 250px;
            background: #F5FAFF;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .produc-imgbx img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .pro-inbox:hover .produc-imgbx img {
            transform: scale(1.05);
        }

        .btn.new-btn {
            position: absolute;
            left: 15px;
            top: 15px;
            background: #9CADC0;
            color: #fff;
            font-size: 14px;
            padding: 6px 20px;
            text-transform: uppercase;
            border-radius: 4px;
            font-weight: 600;
            z-index: 2;
        }

        .proctinbx {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .proctinbx h3.h-20 {
            font-size: 20px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 12px;
            line-height: 1.4;
            min-height: 56px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .proctinbx .text-offer {
            text-decoration: line-through;
            color: #837E7E;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .proctinbx h4.h-20 {
            font-size: 22px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 15px;
        }

        .proctinbx .d-flex {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .proctinbx .p-18 {
            font-size: 16px;
            color: #666;
            margin: 0;
        }

        .bulk-pr.btn {
            background: #9CADC0;
            font-size: 14px;
            color: #fff;
            padding: 6px 15px;
            border-radius: 4px;
            font-weight: 500;
        }

        .bulk-rd.btn {
            background: #D30606;
            font-size: 14px;
            color: #fff;
            padding: 6px 15px;
            border-radius: 4px;
            font-weight: 500;
        }

        .btn.cart-btn.d-block {
            background-color: #203A72 !important;
            color: #fff !important;
            font-weight: 600;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: auto;
        }

        .btn.cart-btn.d-block:hover {
            background-color: #1a2d5a !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(32, 58, 114, 0.2);
        }

        .product-pegination {
            padding-top: 50px;
            padding-bottom: 20px;
        }

        .filter-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 15px;
        }

        .filter-section-title i {
            font-size: 16px;
        }

        .stock-filter {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            background: #F5FAFF;
            border: 1px solid #D9D9D9;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .stock-badge:hover {
            background: #E8F4FF;
            border-color: #203A72;
        }

        .stock-badge.active {
            background: #203A72;
            color: #fff;
            border-color: #203A72;
        }

        .stock-badge i {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .right__box {
                padding-left: 0;
                margin-top: 30px;
            }

            .flter-left {
                position: relative;
                max-height: none;
            }

            .filt_Rhead {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="products-page">
        <!-- Breadcrumb Section -->
        <section>
            <div class="bred-pro">
                <div class="container">
                    <div class="breadcrumb-container">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li><a href="{{ route('categories') }}">Categories</a></li>
                            @if($selectedCategory)
                                <li class="active">{{ $selectedCategory->name }}</li>
                            @else
                                <li class="active">All Products</li>
                            @endif
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Filter Section -->
        <section class="mn-filter">
            <div class="mn-filter-block">
                <div class="container">
                    <form method="GET" action="{{ route('products') }}" id="productFilterForm">
                        <div class="row">
                            <!-- Filter Sidebar -->
                            <div class="col-md-6 col-lg-5 col-sm-6 col-xl-3">
                                <div class="flter-left">
                                    <div class="head-filetr">
                                        <h3 class="h-20">
                                            <span><i class="bi bi-funnel"></i> Filters</span>
                                            <a href="{{ route('products') }}" id="clearFilters">
                                                <i class="bi bi-x-circle"></i> Clear All
                                            </a>
                                        </h3>
                                    </div>
                                    <div class="form-section">
                                        <!-- Category Filter -->
                                        <div class="inbx-fill">
                                            <h3 class="h-20">
                                                <i class="bi bi-grid-3x3-gap"></i>
                                                Categories
                                            </h3>
                                            <div class="chek-box">
                                                @foreach($categories as $category)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="category"
                                                            value="{{ $category->id }}" id="category_{{ $category->id }}" {{ $categoryId == $category->id ? 'checked' : '' }}
                                                            onchange="this.form.submit()">
                                                        <label class="form-check-label" for="category_{{ $category->id }}">
                                                            {{ $category->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Brand Filter -->
                                        @if($brands->count() > 0)
                                            <div class="inbx-fill">
                                                <h3 class="h-20">
                                                    <i class="bi bi-tag"></i>
                                                    Brands
                                                </h3>
                                                <div class="chek-box">
                                                    @foreach($brands as $brand)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="brands[]"
                                                                value="{{ $brand->id }}" id="brand_{{ $brand->id }}" {{ in_array($brand->id, (array) $brandIds) ? 'checked' : '' }}
                                                                onchange="this.form.submit()">
                                                            <label class="form-check-label" for="brand_{{ $brand->id }}">
                                                                {{ $brand->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Attribute Filters (for variable products) -->
                                        @if(isset($attributes) && $attributes->count() > 0)
                                            @foreach($attributes as $attribute)
                                                <div class="inbx-fill">
                                                    <h3 class="h-20">
                                                        <i class="bi bi-tags"></i>
                                                        {{ $attribute->name }}
                                                    </h3>
                                                    <div class="chek-box">
                                                        @foreach($attribute->values as $value)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="attributes[]"
                                                                    value="{{ $value->id }}"
                                                                    id="attr_{{ $attribute->id }}_{{ $value->id }}" {{ in_array($value->id, (array) ($attributeValueIds ?? [])) ? 'checked' : '' }} onchange="this.form.submit()">
                                                                <label class="form-check-label"
                                                                    for="attr_{{ $attribute->id }}_{{ $value->id }}">
                                                                    {{ $value->value }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif

                                        <!-- Price Range Filter -->
                                        <div class="inbx-fill">
                                            <h3 class="h-20">
                                                <i class="bi bi-currency-dollar"></i>
                                                Price Range
                                            </h3>
                                            <div class="chek-box radio-grip">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="price_range"
                                                        value="under_50" id="price_under_50" {{ $priceRange == 'under_50' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="form-check-label" for="price_under_50">
                                                        Under $50
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="price_range"
                                                        value="50_100" id="price_50_100" {{ $priceRange == '50_100' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="form-check-label" for="price_50_100">
                                                        $50 - $100
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="price_range"
                                                        value="100_200" id="price_100_200" {{ $priceRange == '100_200' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="form-check-label" for="price_100_200">
                                                        $100 - $200
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="price_range"
                                                        value="200_500" id="price_200_500" {{ $priceRange == '200_500' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="form-check-label" for="price_200_500">
                                                        $200 - $500
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="price_range"
                                                        value="above_500" id="price_above_500" {{ $priceRange == 'above_500' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="form-check-label" for="price_above_500">
                                                        Above $500
                                                    </label>
                                                </div>
                                            </div>
                                            @if($priceStats)
                                                <div class="price-range-inputs">
                                                    <input type="number" name="min_price" placeholder="Min"
                                                        value="{{ $minPrice }}" min="0" step="0.01">
                                                    <input type="number" name="max_price" placeholder="Max"
                                                        value="{{ $maxPrice }}" min="0" step="0.01">
                                                    <button type="submit" class="btn cart-btn" style="padding: 10px 15px;">
                                                        <i class="bi bi-search"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Stock Status Filter -->
                                        <div class="inbx-fill">
                                            <h3 class="h-20">
                                                <i class="bi bi-box-seam"></i>
                                                Stock Status
                                            </h3>
                                            <div class="stock-filter">
                                                <div class="stock-badge {{ $inStock == '1' ? 'active' : '' }}"
                                                    onclick="toggleStockFilter('1')">
                                                    <i class="bi bi-check-circle"></i>
                                                    <span>In Stock</span>
                                                </div>
                                                <div class="stock-badge {{ $inStock == '0' ? 'active' : '' }}"
                                                    onclick="toggleStockFilter('0')">
                                                    <i class="bi bi-x-circle"></i>
                                                    <span>Out of Stock</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="in_stock" id="in_stock_input" value="{{ $inStock }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Products Grid -->
                            <div class="col-lg-7 col-sm-12 col-md-6 col-xl-9">
                                <div class="right__box">
                                    <div class="filt_Rhead">
                                        <div class="f-hd">
                                            <h2 class="h-30">
                                                {{ $selectedCategory ? $selectedCategory->name : 'All Products' }}
                                            </h2>
                                            <p class="p-20">
                                                Showing
                                                <span>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</span>
                                                of {{ $products->total() }} results
                                            </p>
                                        </div>
                                        <div class="sort-R">
                                            <div class="d-flex align-items-center sort-box">
                                                <label for="sortSelect" class="me-2 mb-0">Sort by:</label>
                                                <select class="form-select sort-select" id="sortSelect" name="sort"
                                                    onchange="this.form.submit()">
                                                    <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>A-Z
                                                    </option>
                                                    <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>Z-A
                                                    </option>
                                                    <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>
                                                        Price: Low to High</option>
                                                    <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>
                                                        Price: High to Low</option>
                                                    <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Newest
                                                    </option>
                                                    <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Oldest
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if($products->count() > 0)
                                        <div class="product-boxes">
                                            <div class="row">
                                                @foreach($products as $product)
                                                    @php
                                                        $primaryImage = $product->primaryImage;
                                                        $imageUrl = $primaryImage ? asset('storage/' . $primaryImage->image_path) : asset('assets/images/default-product.png');

                                                        // Get base price (lowest price for simple products, or first variant for variable)
                                                        if ($product->product_type == 'simple') {
                                                            $basePrice = $product->prices()
                                                                ->whereNull('variant_id')
                                                                ->whereNull('deleted_at')
                                                                ->min('base_price');
                                                        } else {
                                                            // For variable products, get the lowest price from variants
                                                            $basePrice = $product->prices()
                                                                ->whereNull('deleted_at')
                                                                ->min('base_price');
                                                        }

                                                        // Check stock status
                                                        $totalStock = $product->supplierWarehouseProducts()
                                                            ->whereNull('deleted_at')
                                                            ->sum('quantity');
                                                        $isInStock = $totalStock > 0;
                                                        $isLowStock = $totalStock > 0 && $totalStock <= 10;

                                                        // Get min order quantity from price tiers or default
                                                        $minOrder = 1;
                                                        $priceRecord = $product->prices()
                                                            ->whereNull('deleted_at')
                                                            ->first();
                                                        if ($priceRecord && $priceRecord->pricing_type == 'tiered') {
                                                            $minTier = $priceRecord->tiers()
                                                                ->whereNull('deleted_at')
                                                                ->orderBy('min_qty')
                                                                ->first();
                                                            $minOrder = $minTier ? $minTier->min_qty : 1;
                                                        }
                                                    @endphp
                                                    <div class="col-lg-6 col-md-12 col-xl-6 col-xxl-4 col-sm-6">
                                                        <div class="pro-inbox">
                                                            <div class="produc-imgbx">
                                                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('no-image-found.jpg') }}'">
                                                                @if($product->created_at->gt(now()->subDays(30)))
                                                                    <a href="#" class="btn new-btn">NEW</a>
                                                                @endif
                                                            </div>
                                                            <div class="proctinbx">
                                                                <a href="{{ route('product.detail', ['id' => $product->id, 'slug' => $product->slug]) }}"
                                                                    style="text-decoration: none; color: inherit;">
                                                                    <h3 class="h-20 mb-3"
                                                                        style="cursor: pointer; transition: color 0.3s;"
                                                                        onmouseover="this.style.color='#203A72'"
                                                                        onmouseout="this.style.color='#203A72'">{{ $product->name }}
                                                                    </h3>
                                                                </a>
                                                                @if($basePrice)
                                                                    <p><span
                                                                            class="text-offer">{{ currency_format($basePrice * 1.2) }}</span>
                                                                    </p>
                                                                    <h4 class="h-20">{{ currency_format($basePrice) }}</h4>
                                                                @else
                                                                    <h4 class="h-20">Price on request</h4>
                                                                @endif
                                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                                    <p class="p-18">Min Order: {{ $minOrder }}
                                                                        {{ $minOrder == 1 ? 'unit' : 'units' }}</p>
                                                                    @if($isInStock)
                                                                        @if($isLowStock)
                                                                            <span class="bulk-rd btn">{{ $totalStock }} Remaining</span>
                                                                        @else
                                                                            <span class="bulk-pr btn">In Stock</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="bulk-rd btn">Out of Stock</span>
                                                                    @endif
                                                                </div>
                                                                <div class="product-cart-actions mt-3"
                                                                    data-product-id="{{ $product->id }}"
                                                                    data-product-type="{{ $product->product_type }}">
                                                                    <div class="add-to-cart-section">
                                                                        <button type="button"
                                                                            class="btn cart-btn d-block w-100 add-to-cart-btn"
                                                                            style="padding: 12px; border-radius: 8px;">
                                                                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                                                        </button>
                                                                    </div>
                                                                    <div class="quantity-stepper-section" style="display: none;">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div class="input-group"
                                                                                style="flex: 1; max-width: 150px;">
                                                                                <button
                                                                                    class="btn btn-outline-secondary btn-sm qty-decrease"
                                                                                    type="button"
                                                                                    style="width: 36px; height: 36px; border-color: #D9D9D9; background: #F5F5F5;">−</button>
                                                                                <input type="text"
                                                                                    class="form-control text-center qty-input"
                                                                                    value="1" readonly
                                                                                    style="border: 1px solid #D9D9D9; height: 36px; font-weight: 600;">
                                                                                <button
                                                                                    class="btn btn-outline-secondary btn-sm qty-increase"
                                                                                    type="button"
                                                                                    style="width: 36px; height: 36px; border-color: #D9D9D9; background: #F5F5F5;">+</button>
                                                                            </div>
                                                                            <a href="{{ route('product.detail', ['id' => $product->id, 'slug' => $product->slug]) }}"
                                                                                class="btn btn-outline-primary btn-sm"
                                                                                style="padding: 8px 16px; border-radius: 8px;">
                                                                                <i class="bi bi-eye me-1"></i>View
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Pagination -->
                                            @if($products->hasPages())
                                                <div class="product-pegination">
                                                    <nav aria-label="Product pagination">
                                                        <ul class="pagination justify-content-center custom-pagination">
                                                            {{-- Previous Page Link --}}
                                                            @if($products->onFirstPage())
                                                                <li class="page-item disabled">
                                                                    <span class="page-link">
                                                                        <i class="bi bi-chevron-left"></i>
                                                                    </span>
                                                                </li>
                                                            @else
                                                                <li class="page-item">
                                                                    <a class="page-link" href="{{ $products->previousPageUrl() }}"
                                                                        aria-label="Previous">
                                                                        <i class="bi bi-chevron-left"></i>
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            {{-- Pagination Elements --}}
                                                            @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                                                @if($page == $products->currentPage())
                                                                    <li class="page-item active">
                                                                        <span class="page-link">{{ $page }}</span>
                                                                    </li>
                                                                @else
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                                    </li>
                                                                @endif
                                                            @endforeach

                                                            {{-- Next Page Link --}}
                                                            @if($products->hasMorePages())
                                                                <li class="page-item">
                                                                    <a class="page-link" href="{{ $products->nextPageUrl() }}"
                                                                        aria-label="Next">
                                                                        <i class="bi bi-chevron-right"></i>
                                                                    </a>
                                                                </li>
                                                            @else
                                                                <li class="page-item disabled">
                                                                    <span class="page-link">
                                                                        <i class="bi bi-chevron-right"></i>
                                                                    </span>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </nav>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <div class="empty-state">
                                                <div class="empty-state-icon"
                                                    style="width: 120px; height: 120px; background-color: #F5FAFF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                                                    <i class="bi bi-inbox" style="font-size: 60px; color: #9CADC0;"></i>
                                                </div>
                                                <h3 style="font-size: 28px; color: #203A72; margin-bottom: 15px;">No Products
                                                    Found</h3>
                                                <p style="font-size: 18px; color: #666; margin-bottom: 30px;">
                                                    We couldn't find any products matching your filters. Try adjusting your
                                                    search criteria.
                                                </p>
                                                <a href="{{ route('products') }}" class="btn cart-btn">
                                                    <i class="bi bi-arrow-left me-2"></i>Clear All Filters
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('js')
    <script>
        function toggleStockFilter(value) {
            const input = document.getElementById('in_stock_input');
            const badges = document.querySelectorAll('.stock-badge');

            // Toggle active state
            badges.forEach(badge => badge.classList.remove('active'));
            event.currentTarget.classList.add('active');

            // Set value
            if (input.value == value) {
                input.value = ''; // Toggle off if already selected
                event.currentTarget.classList.remove('active');
            } else {
                input.value = value;
            }

            // Submit form
            document.getElementById('productFilterForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Clear filters link
            const clearFilters = document.getElementById('clearFilters');
            if (clearFilters) {
                clearFilters.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.location.href = '{{ route('products') }}';
                });
            }

            // Smooth scroll to top on pagination
            const paginationLinks = document.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function () {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });

            // Add to cart functionality for product cards
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const actionsDiv = this.closest('.product-cart-actions');
                    const productId = actionsDiv.dataset.productId;
                    const productType = actionsDiv.dataset.productType;

                    // For variable products, redirect to detail page
                    if (productType === 'variable') {
                        window.location.href = actionsDiv.querySelector('a[href*="product.detail"]')?.href || '#';
                        return;
                    }

                    // For simple/bundle products, add to cart
                    addProductToCart(productId, null, null, actionsDiv);
                });
            });

            // Quantity stepper controls
            document.querySelectorAll('.qty-increase').forEach(btn => {
                btn.addEventListener('click', function () {
                    const input = this.parentElement.querySelector('.qty-input');
                    const newQty = parseInt(input.value) + 1;
                    input.value = newQty;
                    updateProductCartQty(this.closest('.product-cart-actions'), newQty);
                });
            });

            document.querySelectorAll('.qty-decrease').forEach(btn => {
                btn.addEventListener('click', function () {
                    const input = this.parentElement.querySelector('.qty-input');
                    const currentQty = parseInt(input.value);

                    if (currentQty > 1) {
                        const newQty = currentQty - 1;
                        input.value = newQty;
                        updateProductCartQty(this.closest('.product-cart-actions'), newQty);
                    } else {
                        // Remove from cart and show Add to Cart button
                        removeProductFromCart(this.closest('.product-cart-actions'));
                    }
                });
            });
        });

        function addProductToCart(productId, variantId, unitId, actionsDiv) {
            fetch('{{ route("api.cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId,
                    variant_id: variantId || null,
                    unit_id: unitId || null,
                    quantity: 1
                }),
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide Add to Cart button, show quantity stepper
                        const addSection = actionsDiv.querySelector('.add-to-cart-section');
                        const qtySection = actionsDiv.querySelector('.quantity-stepper-section');
                        const qtyInput = qtySection.querySelector('.qty-input');

                        addSection.style.display = 'none';
                        qtySection.style.display = 'block';
                        qtyInput.value = 1;
                        actionsDiv.dataset.cartItemId = data.cart_item_id || '';

                        // Update cart count
                        if (typeof updateCartCount === 'function') {
                            updateCartCount(data.cart_count);
                        }

                        // Trigger cart update
                        document.dispatchEvent(new Event('cartUpdated'));

                        // Show sidebar cart
                        const cartPanel = document.getElementById('cartPanel');
                        if (cartPanel) {
                            cartPanel.classList.remove('hidden');
                            setTimeout(() => {
                                cartPanel.classList.add('active');
                            }, 10);
                        }
                    } else {
                        alert(data.message || 'Failed to add to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to add to cart');
                });
        }

        function updateProductCartQty(actionsDiv, quantity) {
            const cartItemId = actionsDiv.dataset.cartItemId;
            if (!cartItemId) return;

            fetch('{{ route("api.cart.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: cartItemId,
                    quantity: quantity
                }),
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.dispatchEvent(new Event('cartUpdated'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function removeProductFromCart(actionsDiv) {
            const cartItemId = actionsDiv.dataset.cartItemId;
            if (!cartItemId) {
                // Just hide stepper and show button
                const addSection = actionsDiv.querySelector('.add-to-cart-section');
                const qtySection = actionsDiv.querySelector('.quantity-stepper-section');
                addSection.style.display = 'block';
                qtySection.style.display = 'none';
                return;
            }

            fetch('{{ route("api.cart.remove") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: cartItemId
                }),
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const addSection = actionsDiv.querySelector('.add-to-cart-section');
                        const qtySection = actionsDiv.querySelector('.quantity-stepper-section');
                        addSection.style.display = 'block';
                        qtySection.style.display = 'none';
                        actionsDiv.dataset.cartItemId = '';

                        if (typeof updateCartCount === 'function') {
                            updateCartCount(data.cart_count);
                        }

                        document.dispatchEvent(new Event('cartUpdated'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Load cart state on page load to restore cart buttons for items already in cart
        function loadCartState() {
            fetch('{{ route("api.cart.items") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.items && data.items.length > 0) {
                        // Create a map of product_id to cart item for quick lookup
                        const cartMap = {};
                        data.items.forEach(item => {
                            // For simple products (no variant), use product_id as key
                            // For variable products, we'd need variant_id but those redirect to detail page anyway
                            const key = item.product_id;
                            if (!cartMap[key]) {
                                cartMap[key] = item;
                            }
                        });

                        // Update each product card that's in the cart
                        document.querySelectorAll('.product-cart-actions').forEach(actionsDiv => {
                            const productId = actionsDiv.dataset.productId;
                            const productType = actionsDiv.dataset.productType;

                            // Skip variable products - they redirect to detail page
                            if (productType === 'variable') return;

                            const cartItem = cartMap[productId];
                            if (cartItem) {
                                const addSection = actionsDiv.querySelector('.add-to-cart-section');
                                const qtySection = actionsDiv.querySelector('.quantity-stepper-section');
                                const qtyInput = qtySection ? qtySection.querySelector('.qty-input') : null;

                                if (addSection && qtySection && qtyInput) {
                                    addSection.style.display = 'none';
                                    qtySection.style.display = 'block';
                                    qtyInput.value = cartItem.quantity;
                                    actionsDiv.dataset.cartItemId = cartItem.id;
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading cart state:', error);
                });
        }

        // Load cart state when page loads
        document.addEventListener('DOMContentLoaded', loadCartState);
    </script>
@endpush