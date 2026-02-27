@extends('frontend.layouts.app', [
    'metaInfo' => [
        'title' => $product?->meta_title,
        'content' => $product?->meta_description,
        'url' => route('product.detail', ['id' => $product?->id, 'slug' => $product?->slug, 'variant' => $variant->id ?? null]),
        'keywords' => '',
    ],
])

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        .mainSlider .swiper-slide img {
            height: 500px !important;
            width: 826px !important;
            object-fit: contain !important;
        }

        .thumbSlider .swiper-slide img {
            height: 149px !important;
            width: 149px !important;
            object-fit: contain !important;
        }

        .pill-btn {
            padding: 6px 14px;
            border: 2px dashed #bbb;
            border-radius: 13px;
            background-color: #fff;
            color: #333;
            font-size: 14px;
            cursor: pointer;
        }

        .pill-btn.active {
            border-color: #000;
            font-weight: 600;
            border: 2px solid;
            background-color: #203a7217;
        }

        .pill-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .quantity-group {
            max-width: 200px;
        }

        .quantity-group .btn {
            width: 40px;
            border-color: #D9D9D9;
            background: #F5F5F5 !important;
            color: #000;
        }

        .quantity-group .form-control {
            border-left: none;
            border-right: none;
            text-align: center;
            font-weight: 500;
        }

        .cart-like.active {
            color: #D30606 !important;
        }

        .cart-like.active i {
            fill: #D30606;
        }
    </style>
@endpush

@section('content')
    <!-- beadcrum Section Start -->
    <section>
        <div class="bred-pro">
            <div class="container">
                <div class="breadcrumb-container">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        @forelse($categoryHierarchy as $categoryLevel)
                            @if (!isset($categoryLevel['display']))
                                <li><a>{{ $categoryLevel['name'] }}</a>
                                </li>
                            @else
                                <li><a>{{ $categoryLevel['name'] }}</a></li>
                            @endif
                        @empty
                        @endforelse
                        <li><a href="#" class="text-truncate">{{ $variant?->name }}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- beadcrum Section End -->
    <!-- MAin-section Content Start -->

    <section class="pro-dt-hero">
        <div class="pro-detail-block">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="pro-dtl-slider">
                            <div class="product-gallery">
                                <!-- Main Image Slider -->
                                <div class="swiper mainSlider">
                                    <div class="swiper-wrapper" id="mainSliderWrapper">
                                        @if ($product->product_type == 'variable' && $variant)
                                            @php
                                                $images = $variant->images;
                                                if ($images->count() == 0) {
                                                    $images = $product->images;
                                                }
                                            @endphp
                                            @forelse($images as $image)
                                                <div class="swiper-slide">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}"
                                                        alt="Product Image {{ $loop->iteration }}" onerror="this.src='{{ asset('no-image-found.jpg') }}'" />
                                                </div>
                                            @empty
                                                <div class="swiper-slide">
                                                    <img src="{{ asset('no-image-found.jpg') }}"
                                                        alt="Product Image" />
                                                </div>
                                            @endforelse
                                        @else
                                            @forelse($product->images as $image)
                                                <div class="swiper-slide">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" onerror="this.src='{{ asset('no-image-found.jpg') }}'" alt="Product Image {{ $loop->iteration }}" />
                                                </div>
                                            @empty
                                                <div class="swiper-slide">
                                                    <img src="{{ asset('no-image-found.jpg') }}"
                                                        alt="Product Image" />
                                                </div>
                                            @endforelse
                                        @endif
                                    </div>

                                    <!-- Navigation -->
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                </div>

                                <!-- Thumbnail Slider -->
                                <div class="swiper thumbSlider mt-3">
                                    <div class="swiper-wrapper" id="thumbSliderWrapper">
                                        @if ($product->product_type == 'variable' && $variant)
                                            @php
                                                $images = $variant->images;
                                                if ($images->count() == 0) {
                                                    $images = $product->images;
                                                }
                                            @endphp
                                            @if($images->count() > 1)
                                                @forelse($images as $image)
                                                    <div class="swiper-slide">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                                            alt="Product Image {{ $loop->iteration }}" onerror="this.src='{{ asset('no-image-found.jpg') }}'" />
                                                    </div>
                                                @empty
                                                @endforelse
                                            @endif
                                        @else
                                            @if($product->images->count() > 1)
                                                @forelse($product->images as $image)
                                                    <div class="swiper-slide">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                                            alt="Product Image {{ $loop->iteration }}" onerror="this.src='{{ asset('no-image-found.jpg') }}'" />
                                                    </div>
                                                @empty
                                                @endforelse
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="detail-right">
                            @if ($product->product_type == 'variable')
                                <h2 class="h-40 mb-2">
                                    {{ $variant->name }}
                                </h2>
                                <p class="p-20">SKU: {{ $variant?->sku }}</p>
                                <div class="pt-4 pb-4">
                                    @if ($variant?->in_stock)
                                        <div class="badge bg-is">In Stock</div>
                                    @else
                                        <div class="badge bg-oos">Out of Stock</div>
                                    @endif
                                </div>
                            @else
                                <h2 class="h-40 mb-2">
                                    {{ $product->name }}
                                </h2>
                                <p class="p-20">SKU: {{ $product?->sku }}</p>
                                <div class="pt-4 pb-4">
                                    @if ($product?->in_stock)
                                        <div class="badge bg-is">In Stock</div>
                                    @else
                                        <div class="badge bg-oos">Out of Stock</div>
                                    @endif
                                </div>
                            @endif

                            @if(isset($taxSlab) && $taxSlab)
                                <p class="p-20" style="margin-top: -5px;">
                                    <strong>Tax:</strong>
                                    {{ rtrim(rtrim(number_format($taxSlab->tax_percentage, 2, '.', ''), '0'), '.') }}%
                                    (applied at checkout)
                                </p>
                            @endif

                            {{-- Attribute Selection for Variable Products --}}
                            @if($product->product_type == 'variable' && $attributes->count() > 0)
                                @foreach($attributes as $attribute)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ $attribute->name }}:</label>
                                        <div class="d-flex flex-wrap gap-2 attribute-swatches" data-attribute-id="{{ $attribute->id }}">
                                            @foreach($attribute->values as $value)
                                                @php
                                                    $isActive = $variant && $variant->attributes->contains('id', $value->id);
                                                    // Check if this attribute value combination exists in any variant
                                                    $variantExists = $variants->filter(function($v) use ($attribute, $value, $variant) {
                                                        $hasThisValue = $v->attributes->contains('id', $value->id);
                                                        if (!$variant) return $hasThisValue;
                                                        // Check if this variant matches all other selected attributes
                                                        $otherAttributes = $variant->attributes->where('attribute_id', '!=', $attribute->id);
                                                        $matchesOthers = $otherAttributes->every(function($otherAttr) use ($v) {
                                                            return $v->attributes->contains('id', $otherAttr->id);
                                                        });
                                                        return $hasThisValue && $matchesOthers;
                                                    })->count() > 0;
                                                @endphp
                                                <button type="button"
                                                    class="pill-btn attribute-swatch @if($isActive) active @endif @if(!$variantExists) disabled @endif"
                                                    data-attribute-id="{{ $attribute->id }}"
                                                    data-attribute-value-id="{{ $value->id }}"
                                                    @if(!$variantExists) disabled @endif>
                                                    {{ $value->value }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Unit Selection (only if additional units exist) --}}
                            @if($units->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Select Unit</label>
                                    <div class="d-flex flex-wrap gap-2" id="unitSelector">
                                        @foreach ($units as $unit)
                                            <button type="button"
                                                class="pill-btn unit-selector-btn @if ($unit['is_default']) active @endif"
                                                data-unit-type="{{ $unit['unit_type'] }}"
                                                data-unit-id="{{ $unit['id'] }}" 
                                                data-unit-title="{{ $unit['title'] }}"
                                                @if (isset($unit['quantity'])) data-unit-quantity="{{ $unit['quantity'] }}" @endif>
                                                {{ $unit['title'] }}
                                                @if (isset($unit['quantity']) && $unit['quantity'] > 1)
                                                    ({{ $unit['quantity'] }})
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($product->product_type == 'bundle' && $bundle && $bundleItems->count() > 0)
                                {{-- Bundle Items Display --}}
                                <div class="bult-div mb-4">
                                    <h3 class="h-24 mb-4" style="font-size: 22px; font-weight: 600; color: #203A72;">Bundle Contents</h3>
                                    <div class="bundle-items" style="background: #F5FAFF; border-radius: 8px; padding: 20px; border: 1px solid #EEEEEE;">
                                        @foreach($bundleItems as $bundleItem)
                                            <div class="bundle-item d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom: 1px solid #EEEEEE;">
                                                <div style="width: 80px; height: 80px; flex-shrink: 0;">
                                                    @php
                                                        $itemImage = $bundleItem->product->primaryImage;
                                                        $itemImageUrl = $itemImage ? asset('storage/' . $itemImage->image_path) : asset('assets/images/default-product.png');
                                                    @endphp
                                                    <img src="{{ $itemImageUrl }}" onerror="this.src='{{ asset('no-image-found.jpg') }}'" alt="{{ $bundleItem->product->name }}" style="width: 100%; height: 100%; object-fit: contain; border-radius: 6px; background: #fff; padding: 5px;">
                                                </div>
                                                <div style="flex: 1;">
                                                    <h5 style="font-size: 16px; font-weight: 600; color: #203A72; margin-bottom: 5px;">
                                                        {{ $bundleItem->product->name }}
                                                        @if($bundleItem->variant)
                                                            - {{ $bundleItem->variant->name }}
                                                        @endif
                                                    </h5>
                                                    <p style="font-size: 14px; color: #666; margin: 0;">
                                                        Quantity: <strong>{{ $bundleItem->quantity }}</strong>
                                                        @if($bundleItem->unit && $bundleItem->unit->unit)
                                                            {{ $bundleItem->unit->unit->name }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($bundle->pricing_mode == 'sum_discount')
                                            <div class="bundle-discount mt-3 pt-3" style="border-top: 2px solid #D9D9D9;">
                                                <p style="font-size: 16px; color: #203A72; margin: 0;">
                                                    <strong>Bundle Discount:</strong> 
                                                    @if($bundle->discount_type == 'percentage')
                                                        {{ $bundle->discount_value }}% OFF
                                                    @else
                                                        {{ currency_format($bundle->discount_value) }} OFF
                                                    @endif
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="bult-div">
                                <h3 class="h-24 mb-4" style="font-size: 22px; font-weight: 600; color: #203A72;">Bulk Pricing</h3>
                                <div class="table-responsive" style="border-radius: 8px; overflow: hidden; border: 1px solid #EEEEEE;">
                                    <table class="table price-table text-center table-striped align-middle mb-0" style="margin-bottom: 0;">
                                        <thead style="background: #F5FAFF;">
                                            <tr>
                                                <th style="padding: 14px; font-weight: 600; color: #FFF; border-bottom: 2px solid #D9D9D9;">Quantity</th>
                                                <th style="padding: 14px; font-weight: 600; color: #FFF; border-bottom: 2px solid #D9D9D9;">MRP</th>
                                                <th style="padding: 14px; font-weight: 600; color: #FFF; border-bottom: 2px solid #D9D9D9;">Your Price</th>
                                                <th style="padding: 14px; font-weight: 600; color: #FFF; border-bottom: 2px solid #D9D9D9;">You Save</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pricingTableBody" style="background: #fff;">
                                            {{-- Dynamic pricing will be loaded here --}}
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Add to Cart / Quantity Stepper Section --}}
                                <div class="mt-4">
                                    <div id="addToCartSection" style="display: {{ $cartItem ? 'none' : 'block' }};">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn cart-btn" id="addToCartBtn" style="flex: 1; padding: 14px 24px; font-size: 16px; font-weight: 600; border-radius: 8px;">
                                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                            </button>
                                            <button type="button" class="btn cart-like" id="wishlistBtn" style="border: 1px solid #D9D9D9; padding: 14px 18px; border-radius: 8px; background: #fff; color: #203A72; font-size: 22px; min-width: 56px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-heart" id="wishlistIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="quantityStepperSection" style="display: {{ $cartItem ? 'block' : 'none' }};">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="input-group quantity-group" style="max-width: 180px; flex: 1;">
                                                <button class="btn btn-outline-secondary btn-minus" type="button" id="decreaseQty" style="width: 44px; height: 44px; border-color: #D9D9D9; background: #F5F5F5; color: #203A72; font-size: 20px; font-weight: 600;">−</button>
                                                <input type="text" class="form-control text-center" value="{{ $cartItem ? $cartItem->quantity : 1 }}" id="cartQuantity" readonly style="border: 1px solid #D9D9D9; height: 44px; font-size: 16px; font-weight: 600; color: #203A72;">
                                                <button class="btn btn-outline-secondary btn-plus" type="button" id="increaseQty" style="width: 44px; height: 44px; border-color: #D9D9D9; background: #F5F5F5; color: #203A72; font-size: 20px; font-weight: 600;">+</button>
                                            </div>
                                            <button type="button" class="btn cart-like" id="wishlistBtnStepper" style="border: 1px solid #D9D9D9; padding: 14px 18px; border-radius: 8px; background: #fff; color: #203A72; font-size: 22px; min-width: 56px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-heart" id="wishlistIconStepper"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="bran-size mt-4">
                                    <ul class="product-info">
                                        @if($product->brand)
                                            <li><span>Brand</span><span>{{ $product->brand->name }}</span></li>
                                        @endif
                                        @if($variant && $variant->sku)
                                            <li><span>SKU</span><span>{{ $variant->sku }}</span></li>
                                        @elseif($product->sku)
                                            <li><span>SKU</span><span>{{ $product->sku }}</span></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- MAin-section Content Start -->

    <!-- Description Content Start -->
    <section class="Description">
        <div class="Description__block">
            <div class="Description__box">
                <h2 class="h-30">Product Description</h2>
                <div class="Description__text">
                    <div class="top-des ">
                        {!! $product->long_description !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/js/swiper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Product data
        const productData = {
            productId: {{ $product->id }},
            productType: '{{ $product->product_type }}',
            variantId: {{ $variant ? $variant->id : 'null' }},
            selectedUnitId: {{ $units->where('is_default', true)->first()['id'] ?? ($units->first()['id'] ?? 'null') }},
            variants: @json($variantsJs),
            units: @json($unitsJs)
        };

        let thumbSlider, mainSlider;
        let currentCartItemId = {{ $cartItem ? $cartItem->id : 'null' }};
        let currentQuantity = {{ $cartItem ? $cartItem->quantity : 1 }};

        $(document).ready(function() {
            // Initialize Swiper
            thumbSlider = new Swiper(".thumbSlider", {
                spaceBetween: 10,
                slidesPerView: 5,
                freeMode: true,
                watchSlidesProgress: true,
                breakpoints: {
                    320: { slidesPerView: 3, spaceBetween: 20 },
                    576: { slidesPerView: 4, spaceBetween: 20 },
                    992: { slidesPerView: 5, spaceBetween: 20 },
                },
            });

            mainSlider = new Swiper(".mainSlider", {
                spaceBetween: 10,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                thumbs: {
                    swiper: thumbSlider,
                },
            });

            // Load initial pricing
            loadPricing();

            // Check wishlist status
            checkWishlistStatus();
            
            // If item is in cart, check if we need to update cart item ID when unit changes
            if (currentCartItemId) {
                // When unit changes, we need to check if there's a cart item for the new unit
                // For now, we'll keep the existing cart item and just update pricing
            }

            // Attribute selection (for variable products - refreshes page)
            $('.attribute-swatch').on('click', function() {
                if ($(this).hasClass('disabled')) return;
                
                const attributeId = $(this).data('attribute-id');
                const valueId = $(this).data('attribute-value-id');
                
                // Update active state
                $(this).closest('.attribute-swatches').find('.attribute-swatch').removeClass('active');
                $(this).addClass('active');
                
                // Get all selected attribute values
                const selectedAttributes = [];
                $('.attribute-swatch.active').each(function() {
                    selectedAttributes.push($(this).data('attribute-value-id'));
                });
                
                // Find matching variant
                const matchingVariant = productData.variants.find(v => {
                    return selectedAttributes.every(attrId => v.attributes.includes(attrId)) &&
                           selectedAttributes.length === v.attributes.length;
                });
                
                if (matchingVariant) {
                    // Refresh page with variant in URL
                    const url = '{{ route("product.detail", ["id" => $product->id, "slug" => $product->slug, "variant" => ":variant"]) }}';
                    window.location.href = url.replace(':variant', matchingVariant.id);
                }
            });

            // Unit selection (AJAX - no page refresh)
            $('.unit-selector-btn').on('click', function() {
                const unitId = $(this).data('unit-id');
                productData.selectedUnitId = unitId;
                
                // Update active state
                $('.unit-selector-btn').removeClass('active');
                $(this).addClass('active');
                
                // Load pricing via AJAX
                loadPricing();
                
                // Update images if variant has unit-specific images (if needed)
                // For now, units don't have separate images, so we skip image update
            });

            // Add to Cart button
            $('#addToCartBtn').on('click', function() {
                addToCart();
            });

            // Quantity stepper controls
            $('#increaseQty').on('click', function() {
                const qtyInput = $('#cartQuantity');
                const newQty = parseInt(qtyInput.val()) + 1;
                qtyInput.val(newQty);
                currentQuantity = newQty;
                updateCartItem(newQty);
            });

            $('#decreaseQty').on('click', function() {
                const qtyInput = $('#cartQuantity');
                const currentQty = parseInt(qtyInput.val());
                
                if (currentQty > 1) {
                    const newQty = currentQty - 1;
                    qtyInput.val(newQty);
                    currentQuantity = newQty;
                    updateCartItem(newQty);
                } else {
                    // Remove from cart and show Add to Cart button
                    removeFromCart();
                }
            });

            // Wishlist button
            $('#wishlistBtn, #wishlistBtnStepper').on('click', function() {
                toggleWishlist();
            });
        });

        // Load pricing
        function loadPricing() {
            const variantId = productData.variantId || '';
            const unitId = productData.selectedUnitId;
            const quantity = currentQuantity || 1;
            
            fetch(`{{ route('api.product.pricing') }}?product_id=${productData.productId}&variant_id=${variantId}&unit_id=${unitId}&quantity=${quantity}`)
                .then(response => response.json())
                .then(data => {
                    updatePricingTable(data.tiers || []);
                })
                .catch(error => {
                    console.error('Error loading pricing:', error);
                });
        }

        // Update pricing table
        function updatePricingTable(tiers) {
            const tbody = $('#pricingTableBody');
            tbody.empty();

            if (tiers.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center">No pricing available</td></tr>');
                return;
            }

            tiers.forEach((tier, index) => {
                const qtyRange = tier.max_qty 
                    ? `${tier.min_qty}–${tier.max_qty}`
                    : `${tier.min_qty}+`;
                
                const mrp = tier.mrp || tier.price;
                const savings = mrp - tier.price;
                const savingsPercent = ((savings / mrp) * 100).toFixed(0);

                const rowClass = index === Math.floor(tiers.length / 2) ? 'highlight-row' : '';
                
                tbody.append(`
                    <tr class="${rowClass}">
                        <td>${qtyRange}</td>
                        <td>${window.formatCurrency(mrp)}</td>
                        <td>${window.formatCurrency(tier.price)}</td>
                        <td>${window.formatCurrency(savings)} (${savingsPercent}%)</td>
                    </tr>
                `);
            });
        }

        // Add to cart
        function addToCart() {
            const data = {
                product_id: productData.productId,
                variant_id: productData.variantId || null,
                unit_id: productData.selectedUnitId || null,
                quantity: 1
            };

            fetch('{{ route("api.cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data),
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide Add to Cart button, show quantity stepper
                    $('#addToCartSection').hide();
                    $('#quantityStepperSection').show();
                    $('#cartQuantity').val(1);
                    currentQuantity = 1;
                    currentCartItemId = data.cart_item_id || null;
                    
                    // Update cart count if needed
                    if (typeof updateCartCount === 'function') {
                        updateCartCount(data.cart_count);
                    }
                    
                    // Trigger cart update event
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

        // Update cart item
        function updateCartItem(quantity) {
            if (!currentCartItemId) return;
            
            fetch('{{ route("api.cart.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: currentCartItemId,
                    quantity: quantity
                }),
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update quantity and reload pricing
                    currentQuantity = quantity;
                    loadPricing();
                    
                    // Trigger cart update event
                    document.dispatchEvent(new Event('cartUpdated'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Remove from cart
        function removeFromCart() {
            if (!currentCartItemId) {
                // Just hide stepper and show button
                $('#quantityStepperSection').hide();
                $('#addToCartSection').show();
                return;
            }
            
            fetch('{{ route("api.cart.remove") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: currentCartItemId
                }),
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide stepper, show Add to Cart button
                    $('#quantityStepperSection').hide();
                    $('#addToCartSection').show();
                    currentCartItemId = null;
                    currentQuantity = 1;
                    
                    // Update cart count if needed
                    if (typeof updateCartCount === 'function') {
                        updateCartCount(data.cart_count);
                    }
                    
                    // Trigger cart update event
                    document.dispatchEvent(new Event('cartUpdated'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Check wishlist status
        function checkWishlistStatus() {
            fetch(`{{ route("api.wishlist.check") }}?product_id=${productData.productId}&variant_id=${productData.variantId || ''}`, {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.in_wishlist) {
                    $('#wishlistIcon, #wishlistIconStepper').addClass('bi-heart-fill').removeClass('bi-heart');
                    $('#wishlistBtn, #wishlistBtnStepper').addClass('active');
                }
            })
            .catch(error => {
                console.error('Error checking wishlist:', error);
            });
        }

        // Toggle wishlist
        function toggleWishlist() {
            const isInWishlist = $('#wishlistBtn').hasClass('active');
            const url = isInWishlist ? '{{ route("api.wishlist.remove") }}' : '{{ route("api.wishlist.add") }}';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productData.productId,
                    variant_id: productData.variantId || null
                }),
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.in_wishlist) {
                        $('#wishlistIcon, #wishlistIconStepper').addClass('bi-heart-fill').removeClass('bi-heart');
                        $('#wishlistBtn, #wishlistBtnStepper').addClass('active');
                    } else {
                        $('#wishlistIcon, #wishlistIconStepper').removeClass('bi-heart-fill').addClass('bi-heart');
                        $('#wishlistBtn, #wishlistBtnStepper').removeClass('active');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
@endpush
