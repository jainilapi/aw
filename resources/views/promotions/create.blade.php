@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle, 'select2' => true, 'editor' => true, 'datepicker' => true])

@push('css')
<style>
    .type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .type-card:hover {
        border-color: var(--customizable-bg);
        transform: translateY(-2px);
    }
    .type-card.selected {
        border-color: var(--customizable-bg);
        background: rgba(var(--customizable-bg-rgb), 0.1);
    }
    .type-card .card-body {
        padding: 1rem;
    }
    .type-card .type-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: var(--customizable-bg);
    }
    .poster-preview {
        max-width: 100%;
        max-height: 400px;
        object-fit: contain;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }
    .poster-upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .poster-upload-zone:hover {
        border-color: var(--customizable-bg);
        background: rgba(var(--customizable-bg-rgb), 0.05);
    }
    .poster-upload-zone.has-image {
        padding: 0;
        border: none;
    }
    .conditional-section {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .conditional-section.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .ql-editor {
        min-height: 150px;
    }
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }
</style>
@endpush

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="promotionForm" method="POST" action="{{ route('promotions.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <!-- Type Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-tag me-2"></i>Promotion Type</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="type-card card h-100 text-center" data-type="catdisc">
                                <div class="card-body">
                                    <div class="type-icon"><i class="fa fa-folder-open"></i></div>
                                    <strong>Category Discount</strong>
                                    <small class="text-muted d-block">Discount on selected categories</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="type-card card h-100 text-center" data-type="prodisc">
                                <div class="card-body">
                                    <div class="type-icon"><i class="fa fa-box"></i></div>
                                    <strong>Product Discount</strong>
                                    <small class="text-muted d-block">Discount on selected products</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="type-card card h-100 text-center" data-type="cardisc">
                                <div class="card-body">
                                    <div class="type-icon"><i class="fa fa-shopping-cart"></i></div>
                                    <strong>Cart Discount</strong>
                                    <small class="text-muted d-block">Discount on cart amount</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="type-card card h-100 text-center" data-type="buyxgetx">
                                <div class="card-body">
                                    <div class="type-icon"><i class="fa fa-gift"></i></div>
                                    <strong>Buy X Get Y</strong>
                                    <small class="text-muted d-block">Buy product get another free</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="type" id="promotionType" value="{{ old('type', 'catdisc') }}" required>
                    @error('type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Promotion Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g., Summer Sale 2026" required>
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Promo Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code') }}" placeholder="e.g., SUMMER20" required>
                            @error('code')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Type-Specific Fields -->
            <!-- Category Discount -->
            <div class="conditional-section" id="section-catdisc">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-folder-open me-2"></i>Category Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Categories <span class="text-danger">*</span></label> <br>
                            <select name="category_id[]" id="categorySelect" class="form-select" multiple data-placeholder="Search and select categories...">
                            </select>
                            @error('category_id')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                                <select name="discount_type" class="form-select discount-type-select type-field">
                                    <option value="0" {{ old('discount_type') == '0' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="1" {{ old('discount_type') == '1' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="discount_amount" class="form-control type-field" value="{{ old('discount_amount') }}" step="0.01" min="0" placeholder="Enter value">
                                    <span class="input-group-text discount-symbol">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Discount -->
            <div class="conditional-section" id="section-prodisc">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-box me-2"></i>Product Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Products <span class="text-danger">*</span></label> <br>
                            <select name="product_id[]" id="productSelect" class="form-select" multiple data-placeholder="Search and select products...">
                            </select>
                            @error('product_id')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        
                        <!-- Dynamic Variants Container -->
                        <div id="variantsContainer" class="mb-3" style="display: none;">
                            <label class="form-label">Select Variants</label>
                            <select name="variant_id[]" id="variantSelect" class="form-select" multiple data-placeholder="Select variants...">
                            </select>
                        </div>
                        
                        <!-- Dynamic Units Container -->
                        <div id="unitsContainer" class="mb-3" style="display: none;">
                            <label class="form-label">Select Units</label>
                            <select name="unit_id[]" id="unitSelect" class="form-select" multiple data-placeholder="Select units...">
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                                <select name="discount_type" class="form-select discount-type-select prodisc-discount-type type-field">
                                    <option value="0">Percentage (%)</option>
                                    <option value="1">Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="discount_amount" class="form-control prodisc-discount-amount type-field" step="0.01" min="0" placeholder="Enter value">
                                    <span class="input-group-text discount-symbol prodisc-discount-symbol">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Discount -->
            <div class="conditional-section" id="section-cardisc">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-shopping-cart me-2"></i>Cart Amount Discount</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Minimum Cart Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="cart_minimum_amount" class="form-control" value="{{ old('cart_minimum_amount') }}" step="0.01" min="0" placeholder="Enter minimum cart total">
                                </div>
                                @error('cart_minimum_amount')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                                <select name="discount_type" class="form-select discount-type-select cardisc-discount-type type-field">
                                    <option value="0">Percentage (%)</option>
                                    <option value="1">Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="discount_amount" class="form-control cardisc-discount-amount type-field" step="0.01" min="0" placeholder="Enter value">
                                    <span class="input-group-text discount-symbol cardisc-discount-symbol">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buy X Get Y -->
            <div class="conditional-section" id="section-buyxgetx">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-gift me-2"></i>Buy X Get Y Configuration</h5>
                    </div>
                    <div class="card-body">
                        <!-- Product X (To Buy) -->
                        <div class="border rounded p-3 mb-4 bg-light">
                            <h6 class="text-primary mb-3"><i class="fa fa-shopping-basket me-2"></i>Product to Buy (X)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Select Product <span class="text-danger">*</span></label> <br>
                                    <select name="x_product" id="xProductSelect" class="form-select" data-placeholder="Search product...">
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="xVariantContainer" style="display: none;">
                                    <label class="form-label">Select Variant</label>
                                    <select name="x_variant" id="xVariantSelect" class="form-select">
                                        <option value="">Select Variant</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="xUnitContainer" style="display: none;">
                                    <label class="form-label">Select Unit <span class="text-danger">*</span></label>
                                    <select name="x_unit" id="xUnitSelect" class="form-select">
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantity to Buy <span class="text-danger">*</span></label>
                                    <input type="number" name="x_quantity" class="form-control" value="{{ old('x_quantity', 1) }}" min="1" placeholder="e.g., 2">
                                </div>
                            </div>
                        </div>

                        <!-- Product Y (To Get) -->
                        <div class="border rounded p-3 bg-light">
                            <h6 class="text-success mb-3"><i class="fa fa-gift me-2"></i>Product to Get Free (Y)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Select Product <span class="text-danger">*</span></label>
                                    <select name="y_item" id="yProductSelect" class="form-select" data-placeholder="Search product...">
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="yVariantContainer" style="display: none;">
                                    <label class="form-label">Select Variant</label>
                                    <select name="y_variant" id="yVariantSelect" class="form-select">
                                        <option value="">Select Variant</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="yUnitContainer" style="display: none;">
                                    <label class="form-label">Select Unit <span class="text-danger">*</span></label>
                                    <select name="y_unit" id="yUnitSelect" class="form-select">
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantity to Get <span class="text-danger">*</span></label>
                                    <input type="number" name="y_quantity" class="form-control" value="{{ old('y_quantity', 1) }}" min="1" placeholder="e.g., 1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rich Text Editors -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-align-left me-2"></i>Details & Terms</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <div id="descriptionEditor" class="form-control" style="min-height: 150px;">{!! old('description') !!}</div>
                        <input type="hidden" name="description" id="descriptionInput">
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">How to Use <span class="text-danger">*</span></label>
                        <div id="howToUseEditor" class="form-control" style="min-height: 150px;">{!! old('how_to_use') !!}</div>
                        <input type="hidden" name="how_to_use" id="howToUseInput">
                        @error('how_to_use')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Terms & Conditions <span class="text-danger">*</span></label>
                        <div id="termsEditor" class="form-control" style="min-height: 150px;">{!! old('terms_and_condition') !!}</div>
                        <input type="hidden" name="terms_and_condition" id="termsInput">
                        @error('terms_and_condition')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Validity & Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-calendar me-2"></i>Validity & Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="text" name="start_date" id="startDate" class="form-control" value="{{ old('start_date') }}" placeholder="Select start date & time" required readonly>
                            @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date & Time <span class="text-danger">*</span></label>
                            <input type="text" name="end_date" id="endDate" class="form-control" value="{{ old('end_date') }}" placeholder="Select end date & time" required readonly>
                            @error('end_date')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Application Limit <span class="text-danger">*</span></label>
                            <input type="number" name="application_limit" class="form-control" value="{{ old('application_limit', 1) }}" min="1" required>
                            <small class="text-muted">Times each customer can use this</small>
                            @error('application_limit')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">Auto Applicable</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="autoApplicable" name="auto_applicable" value="1" {{ old('auto_applicable') ? 'checked' : '' }}>
                                {{-- <label class="form-check-label" for="autoApplicable">Apply automatically to cart</label> --}}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', true) ? 'checked' : '' }}>
                                {{-- <label class="form-check-label" for="status">Active</label> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Poster Upload -->
            <div class="card mb-4 sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-image me-2"></i>Promotion Poster</h5>
                </div>
                <div class="card-body">
                    <div class="poster-upload-zone" id="posterUploadZone">
                        <input type="file" name="poster" id="posterInput" class="d-none" accept="image/jpeg,image/png,image/webp" required>
                        <div id="posterPlaceholder">
                            <i class="fa fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-1">Click or drag to upload poster</p>
                            <small class="text-muted">Recommended: 800 × 1200 pixels</small>
                            <small class="text-muted d-block">Max size: 5MB</small>
                        </div>
                        <img id="posterPreview" src="" class="poster-preview d-none" alt="Poster Preview">
                    </div>
                    @error('poster')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="changePoster" style="display: none;">
                            <i class="fa fa-sync me-1"></i> Change Poster
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">
                        Create
                    </button>
                    <a href="{{ route('promotions.index') }}" class="btn btn-secondary w-100">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('js')
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize Quill editors
    const descriptionEditor = new Quill('#descriptionEditor', { theme: 'snow' });
    const howToUseEditor = new Quill('#howToUseEditor', { theme: 'snow' });
    const termsEditor = new Quill('#termsEditor', { theme: 'snow' });

    let type = $('#promotionType').val();
    $('#section-' + type).addClass('active');
    $('.type-card[data-type="' + type + '"]').addClass('selected');

    enableFieldsInSection(type);

    // Initialize DateTimePickers
    $('#startDate, #endDate').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: false,
        autoApply: true,
        locale: {
            format: 'YYYY-MM-DD HH:mm'
        }
    });

    // Type selection
    $('.type-card').on('click', function () {
        let type = $(this).data('type');

        $('.type-card').removeClass('selected');
        $(this).addClass('selected');

        $('.conditional-section').removeClass('active');
        $('#section-' + type).addClass('active');

        $('#promotionType').val(type);

        enableFieldsInSection(type);
    });


    // Initialize with first type selected
    $('.type-card[data-type="{{ old('type', 'catdisc') }}"]').click();

    // Category Select2 with pagination
    $('#categorySelect').select2({
        placeholder: 'Search and select categories...',
        allowClear: true,
        ajax: {
            url: '{{ route('promotions.categories') }}',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: function(params) {
                return { searchQuery: params.term, page: params.page || 1 };
            },
            processResults: function(data) {
                return { results: data.items, pagination: { more: data.pagination.more } };
            }
        }
    });

    // Product Select2 with pagination (multi)
    $('#productSelect').select2({
        placeholder: 'Search and select products...',
        width: '100%',
        allowClear: true,
        ajax: {
            url: '{{ route('promotions.products') }}',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: function(params) {
                return { searchQuery: params.term, page: params.page || 1 };
            },
            processResults: function(data) {
                return { 
                    results: data.items.map(item => ({
                        id: item.id,
                        text: item.text + (item.type === 'variable' ? ' [Variable]' : ' [Simple]'),
                        type: item.type
                    })), 
                    pagination: { more: data.pagination.more } 
                };
            }
        }
    });

    // Handle product selection for variants/units in prodisc
    $('#productSelect').on('change', function() {
        const selected = $(this).select2('data');
        const hasVariable = selected.some(p => p.type === 'variable');
        
        if (hasVariable) {
            // Fetch variants for variable products
            const variableProductIds = selected.filter(p => p.type === 'variable').map(p => p.id);
            loadVariantsForProducts(variableProductIds);
            $('#variantsContainer').show();
        } else {
            $('#variantsContainer').hide();
            $('#variantSelect').empty();
        }
        
        // For simple products, load units directly
        const simpleProductIds = selected.filter(p => p.type === 'simple').map(p => p.id);
        if (simpleProductIds.length > 0) {
            loadUnitsForSimpleProducts(simpleProductIds);
            $('#unitsContainer').show();
        } else if (!hasVariable) {
            $('#unitsContainer').hide();
        }
    });

    function loadVariantsForProducts(productIds) {
        const promises = productIds.map(id => 
            $.get(`{{ url('admin/promotions/variants') }}/${id}`)
        );
        
        Promise.all(promises).then(results => {
            let allVariants = [];
            results.forEach(result => {
                if (result.variants) {
                    allVariants = allVariants.concat(result.variants);
                }
            });
            
            const $variantSelect = $('#variantSelect');
            $variantSelect.empty();
            allVariants.forEach(v => {
                $variantSelect.append(new Option(v.text, v.id, false, false));
            });
            $variantSelect.trigger('change');
        });
    }

    function loadUnitsForSimpleProducts(productIds) {
        const promises = productIds.map(id => 
            $.get(`{{ url('admin/promotions/units/simple') }}/${id}`)
        );
        
        Promise.all(promises).then(results => {
            let allUnits = [];
            results.forEach(result => {
                if (result.units) {
                    allUnits = allUnits.concat(result.units);
                }
            });
            
            const $unitSelect = $('#unitSelect');
            $unitSelect.empty();
            allUnits.forEach(u => {
                $unitSelect.append(new Option(u.text, u.id, false, false));
            });
        });
    }

    // Handle variant selection for units
    $('#variantSelect').on('change', function() {
        const variantIds = $(this).val();
        if (variantIds && variantIds.length > 0) {
            loadUnitsForVariants(variantIds);
            $('#unitsContainer').show();
        }
    });

    function loadUnitsForVariants(variantIds) {
        const promises = variantIds.map(id => 
            $.get(`{{ url('admin/promotions/units/variant') }}/${id}`)
        );
        
        Promise.all(promises).then(results => {
            let allUnits = [];
            results.forEach(result => {
                if (result.units) {
                    allUnits = allUnits.concat(result.units);
                }
            });
            
            const $unitSelect = $('#unitSelect');
            $unitSelect.empty();
            allUnits.forEach(u => {
                $unitSelect.append(new Option(u.text, u.id, false, false));
            });
        });
    }

    // X Product Select2 (single) for Buy X Get Y
    $('#xProductSelect, #yProductSelect').select2({
        placeholder: 'Search product...',
        width: '100%',
        allowClear: true,
        ajax: {
            url: '{{ route('promotions.products') }}',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: function(params) {
                return { searchQuery: params.term, page: params.page || 1 };
            },
            processResults: function(data) {
                return { 
                    results: data.items.map(item => ({
                        id: item.id,
                        text: item.text + (item.type === 'variable' ? ' [Variable]' : ' [Simple]'),
                        type: item.type
                    })), 
                    pagination: { more: data.pagination.more } 
                };
            }
        }
    });

    // Handle X product selection
    $('#xProductSelect').on('change', function() {
        const data = $(this).select2('data')[0];
        if (!data) return;
        
        if (data.type === 'variable') {
            // Load variants
            $.get(`{{ url('admin/promotions/variants') }}/${data.id}`, function(result) {
                const $select = $('#xVariantSelect');
                $select.empty().append('<option value="">Select Variant</option>');
                result.variants.forEach(v => {
                    $select.append(new Option(v.text, v.id, false, false));
                });
                $('#xVariantContainer').show();
                $('#xUnitContainer').hide();
            });
        } else {
            // Load units directly
            $.get(`{{ url('admin/promotions/units/simple') }}/${data.id}`, function(result) {
                const $select = $('#xUnitSelect');
                $select.empty().append('<option value="">Select Unit</option>');
                result.units.forEach(u => {
                    $select.append(new Option(u.text, u.id, false, false));
                });
                $('#xVariantContainer').hide();
                $('#xUnitContainer').show();
            });
        }
    });

    // Handle X variant selection
    $('#xVariantSelect').on('change', function() {
        const variantId = $(this).val();
        if (!variantId) return;
        
        $.get(`{{ url('admin/promotions/units/variant') }}/${variantId}`, function(result) {
            const $select = $('#xUnitSelect');
            $select.empty().append('<option value="">Select Unit</option>');
            result.units.forEach(u => {
                $select.append(new Option(u.text, u.id, false, false));
            });
            $('#xUnitContainer').show();
        });
    });

    // Handle Y product selection
    $('#yProductSelect').on('change', function() {
        const data = $(this).select2('data')[0];
        if (!data) return;
        
        if (data.type === 'variable') {
            $.get(`{{ url('admin/promotions/variants') }}/${data.id}`, function(result) {
                const $select = $('#yVariantSelect');
                $select.empty().append('<option value="">Select Variant</option>');
                result.variants.forEach(v => {
                    $select.append(new Option(v.text, v.id, false, false));
                });
                $('#yVariantContainer').show();
                $('#yUnitContainer').hide();
            });
        } else {
            $.get(`{{ url('admin/promotions/units/simple') }}/${data.id}`, function(result) {
                const $select = $('#yUnitSelect');
                $select.empty().append('<option value="">Select Unit</option>');
                result.units.forEach(u => {
                    $select.append(new Option(u.text, u.id, false, false));
                });
                $('#yVariantContainer').hide();
                $('#yUnitContainer').show();
            });
        }
    });

    // Handle Y variant selection
    $('#yVariantSelect').on('change', function() {
        const variantId = $(this).val();
        if (!variantId) return;
        
        $.get(`{{ url('admin/promotions/units/variant') }}/${variantId}`, function(result) {
            const $select = $('#yUnitSelect');
            $select.empty().append('<option value="">Select Unit</option>');
            result.units.forEach(u => {
                $select.append(new Option(u.text, u.id, false, false));
            });
            $('#yUnitContainer').show();
        });
    });

    // Discount type toggle
    $('.discount-type-select').on('change', function() {
        const symbol = $(this).val() === '1' ? '₹' : '%';
        $(this).closest('.row').find('.discount-symbol').text(symbol);
    });

    // Poster upload handling
    const posterZone = $('#posterUploadZone');
    const posterInput = $('#posterInput');
    const posterPreview = $('#posterPreview');
    const posterPlaceholder = $('#posterPlaceholder');
    const changePosterBtn = $('#changePoster');

    posterZone.on('click', function (e) {
        if (!posterPreview.hasClass('d-none')) return;

        e.stopPropagation();
        posterInput.trigger('click');
    });

    posterInput.on('click', function (e) {
        e.stopPropagation();
    });

    changePosterBtn.on('click', function() {
        posterInput.click();
    });

    posterInput.on('change', function() {
        const file = this.files[0];
        if (!file) return;
        
        // Validate file type
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            Swal.fire('Error', 'Please upload a valid image (JPEG, PNG, or WebP)', 'error');
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Error', 'Image size must be less than 5MB', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            // Check dimensions
            const img = new Image();
            img.onload = function() {
                if (img.width !== 800 || img.height !== 1200) {
                    Swal.fire({
                        title: 'Dimension Warning',
                        text: `Recommended size is 800×1200 pixels. Your image is ${img.width}×${img.height}.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Use Anyway',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showPosterPreview(e.target.result);
                        } else {
                            posterInput.val('');
                        }
                    });
                } else {
                    showPosterPreview(e.target.result);
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    function showPosterPreview(src) {
        posterPreview.attr('src', src).removeClass('d-none');
        posterPlaceholder.addClass('d-none');
        posterZone.addClass('has-image');
        changePosterBtn.show();
    }

    // Drag and drop
    posterZone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-primary');
    }).on('dragleave', function() {
        $(this).removeClass('border-primary');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary');
        const file = e.originalEvent.dataTransfer.files[0];
        if (file) {
            posterInput[0].files = e.originalEvent.dataTransfer.files;
            posterInput.trigger('change');
        }
    });

    function disableAllTypeFields() {
        $('.type-field').prop('disabled', true);
    }

    function enableFieldsInSection(type) {
        disableAllTypeFields();

        $('#section-' + type).find('.type-field').prop('disabled', false);
    }

    // Form validation and submission
    $('#promotionForm').validate({
        rules: {
            name: { required: true },
            code: { required: true },
            start_date: { required: true },
            end_date: { required: true },
            application_limit: { required: true, min: 1 }
        },
        errorPlacement: function(error, element) {
            error.addClass('text-danger small');
            error.insertAfter(element.closest('.input-group').length ? element.closest('.input-group') : element);
        },
        submitHandler: function(form) {
            // Populate hidden inputs from Quill editors
            $('#descriptionInput').val(descriptionEditor.root.innerHTML.trim());
            $('#howToUseInput').val(howToUseEditor.root.innerHTML.trim());
            $('#termsInput').val(termsEditor.root.innerHTML.trim());

            // Check required editors
            if (descriptionEditor.getText().trim().length === 0) {
                Swal.fire('Error', 'Description is required', 'error');
                return false;
            }
            if (howToUseEditor.getText().trim().length === 0) {
                Swal.fire('Error', 'How to Use is required', 'error');
                return false;
            }
            if (termsEditor.getText().trim().length === 0) {
                Swal.fire('Error', 'Terms & Conditions is required', 'error');
                return false;
            }

            form.submit();
        }
    });
});
</script>
@endpush
