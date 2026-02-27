@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@push('product-css')
<style>
    .bundle-product-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        background: #fff;
        position: relative;
    }
    .bundle-product-card .product-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .bundle-product-card .product-badge.simple {
        background: #dbeafe;
        color: #1e40af;
    }
    .bundle-product-card .product-badge.variable {
        background: #f3e8ff;
        color: #7c3aed;
    }
    .bundle-product-card .product-name {
        font-weight: 600;
        font-size: 16px;
        color: #1e293b;
    }
    .bundle-product-card .product-sku {
        font-size: 12px;
        color: #64748b;
    }
    .bundle-product-card .remove-btn {
        position: absolute;
        top: 12px;
        right: 12px;
    }
    .bundle-product-card .units-section {
        margin-top: 12px;
    }
    .bundle-product-card .units-section label {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }
    .bundle-product-card .unit-option {
        display: block;
        margin-bottom: 4px;
    }
    .bundle-product-card .unit-option input[type="radio"] {
        margin-right: 8px;
    }
    .bundle-product-card .quantity-section {
        margin-top: 12px;
    }
    .bundle-product-card .quantity-section label {
        font-weight: 600;
        margin-bottom: 4px;
        display: block;
    }
    .bundle-product-card .quantity-section input {
        width: 80px;
    }
    .pricing-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        background: #fff;
    }
    .pricing-card h5 {
        font-weight: 600;
        margin-bottom: 12px;
        color: #1e293b;
    }
    .pricing-card .pricing-option {
        margin-bottom: 8px;
    }
    .pricing-card .current-total {
        font-size: 12px;
        color: #64748b;
        margin-left: 24px;
    }
    .pricing-card .final-price {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
    }
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #64748b;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
    }
    .modal-product-type {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .modal-product-type:hover {
        border-color: #3b82f6;
    }
    .modal-product-type.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .modal-product-type input[type="radio"] {
        margin-right: 12px;
    }
    .modal-product-type .type-title {
        font-weight: 600;
        color: #1e293b;
    }
    .modal-product-type .type-desc {
        font-size: 12px;
        color: #64748b;
        margin-left: 28px;
    }
    #addProductModal .modal-step {
        display: none;
    }
    #addProductModal .modal-step.active {
        display: block;
    }
</style>
@endpush

@section('product-content')
<div class="container-fluid">
    <div class="row">
        <!-- Left Column: Bundle Products -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-0">Bundle Products</h5>
                        <small class="text-muted">Add simple products or variable product variants to this bundle</small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        + Add Product
                    </button>
                </div>
                <div class="card-body">
                    <div id="bundle-products-container">
                        {{-- @if($bundle && $bundle->items->count() > 0)
                            @foreach($bundle->items as $item)
                                <div class="bundle-product-card" 
                                     data-product-id="{{ $item->product_id }}" 
                                     data-variant-id="{{ $item->variant_id ?? '' }}"
                                     data-unit-id="{{ $item->unit_id }}"
                                     data-quantity="{{ $item->quantity }}">
                                    <!-- Will be populated by JS on page load -->
                                </div>
                            @endforeach
                        @endif --}}
                    </div>
                    <div id="empty-state" class="empty-state" style="{{ $bundle && $bundle->items->count() > 0 ? 'display:none;' : '' }}">
                        No products added yet. Use the button above to include products in this bundle.
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Pricing -->
        <div class="col-md-4">
            <!-- Pricing Mode -->
            <div class="pricing-card">
                <h5>Pricing</h5>
                <div class="pricing-option">
                    <label>
                        <input type="radio" name="pricing_mode" value="sum_discount" {{ (!$bundle || $bundle->pricing_mode === 'sum_discount') ? 'checked' : '' }}>
                        Sum of product prices
                    </label>
                    <div class="current-total">Current total: <strong id="sum-total">$0.00</strong></div>
                </div>
                <div class="pricing-option mt-3">
                    <label>
                        <input type="radio" name="pricing_mode" value="fixed" {{ ($bundle && $bundle->pricing_mode === 'fixed') ? 'checked' : '' }}>
                        Fixed bundle price
                    </label>
                    <input type="number" step="0.01" min="0" name="fixed_bundle_price" id="fixed_bundle_price" 
                           class="form-control mt-1" placeholder="0.00"
                           value="{{ ($bundle && $bundle->pricing_mode === 'fixed') ? $bundle->total : '' }}"
                           {{ (!$bundle || $bundle->pricing_mode !== 'fixed') ? 'disabled' : '' }}>
                </div>
            </div>

            <!-- Discount -->
            <div class="pricing-card" id="discount-section">
                <h5>Discount</h5>
                <div class="pricing-option">
                    <label>
                        <input type="radio" name="discount_type" value="fixed" {{ ($bundle && $bundle->discount_type === 'fixed') ? 'checked' : '' }}>
                        Fixed discount
                    </label>
                    <input type="number" step="0.01" min="0" name="fixed_discount_amount" id="fixed_discount_amount" 
                           class="form-control mt-1" placeholder="Amount"
                           value="{{ ($bundle && $bundle->discount_type === 'fixed') ? $bundle->discount_value : '' }}"
                           {{ (!$bundle || $bundle->discount_type !== 'fixed') ? 'disabled' : '' }}>
                </div>
                <div class="pricing-option mt-3">
                    <label>
                        <input type="radio" name="discount_type" value="percentage" {{ ($bundle && $bundle->discount_type === 'percentage') ? 'checked' : '' }}>
                        Percentage discount
                    </label>
                    <input type="number" step="0.01" min="0" max="100" name="percentage_discount" id="percentage_discount" 
                           class="form-control mt-1" placeholder="0"
                           value="{{ ($bundle && $bundle->discount_type === 'percentage') ? $bundle->discount_value : '' }}"
                           {{ (!$bundle || $bundle->discount_type !== 'percentage') ? 'disabled' : '' }}>
                </div>
            </div>

            <!-- Final Price -->
            <div class="pricing-card">
                <h5>Final Price</h5>
                <div class="final-price" id="final-price">$0.00</div>
                <input type="hidden" name="total_amount" id="total_amount" value="0">
            </div>
        </div>
    </div>
</div>

<!-- Hidden inputs for form submission -->
<div id="hidden-inputs-container"></div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product to Bundle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Choose product type -->
                <div class="modal-step active" id="modal-step-1">
                    <p class="text-muted mb-3">Choose the type of product you want to add.</p>
                    
                    <label class="modal-product-type d-block" id="type-simple">
                        <input type="radio" name="product_type_choice" value="simple">
                        <span class="type-title">Simple Product</span>
                        <div class="type-desc">Add standalone products without variants.</div>
                    </label>
                    
                    <label class="modal-product-type d-block" id="type-variable">
                        <input type="radio" name="product_type_choice" value="variable">
                        <span class="type-title">Variable Product Variant</span>
                        <div class="type-desc">Pick a specific variant from variable products.</div>
                    </label>
                </div>

                <!-- Step 2a: Simple product selection -->
                <div class="modal-step" id="modal-step-2-simple">
                    <label class="form-label">Select Product</label>
                    <select id="simple-product-select" class="form-control" style="width: 100%;">
                        <option value="">Search for a product...</option>
                    </select>
                </div>

                <div class="modal-step" id="modal-step-2-variable">
                    <label class="form-label">Select Product Variant</label>
                    <select id="variable-product-select" class="form-control" style="width: 100%;">
                        <option value="">Select a product first...</option>
                    </select>
                    <div id="variant-select-container" class="mt-3" style="display: none;">
                        <label class="form-label">Select Variant</label>
                        <select id="variant-select" class="form-control" style="width: 100%;">
                            <option value="">Select a variant...</option>
                        </select>
                        <small class="text-muted" id="no-variants-msg" style="display: none;">No variants available.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modal-back-btn" style="display: none;">Back</button>
                <button type="button" class="btn btn-primary" id="modal-next-btn">Next</button>
                <button type="button" class="btn btn-primary" id="modal-add-btn" style="display: none;">Add</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('product-js')
<script>
$(document).ready(function() {
    let bundleItems = [];
    let currentModalStep = 1;
    let selectedProductType = null;

    const routes = {
        searchProducts: '{{ route("bundle-products.search") }}',
        getVariants: '{{ url("admin/bundle-products") }}',
        getUnits: '{{ url("admin/bundle-products") }}',
        itemPrice: '{{ route("bundle-products.item-price") }}'
    };

    @if($bundle && $bundle->items->count() > 0)
        @foreach($bundle->items as $item)
            bundleItems.push({
                product_id: {{ $item->product_id }},
                variant_id: {{ $item->variant_id ?? 'null' }},
                unit_id: {{ $item->unit_id }},
                quantity: {{ $item->quantity }},
                product_name: '{{ addslashes($item->product->name ?? "") }}',
                product_sku: '{{ addslashes($item->product->sku ?? "") }}',
                product_type: '{{ $item->product->product_type ?? "simple" }}',
                variant_name: '{{ $item->variant_id ? addslashes($item->product->name . " - Variant") : "" }}'
            });
        @endforeach
        loadExistingItems();
    @endif

    function loadExistingItems() {
        bundleItems.forEach((item, index) => {
            fetchUnitsAndRenderCard(item, index);
        });
    }

    function fetchUnitsAndRenderCard(item, index) {
        let url = `${routes.getUnits}/${item.product_id}/units`;
        if (item.variant_id) {
            url += `?variant_id=${item.variant_id}`;
        }

        $.get(url, function(units) {
            item.units = units;
            renderProductCard(item, index);
            updatePricing();
        });
    }

    function renderProductCard(item, index) {
        const typeClass = item.variant_id ? 'variable' : 'simple';
        const typeBadge = item.variant_id ? 'VARIABLE' : 'SIMPLE';
        const displayName = item.variant_id ? (item.variant_name || item.product_name) : item.product_name;

        let unitsHtml = '';
        if (item.units && item.units.length > 0) {
            unitsHtml = item.units.map(unit => {
                const checked = unit.id == item.unit_id ? 'checked' : '';
                const priceDisplay = `$${unit.price.toFixed(2)}`;
                const parentDisplay = unit.parent_display ? ` ${unit.parent_display}` : '';
                return `
                    <label class="unit-option">
                        <input type="radio" name="item_unit_${index}" value="${unit.id}" data-price="${unit.price}" ${checked}>
                        ${unit.unit_name}${parentDisplay} - ${priceDisplay}
                    </label>
                `;
            }).join('');
        }

        const cardHtml = `
            <div class="bundle-product-card" data-index="${index}" data-product-id="${item.product_id}" data-variant-id="${item.variant_id}" data-unit-id="${item.unit_id}" data-quantity="${item.quantity}">
                <button type="button" class="btn btn-danger btn-sm remove-btn" data-index="${index}">Remove</button>
                <span class="product-badge ${typeClass}">${typeBadge}</span>
                <div class="product-name">${displayName}</div>
                <div class="product-sku">SKU: ${item.product_sku || 'N/A'}</div>
                
                <div class="units-section">
                    <label>Units</label>
                    ${unitsHtml || '<span class="text-muted">No units available</span>'}
                </div>
                
                <div class="quantity-section">
                    <label>Quantity</label>
                    <input type="number" class="form-control item-quantity" data-index="${index}" min="1" value="${item.quantity || 1}">
                </div>
            </div>
        `;

        const container = $('#bundle-products-container');
        const existingCard = container.find(`[data-index="${index}"]`);
        if (existingCard.length) {
            existingCard.replaceWith(cardHtml);
        } else {
            container.append(cardHtml);
        }

        $('#empty-state').hide();
    }

    $(document).on('click', '.remove-btn', function() {
        const index = $(this).data('index');
        bundleItems.splice(index, 1);
        rebuildCards();
        updatePricing();
    });

    function rebuildCards() {
        $('#bundle-products-container').empty();
        if (bundleItems.length === 0) {
            $('#empty-state').show();
        } else {
            bundleItems.forEach((item, index) => {
                renderProductCard(item, index);
            });
        }
    }

    $(document).on('change', 'input[name^="item_unit_"]', function() {
        const index = $(this).closest('.bundle-product-card').data('index');
        bundleItems[index].unit_id = parseInt($(this).val());
        updatePricing();
    });

    $(document).on('change keyup', '.item-quantity', function() {
        const index = $(this).data('index');
        bundleItems[index].quantity = parseInt($(this).val()) || 1;
        updatePricing();
    });

    $('input[name="pricing_mode"]').on('change', function() {
        const mode = $(this).val();
        if (mode === 'fixed') {
            $('#fixed_bundle_price').prop('disabled', false);
            $('#discount-section').hide();
        } else {
            $('#fixed_bundle_price').prop('disabled', true);
            $('#discount-section').show();
        }
        updatePricing();
    });

    $('input[name="discount_type"]').on('change', function() {
        const type = $(this).val();
        if (type === 'fixed') {
            $('#fixed_discount_amount').prop('disabled', false);
            $('#percentage_discount').prop('disabled', true);
        } else if (type === 'percentage') {
            $('#fixed_discount_amount').prop('disabled', true);
            $('#percentage_discount').prop('disabled', false);
        }
        updatePricing();
    });

    $('#fixed_bundle_price, #fixed_discount_amount, #percentage_discount').on('change keyup', function() {
        updatePricing();
    });

    function updatePricing() {
        let sumTotal = 0;

        bundleItems.forEach(item => {
            if (item.units) {
                const selectedUnit = item.units.find(u => u.id == item.unit_id);
                if (selectedUnit) {
                    sumTotal += selectedUnit.price * (item.quantity || 1);
                }
            }
        });

        $('#sum-total').text('$' + sumTotal.toFixed(2));

        let finalPrice = 0;
        const pricingMode = $('input[name="pricing_mode"]:checked').val();

        if (pricingMode === 'fixed') {
            finalPrice = parseFloat($('#fixed_bundle_price').val()) || 0;
        } else {
            finalPrice = sumTotal;
            const discountType = $('input[name="discount_type"]:checked').val();
            if (discountType === 'fixed') {
                finalPrice -= parseFloat($('#fixed_discount_amount').val()) || 0;
            } else if (discountType === 'percentage') {
                const pct = parseFloat($('#percentage_discount').val()) || 0;
                finalPrice -= (sumTotal * pct / 100);
            }
        }

        finalPrice = Math.max(0, finalPrice);
        $('#final-price').text('$' + finalPrice.toFixed(2));
        $('#total_amount').val(finalPrice);

        updateHiddenInputs();
    }

    function updateHiddenInputs() {
        const container = $('#hidden-inputs-container');
        container.empty();

        const pricingMode = $('input[name="pricing_mode"]:checked').val();
        container.append(`<input type="hidden" name="pricing_mode" value="${pricingMode}">`);

        if (pricingMode === 'fixed') {
            container.append(`<input type="hidden" name="fixed_bundle_price" value="${$('#fixed_bundle_price').val() || 0}">`);
        } else {
            const discountType = $('input[name="discount_type"]:checked').val();
            if (discountType) {
                container.append(`<input type="hidden" name="discount_type" value="${discountType}">`);
                if (discountType === 'fixed') {
                    container.append(`<input type="hidden" name="discount_value" value="${$('#fixed_discount_amount').val() || 0}">`);
                } else {
                    container.append(`<input type="hidden" name="discount_value" value="${$('#percentage_discount').val() || 0}">`);
                }
            }
        }

        bundleItems.forEach((item, index) => {
            container.append(`<input type="hidden" name="bundle_items[${index}][product_id]" value="${item.product_id}">`);
            container.append(`<input type="hidden" name="bundle_items[${index}][variant_id]" value="${item.variant_id || ''}">`);
            container.append(`<input type="hidden" name="bundle_items[${index}][unit_id]" value="${item.unit_id}">`);
            container.append(`<input type="hidden" name="bundle_items[${index}][quantity]" value="${item.quantity || 1}">`);
        });
    }

    if ($('input[name="pricing_mode"]:checked').val() === 'fixed') {
        $('#discount-section').hide();
    }
    updatePricing();

    $('.modal-product-type').on('click', function() {
        $('.modal-product-type').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        selectedProductType = $(this).find('input[type="radio"]').val();
    });

    $('#simple-product-select').select2({
        dropdownParent: $('#addProductModal'),
        ajax: {
            url: routes.searchProducts,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, type: 'simple' };
            },
            processResults: function(data) {
                return {
                    results: data.map(p => ({ id: p.id, text: p.name + ' (SKU: ' + p.sku + ')', product: p }))
                };
            }
        },
        placeholder: 'Search for a product...',
        minimumInputLength: 1
    });

    $('#variable-product-select').select2({
        dropdownParent: $('#addProductModal'),
        ajax: {
            url: routes.searchProducts,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, type: 'variable' };
            },
            processResults: function(data) {
                return {
                    results: data.map(p => ({ id: p.id, text: p.name + ' (SKU: ' + p.sku + ')', product: p }))
                };
            }
        },
        placeholder: 'Search for a variable product...',
        minimumInputLength: 1
    });

    $('#variant-select').select2({
        dropdownParent: $('#addProductModal'),
        placeholder: 'Search for a variable product...'
    });

    $('#variable-product-select').on('select2:select', function(e) {
        const productId = e.params.data.id;
        $('#variant-select-container').show();
        $('#variant-select').empty().append('<option value="">Loading variants...</option>');

        $.get(`${routes.getVariants}/${productId}/variants`, function(variants) {
            $('#variant-select').empty().append('<option value="">Select a variant...</option>');
            if (variants.length === 0) {
                $('#no-variants-msg').show();
            } else {
                $('#no-variants-msg').hide();
                variants.forEach(v => {
                    $('#variant-select').append(`<option value="${v.id}" data-sku="${v.sku}" data-name="${v.name}">${v.name}</option>`);
                });
            }
        });
    });

    function showModalStep(step) {
        currentModalStep = step;
        $('.modal-step').removeClass('active');

        if (step === 1) {
            $('#modal-step-1').addClass('active');
            $('#modal-back-btn').hide();
            $('#modal-next-btn').show();
            $('#modal-add-btn').hide();
        } else if (step === 2) {
            if (selectedProductType === 'simple') {
                $('#modal-step-2-simple').addClass('active');
            } else {
                $('#modal-step-2-variable').addClass('active');
            }
            $('#modal-back-btn').show();
            $('#modal-next-btn').hide();
            $('#modal-add-btn').show();
        }
    }

    $('#modal-next-btn').on('click', function() {
        if (currentModalStep === 1) {
            if (!selectedProductType) {
                alert('Please select a product type.');
                return;
            }
            showModalStep(2);
        }
    });

    $('#modal-back-btn').on('click', function() {
        if (currentModalStep === 2) {
            showModalStep(1);
        }
    });

    $('#modal-add-btn').on('click', function() {
        if (selectedProductType === 'simple') {
            const productData = $('#simple-product-select').select2('data')[0];
            if (!productData || !productData.id) {
                alert('Please select a product.');
                return;
            }

            if (bundleItems.some(item => item.product_id == productData.id && !item.variant_id)) {
                alert('This product is already in the bundle.');
                return;
            }

            const newItem = {
                product_id: productData.product.id,
                variant_id: null,
                unit_id: null,
                quantity: 1,
                product_name: productData.product.name,
                product_sku: productData.product.sku,
                product_type: 'simple'
            };

            bundleItems.push(newItem);
            fetchUnitsAndRenderCard(newItem, bundleItems.length - 1);
            closeAndResetModal();

        } else if (selectedProductType === 'variable') {
            const productData = $('#variable-product-select').select2('data')[0];
            const variantId = $('#variant-select').val();
            
            if (!variantId) {
                alert('Please select a variant.');
                return;
            }

            if (bundleItems.some(item => item.variant_id == variantId)) {
                alert('This variant is already in the bundle.');
                return;
            }

            const variantOption = $('#variant-select option:selected');
            const newItem = {
                product_id: productData.product.id,
                variant_id: parseInt(variantId),
                unit_id: null,
                quantity: 1,
                product_name: productData.product.name,
                product_sku: variantOption.data('sku'),
                product_type: 'variable',
                variant_name: variantOption.data('name')
            };

            bundleItems.push(newItem);
            fetchUnitsAndRenderCard(newItem, bundleItems.length - 1);
            closeAndResetModal();
        }
    });

    function closeAndResetModal() {
        $('#addProductModal').modal('hide');
        currentModalStep = 1;
        selectedProductType = null;
        $('.modal-product-type').removeClass('selected');
        $('input[name="product_type_choice"]').prop('checked', false);
        $('#simple-product-select').val(null).trigger('change');
        $('#variable-product-select').val(null).trigger('change');
        $('#variant-select').empty().append('<option value="">Select a variant...</option>');
        $('#variant-select-container').hide();
        showModalStep(1);
    }

    $('#addProductModal').on('hidden.bs.modal', function() {
        closeAndResetModal();
    });

    $('#productForm').on('submit', function(e) {
        if (bundleItems.length === 0) {
            e.preventDefault();
            alert('Please add at least one product to the bundle.');
            return false;
        }

        let hasUnselectedUnit = false;
        bundleItems.forEach((item, index) => {
            if (!item.unit_id) {
                hasUnselectedUnit = true;
            }
        });

        if (hasUnselectedUnit) {
            e.preventDefault();
            alert('Please select a unit for all products.');
            return false;
        }

        updateHiddenInputs();
        return true;
    });
});
</script>
@endpush