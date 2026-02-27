@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@push('product-css')
<style>
    .product-preview-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    
    .btn btn-primary {
        padding: 6px 16px;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn btn-primary:hover {
        background: #0056b3;
        color: #fff;
        text-decoration: none;
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
    
    .variants-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    
    .variants-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        color: #333;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .variants-table td {
        padding: 12px;
        font-size: 13px;
        color: #666;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .variants-table tr:hover {
        background: #f8f9fa;
    }
    
    .stock-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }
    
    .stock-in-stock {
        background: #d4edda;
        color: #155724;
    }
    
    .stock-low-stock {
        background: #fff3cd;
        color: #856404;
    }
    
    .stock-out-stock {
        background: #f8d7da;
        color: #721c24;
    }
    
    .pricing-structure {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    
    .pricing-tier {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }
    
    .pricing-tier-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    
    .pricing-tier-price {
        font-size: 18px;
        font-weight: 700;
        color: #007bff;
        margin-bottom: 5px;
    }
    
    .pricing-tier-subtitle {
        font-size: 12px;
        color: #666;
    }
    
    .category-breadcrumb {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .category-breadcrumb a {
        color: #007bff;
        text-decoration: none;
    }
    
    .category-breadcrumb a:hover {
        text-decoration: underline;
    }
    
    .featured-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background: #fff3cd;
        color: #856404;
        border-radius: 12px;
        font-size: 13px;
        margin-top: 10px;
    }
    
    .featured-badge .star-icon {
        color: #ffc107;
    }
    
    .inventory-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    
    .inventory-stat {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .inventory-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #007bff;
        margin-bottom: 5px;
    }
    
    .inventory-stat-label {
        font-size: 13px;
        color: #666;
    }
    
    .more-variants {
        margin-top: 10px;
        font-size: 13px;
        color: #007bff;
        font-weight: 500;
    }
</style>
@endpush

@section('product-content')
@php
    
    $primaryCategory = \App\Models\AwProductCategory::where('product_id', $product->id)
        ->where('is_primary', 1)
        ->with('category')
        ->first();

    $secondaryCategories = \App\Models\AwProductCategory::where('product_id', $product->id)
        ->where('is_primary', 0)
        ->with('category')
        ->get();
    
    $categoryBreadcrumb = [];
    if ($primaryCategory && $primaryCategory->category) {
        $cat = $primaryCategory->category;
        $breadcrumb = [];
        while ($cat) {
            array_unshift($breadcrumb, $cat->name);
            $cat = $cat->parent;
        }
        $categoryBreadcrumb = $breadcrumb;
    }
    
    $productTags = $product->tags;
    
    $totalStock = 0;
    $lowStockVariants = 0;
    $warehouseIds = [];
    
    $variantStock = $product->supplierWarehouseProducts->sum('quantity');
    $totalStock += $variantStock;
    
    $reorderLevel = $product->supplierWarehouseProducts->max('reorder_level') ?? 0;
    if ($variantStock <= $reorderLevel && $variantStock > 0) {
        $lowStockVariants++;
    }
    
    foreach ($product->supplierWarehouseProducts as $inv) {
        if ($inv->warehouse_id) {
            $warehouseIds[$inv->warehouse_id] = true;
        }
    }
    
    $warehouseCount = count($warehouseIds);
    
    $pricingByUnit = [];
    $unitNames = [];
    
    foreach ($product->units as $productUnit) {
        $unitId = $productUnit->unit_id;
        $unitName = $productUnit->unit->name ?? 'Unit';
        
        if (!isset($unitNames[$unitId])) {
            $unitNames[$unitId] = $unitName;
        }
        
        $price = \App\Models\AwPrice::where('product_id', $product->id)
            ->where('original_unit_id', $unitId)
            ->with('tiers')
            ->first();

        if ($price) {
            if (!isset($pricingByUnit[$unitId])) {
                $pricingByUnit[$unitId] = [
                    'unit_name' => $unitName,
                    'type' => $price->pricing_type,
                    'prices' => []
                ];
            }
            
            if ($price->pricing_type === 'tiered' && $price->tiers->count() > 0) {
                $minPrice = $price->tiers->min('price');
                $maxPrice = $price->tiers->max('price');
            } else {
                $minPrice = $price->base_price;
                $maxPrice = $price->base_price;
            }
            
            $pricingByUnit[$unitId]['prices'][] = [
                'min' => $minPrice,
                'max' => $maxPrice
            ];
        }
    }
    
    $unitPriceRanges = [];
    foreach ($pricingByUnit as $unitId => $data) {
        $allPrices = collect($data['prices'])->flatten();
        $unitPriceRanges[$unitId] = [
            'type' => $data['type'],
            'unit_name' => $data['unit_name'],
            'min' => $allPrices->min(),
            'max' => $allPrices->max()
        ];
    }
    
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="product-preview-card">
                <div class="product-image-placeholder">
                    @if($mainImage)
                        <img onerror="this.onerror=null; this.src='{{ asset('no-image-found.jpg') }}';" src="{{ asset('storage/' . $mainImage->image_path) }}" alt="{{ $product->name }}">
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
                    <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt(1), 'id' => encrypt($product->id)]) }}" class="btn btn-primary">Edit</a>
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
                    <span class="info-item-label">Description:</span>
                    <span class="info-item-value">{{ \Illuminate\Support\Str::limit($product->short_description ?? $product->long_description ?? 'No description', 150) }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <!-- Categories (Bottom Left) -->
        <div class="col-md-4">
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title">Categories</h4>
                    <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt(5), 'id' => encrypt($product->id)]) }}" class="btn btn-primary">Edit</a>
                </div>

                <h4>Primary Category</h4>
                @if(!empty($categoryBreadcrumb))
                    <div class="category-breadcrumb">
                        @foreach($categoryBreadcrumb as $index => $catName)
                            <a href="#" @if(!$loop->last) class="text-secondary" @endif >{{ $catName }}</a>
                            @if($index < count($categoryBreadcrumb) - 1)
                                <span> > </span>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="info-item-value">No category assigned</div>
                @endif

                <hr>

                <h4> Secondary Cateogries </h4>
                <ul>
                    @foreach ($secondaryCategories as $secondaryCategoriesRow)
                        <li> {{ $secondaryCategoriesRow->category->name ?? '' }} </li>
                    @endforeach
                </ul>

            </div>
        </div>

        <div class="col-md-4">
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title">Pricing Structure</h4>
                    <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt(3), 'id' => encrypt($product->id)]) }}" class="btn btn-primary">Edit</a>
                </div>
                
                <div class="pricing-structure">
                    @php
                        $unitDisplayMap = [
                            'can' => 'Can (Single Unit)',
                            'case' => 'Case (24 Cans)',
                            'pallet' => 'Pallet (60 Cases)',
                            'unit' => 'Unit',
                            'piece' => 'Piece',
                            'box' => 'Box',
                            'pack' => 'Pack'
                        ];
                        
                        $displayedUnits = 0;
                        $maxDisplayUnits = 3;
                    @endphp
                    
                    @foreach($unitPriceRanges as $unitId => $range)
                        @if($displayedUnits < $maxDisplayUnits)
                            @php
                                $unitName = strtolower($range['unit_name']);
                                $displayName = $unitDisplayMap[$unitName] ?? ucfirst($range['unit_name']) . ' (Per unit)';
                                $subtitle = 'Per unit pricing';
                                
                                if (strpos(strtolower($range['unit_name']), 'case') !== false) {
                                    $subtitle = 'Per unit in case';
                                } elseif (strpos(strtolower($range['unit_name']), 'pallet') !== false) {
                                    $subtitle = 'Per unit in pallet';
                                }
                            @endphp
                            <div class="pricing-tier">
                                <div class="pricing-tier-title">{{ $displayName }}</div>
                                @if($range['type'] == 'tiered')
                                <div class="pricing-tier-price">
                                    ${{ number_format($range['max'], 2) }} - ${{ number_format($range['min'], 2) }}
                                </div>
                                @else
                                    ${{ number_format($range['max'], 2) }}
                                @endif
                                <div class="pricing-tier-subtitle">{{ $subtitle }}</div>
                            </div>
                            @php $displayedUnits++; @endphp
                        @endif
                    @endforeach
                    
                    @if(empty($unitPriceRanges))
                        <div class="info-item-value">No pricing structure defined</div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title">Inventory Status</h4>
                    <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt(4), 'id' => encrypt($product->id)]) }}" class="btn btn-primary">Edit</a>
                </div>
                
                <div class="inventory-stats">
                    <div class="inventory-stat">
                        <div class="inventory-stat-value">{{ number_format($totalStock) }}</div>
                        <div class="inventory-stat-label">Total Stock</div>
                    </div>
                    
                    <div class="inventory-stat">
                        <div class="inventory-stat-value">{{ $lowStockVariants }}</div>
                        <div class="inventory-stat-label">Low Stock Alerts</div>
                    </div>
                    
                    <div class="inventory-stat">
                        <div class="inventory-stat-value">{{ $warehouseCount }}</div>
                        <div class="inventory-stat-label">Locations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('product-js')
<script>

</script>
@endpush
