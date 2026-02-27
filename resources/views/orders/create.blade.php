@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@push('css')
    <style>
        .wrapper:before {
            height: 200px!important;
        }
        .order-section {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .order-section .section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
            background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%);
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .order-section .section-header h5 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        .order-section .section-body {
            padding: 1.5rem;
        }
        .item-row {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
        }
        .item-row:hover {
            background: #e9ecef;
        }
        .item-row .item-product {
            flex: 2;
        }
        .item-row .item-qty,
        .item-row .item-price,
        .item-row .item-discount,
        .item-row .item-total {
            flex: 1;
        }
        .item-row .item-actions {
            flex: 0 0 50px;
            text-align: center;
        }
        .order-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
        }
        .order-summary .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .order-summary .summary-row.total {
            font-size: 1.25rem;
            font-weight: bold;
            border-bottom: none;
            margin-top: 0.5rem;
        }
        .address-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            height: 100%;
        }
        .address-card h6 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        #itemsContainer .item-row:first-child .remove-item {
            display: block;
        }
    </style>
@endpush

@section('content')
    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
        @csrf

        <!-- Customer Selection -->
        <div class="order-section">
            <div class="section-header d-flex justify-content-between align-items-center">
                <h5><i class="fa fa-user me-2"></i>Customer</h5>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customerSelect" class="form-control" required>
                            <option value="">Search and select customer...</option>
                        </select>
                        @error('customer_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Select Saved Address</label>
                        <select id="savedAddressSelect" class="form-control" disabled>
                            <option value="">Select customer first...</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Shipping Address -->
            <div class="col-md-6">
                <div class="order-section">
                    <div class="section-header">
                        <h5><i class="fa fa-truck me-2"></i>Shipping Address</h5>
                    </div>
                    <div class="section-body">
                        @include('orders.partials._address_form', [
                            'prefix' => 'shipping',
                            'recipientLabel' => 'Recipient'
                        ])
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="col-md-6">
                <div class="order-section">
                    <div class="section-header d-flex justify-content-between align-items-center">
                        <h5><i class="fa fa-file-invoice me-2"></i>Billing Address</h5>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="sameAsShipping" checked>
                            <label class="form-check-label" for="sameAsShipping">Same as Shipping</label>
                        </div>
                    </div>
                    <div class="section-body" id="billingAddressSection">
                        @include('orders.partials._address_form', [
                            'prefix' => 'billing',
                            'recipientLabel' => 'Billing Name'
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="order-section">
            <div class="section-header d-flex justify-content-between align-items-center">
                <h5><i class="fa fa-shopping-cart me-2"></i>Order Items</h5>
                <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                    <i class="fa fa-plus me-1"></i>Add Item
                </button>
            </div>
            <div class="section-body">
                <div class="d-none d-md-flex gap-2 mb-3 px-3 text-muted small">
                    <div style="flex: 2">Product</div>
                    <div style="flex: 1">Variant</div>
                    <div style="flex: 1">Unit</div>
                    <div style="flex: 1">Warehouse</div>
                    <div style="flex: 0 0 80px">Stock</div>
                    <div style="flex: 0 0 80px">Qty</div>
                    <div style="flex: 1">Price</div>
                    <div style="flex: 1">Tax Slab</div>
                    <div style="flex: 0 0 80px">Discount</div>
                    <div style="flex: 1">Total</div>
                    <div style="flex: 0 0 40px"></div>
                </div>
                <div id="itemsContainer">
                    <!-- Items will be added here dynamically -->
                </div>
                @error('items')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <!-- Payment & Notes -->
            <div class="col-md-6">
                <div class="order-section">
                    <div class="section-header">
                        <h5><i class="fa fa-credit-card me-2"></i>Payment & Notes</h5>
                    </div>
                    <div class="section-body">
                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-control" required>
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Cost</label>
                            <input type="number" name="shipping_total" class="form-control" id="shippingTotal" value="0" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Notes visible to customer..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="internal_notes" class="form-control" rows="2" placeholder="Internal notes (not visible to customer)..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-md-6">
                <div class="order-section">
                    <div class="section-header">
                        <h5><i class="fa fa-calculator me-2"></i>Order Summary</h5>
                    </div>
                    <div class="section-body">
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span id="summarySubtotal">$0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax</span>
                                <span id="summaryTax">$0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span id="summaryShipping">$0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Discount</span>
                                <span id="summaryDiscount">-$0.00</span>
                            </div>
                            <div class="summary-row total">
                                <span>Grand Total</span>
                                <span id="summaryGrandTotal">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                        <i class="fa fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Create Order
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Item Row Template -->
    <template id="itemRowTemplate">
        <div class="item-row" data-index="__INDEX__">
            <input type="hidden" class="product-type-hidden" value="">
            <div class="item-product" style="flex: 2">
                <select name="items[__INDEX__][product_id]" class="form-control product-select" required>
                    <option value="">Search product...</option>
                </select>
            </div>
            <div class="item-variant" style="flex: 1">
                <select name="items[__INDEX__][variant_id]" class="form-control variant-select" disabled>
                    <option value="">N/A</option>
                </select>
            </div>
            <div class="item-unit" style="flex: 1">
                <select name="items[__INDEX__][unit_id]" class="form-control unit-select" required disabled>
                    <option value="">Select unit...</option>
                </select>
            </div>
            <div class="item-warehouse" style="flex: 1">
                <select name="items[__INDEX__][warehouse_id]" class="form-control warehouse-select">
                    <option value="">Select...</option>
                </select>
            </div>
            <div class="item-stock" style="flex: 0 0 80px">
                <input type="text" class="form-control stock-display" value="-" readonly disabled>
            </div>
            <div class="item-qty" style="flex: 0 0 80px">
                <input type="number" name="items[__INDEX__][quantity]" class="form-control item-quantity" value="1" min="1" required>
            </div>
            <div class="item-price" style="flex: 1">
                <input type="number" name="items[__INDEX__][unit_price]" class="form-control item-price-input" value="0" min="0" step="0.01" required>
            </div>
            <div class="item-tax-slab" style="flex: 1">
                <select name="items[__INDEX__][tax_slab_id]" class="form-control tax-slab-select" required>
                    <option value="">-- Select Tax Slab --</option>
                    @foreach($taxSlabs as $taxSlab)
                        <option value="{{ $taxSlab->id }}" data-percentage="{{ $taxSlab->tax_percentage }}">{{ $taxSlab->name }} ({{ $taxSlab->tax_percentage }}%)</option>
                    @endforeach
                </select>
                <input type="hidden" name="items[__INDEX__][tax_amount]" class="item-tax-amount-hidden" value="0">
            </div>
            <div class="item-discount" style="flex: 0 0 80px">
                <input type="number" name="items[__INDEX__][discount_amount]" class="form-control item-discount-input" value="0" min="0" step="0.01">
            </div>
            <div class="item-total" style="flex: 1">
                <input type="text" class="form-control item-total-display" value="$0.00" readonly>
            </div>
            <div class="item-actions" style="flex: 0 0 40px">
                <button type="button" class="btn btn-danger btn-sm remove-item">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    </template>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            let itemIndex = 0;
            const carribianCountries = @json(\App\Helpers\Helper::$carribianCountries);
            const taxSlabsData = @json($taxSlabs);

            // Initialize customer select
            $('#customerSelect').select2({
                placeholder: 'Search customer by name, email, or phone...',
                allowClear: true,
                ajax: {
                    url: '{{ route("orders.customers") }}',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term,
                            _token: '{{ csrf_token() }}'
                        };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    }
                }
            });

            $('#savedAddressSelect').select2({
                placeholder: 'Select saved address...',
                allowClear: true,
            });

            // Initialize generic select2s
            $('.select2').not('#customerSelect, #savedAddressSelect, .product-select, .unit-select, [name$="_state_id"], [name$="_city_id"]').select2({
                placeholder: 'Select option',
                allowClear: true,
                width: '100%'
            });

            // Handle Country Change logic (UI only)
            function handleCountryChange(element) {
                const countryId = $(element).val();
                const prefix = $(element).attr('name').replace('_country_id', '');
                const stateSelect = $(`select[name="${prefix}_state_id"]`);
                const citySelect = $(`select[name="${prefix}_city_id"]`);
                const cityContainer = citySelect.closest('.col-md-6');
                const stateLabelEl = stateSelect.siblings('label');
                const isCaribbean = carribianCountries.includes(parseInt(countryId));

                // Reset dependent fields
                stateSelect.val(null).trigger('change');
                citySelect.val(null).trigger('change');

                if (isCaribbean) {
                    stateLabelEl.html('Parish <span class="text-danger">*</span>');
                    cityContainer.hide();
                    citySelect.prop('required', false);
                } else {
                    stateLabelEl.html('State <span class="text-danger">*</span>');
                    cityContainer.show();
                    citySelect.prop('required', true);
                }
            }

            // Bind change event to country selects
            $('select[name$="_country_id"]').on('change', function () {
                handleCountryChange(this);
            });

            // Initialize State Select2 with AJAX
            $('select[name$="_state_id"]').each(function() {
                const prefix = $(this).attr('name').replace('_state_id', '');
                
                $(this).select2({
                    placeholder: 'Select State/Parish',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: "{{ route('state-list') }}",
                        type: "POST",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                country_id: $(`select[name="${prefix}_country_id"]`).val(),
                                _token: "{{ csrf_token() }}"
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: $.map(data.items, function(item) {
                                    return {
                                        id: item.id,
                                        text: item.text
                                    };
                                }),
                                pagination: {
                                    more: data.pagination.more
                                }
                            };
                        },
                        cache: true
                    }
                });
            });

            // Initialize City Select2 with AJAX
            $('select[name$="_city_id"]').each(function() {
                const prefix = $(this).attr('name').replace('_city_id', '');

                $(this).select2({
                    placeholder: 'Select City',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: "{{ route('city-list') }}",
                        type: "POST",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                state_id: $(`select[name="${prefix}_state_id"]`).val(),
                                _token: "{{ csrf_token() }}"
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: $.map(data.items, function(item) {
                                    return {
                                        id: item.id,
                                        text: item.text
                                    };
                                }),
                                pagination: {
                                    more: data.pagination.more
                                }
                            };
                        },
                        cache: true
                    }
                });
            });

            // On customer change, load saved addresses
            $('#customerSelect').on('change', function () {
                const customerId = $(this).val();
                const addressSelect = $('#savedAddressSelect');

                if (!customerId) {
                    addressSelect.html('<option value="">Select customer first...</option>').prop('disabled', true);
                    return;
                }

                addressSelect.html('<option value="">Loading...</option>');

                $.get('{{ url("admin/orders/customer-locations") }}/' + customerId, function (response) {
                    let options = '<option value="">Select saved address...</option>';
                    response.locations.forEach(function (loc) {
                        options += `<option value="${loc.id}" data-address='${JSON.stringify(loc)}'>${loc.name || loc.address_line_1}</option>`;
                    });
                    addressSelect.html(options).prop('disabled', false);
                });
            });

            // On saved address change, fill shipping form
            $('#savedAddressSelect').on('change', function () {
                const option = $(this).find(':selected');
                const address = option.data('address');

                if (address) {
                    fillAddressForm('shipping', address);
                    
                    if ($('#sameAsShipping').prop('checked')) {
                        copyShippingToBilling();
                    }
                }
            });

            function fillAddressForm(prefix, address) {
                $(`input[name="${prefix}_address_line_1"]`).val(address.address_line_1 || '');
                $(`input[name="${prefix}_address_line_2"]`).val(address.address_line_2 || '');
                $(`select[name="${prefix}_country_id"]`).val(address.country_id).trigger('change');
                $(`input[name="${prefix}_zipcode"]`).val(address.zipcode || '');
                $(`input[name="${prefix.replace('shipping', 'recipient').replace('billing', 'billing')}_name"]`).val(address.contact_name || '');
                $(`input[name="${prefix.replace('shipping', 'recipient').replace('billing', 'billing')}_contact_number"]`).val(address.contact_number || '');
                $(`input[name="${prefix.replace('shipping', 'recipient').replace('billing', 'billing')}_email"]`).val(address.email || '');

                // For AJAX Select2, we need to create the option if it doesn't exist and select it
                if (address.state_id) {
                     // We might not have the state name here if the address object doesn't include it.
                    // Assuming address object has state relation loaded or we just set ID and let user see ID?
                    // Usually saved addresses endpoint should return state name too.
                    // If not, we just set val for now. If select2 doesn't have the option, it won't show text.
                    // Ideally we fetch the state details or append option.
                    
                     // NOTE: Assuming address.state and address.city objects are present in the response
                    if (address.state) {
                         const stateOption = new Option(address.state.name, address.state.id, true, true);
                         $(`select[name="${prefix}_state_id"]`).append(stateOption).trigger('change');
                    } else if (address.state_id) {
                         // Fallback if name is missing (might show empty or ID depending on config)
                         // Ideally we should have the name.
                    }

                    if (address.city) {
                         const cityOption = new Option(address.city.name, address.city.id, true, true);
                         $(`select[name="${prefix}_city_id"]`).append(cityOption).trigger('change');
                    }
                }
            }
            
            // Note: The customer locations endpoint needs to return state and city relations for the above to work perfectly.
            // If strictly following scope, we can assume the backend returns what is needed or we just trigger change.

            // Same as shipping checkbox
            $('#sameAsShipping').on('change', function () {
                if ($(this).prop('checked')) {
                    copyShippingToBilling();
                    $('#billingAddressSection').find('input, select').prop('readonly', true);
                     $('#billingAddressSection').find('select').prop('disabled', true);
                } else {
                    $('#billingAddressSection').find('input, select').prop('readonly', false);
                    $('#billingAddressSection').find('select').prop('disabled', false);
                }
            });

            function copyShippingToBilling() {
                $('input[name="billing_address_line_1"]').val($('input[name="shipping_address_line_1"]').val());
                $('input[name="billing_address_line_2"]').val($('input[name="shipping_address_line_2"]').val());
                
                const shippingCountry = $('select[name="shipping_country_id"]').val();
                $('select[name="billing_country_id"]').val(shippingCountry).trigger('change');
                
                $('input[name="billing_zipcode"]').val($('input[name="shipping_zipcode"]').val());
                $('input[name="billing_name"]').val($('input[name="recipient_name"]').val());
                $('input[name="billing_contact_number"]').val($('input[name="recipient_contact_number"]').val());
                $('input[name="billing_email"]').val($('input[name="recipient_email"]').val());

                setTimeout(() => {
                    const shippingStateSelect = $('select[name="shipping_state_id"]');
                    const shippingStateId = shippingStateSelect.val();
                    const shippingStateText = shippingStateSelect.find('option:selected').text();
                    
                    const billingStateSelect = $('select[name="billing_state_id"]');
                    
                    if (shippingStateId) {
                         // Check if option exists
                         if (billingStateSelect.find(`option[value="${shippingStateId}"]`).length === 0) {
                             const newOption = new Option(shippingStateText, shippingStateId, true, true);
                             billingStateSelect.append(newOption);
                         }
                         billingStateSelect.val(shippingStateId).trigger('change');
                    }

                    setTimeout(() => {
                        const shippingCitySelect = $('select[name="shipping_city_id"]');
                        const shippingCityId = shippingCitySelect.val();
                        const shippingCityText = shippingCitySelect.find('option:selected').text();

                        const billingCitySelect = $('select[name="billing_city_id"]');
                        
                        if (shippingCityId) {
                             if (billingCitySelect.find(`option[value="${shippingCityId}"]`).length === 0) {
                                  const newOption = new Option(shippingCityText, shippingCityId, true, true);
                                  billingCitySelect.append(newOption);
                             }
                             billingCitySelect.val(shippingCityId).trigger('change');
                        }
                    }, 500);
                }, 500);
            }

            // Watch shipping fields for sync
            $('[name^="shipping_"], [name="recipient_name"], [name="recipient_contact_number"], [name="recipient_email"]').on('change keyup', function () {
                if ($('#sameAsShipping').prop('checked')) {
                    copyShippingToBilling();
                }
            });

            // Add first item row
            addItemRow();

            // Add item button
            $('#addItemBtn').on('click', function () {
                addItemRow();
            });

            function addItemRow() {
                const template = $('#itemRowTemplate').html().replace(/__INDEX__/g, itemIndex);
                $('#itemsContainer').append(template);
                initializeItemRow(itemIndex);
                itemIndex++;
            }

            function initializeItemRow(index) {
                const row = $(`.item-row[data-index="${index}"]`);

                // Product select
                row.find('.product-select').select2({
                    placeholder: 'Search product...',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("orders.products") }}',
                        type: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                                _token: '{{ csrf_token() }}'
                            };
                        },
                        processResults: function (data) {
                            return { results: data.results };
                        }
                    }
                }).on('select2:select', function (e) {
                    const product = e.params.data;
                    row.find('.product-type-hidden').val(product.product_type);
                    handleProductTypeChange(row, product.id, product.product_type);
                }).on('select2:clear', function () {
                    resetRowSelects(row);
                    resetTaxSlab(row);
                });

                // Variant select
                row.find('.variant-select').select2({
                    placeholder: 'Select variant...',
                    allowClear: true,
                    width: '100%'
                }).on('change', function () {
                    const variantId = $(this).val();
                    if (variantId) {
                        const selectedOption = $(this).find('option:selected');
                        const taxSlabId = selectedOption.data('tax-slab-id');
                        const taxPct = selectedOption.data('tax-percentage');
                        if (taxSlabId) {
                            setTaxSlab(row, taxSlabId);
                        }
                        loadVariantUnits(row, variantId);
                    } else {
                        row.find('.unit-select').html('<option value="">Select variant first...</option>').prop('disabled', true);
                    }
                });
                
                // Unit select
                row.find('.unit-select').select2({
                    placeholder: 'Select unit...',
                    allowClear: true,
                    width: '100%'
                });

                // Warehouse select
                row.find('.warehouse-select').select2({
                    placeholder: 'Select warehouse...',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("orders.warehouses") }}',
                        type: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                                _token: '{{ csrf_token() }}'
                            };
                        },
                        processResults: function (data) {
                            return { results: data.results };
                        }
                    }
                }).on('change', function () {
                    loadAvailableStock(row);
                });
            }

            function handleProductTypeChange(row, productId, productType) {
                const variantSelect = row.find('.variant-select');
                const unitSelect = row.find('.unit-select');
                
                // Reset all selects
                variantSelect.html('<option value="">N/A</option>').prop('disabled', true).trigger('change');
                unitSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                row.find('.item-price-input').val(0);
                row.find('.stock-display').val('-');
                resetTaxSlab(row);

                if (productType === 'simple') {
                    loadProductUnits(row, productId);
                } else if (productType === 'variable') {
                    loadProductVariants(row, productId);
                } else if (productType === 'bundle') {
                    loadBundlePrice(row, productId);
                    unitSelect.html('<option value="">N/A (Bundle)</option>').prop('disabled', true);
                    unitSelect.prop('required', false);
                }
            }

            function resetTaxSlab(row) {
                row.find('.tax-slab-select').val('').trigger('change');
                row.find('.item-tax-amount-hidden').val(0);
            }

            function setTaxSlab(row, taxSlabId) {
                if (taxSlabId) {
                    row.find('.tax-slab-select').val(taxSlabId).trigger('change');
                }
            }

            function resetRowSelects(row) {
                row.find('.product-type-hidden').val('');
                row.find('.variant-select').html('<option value="">N/A</option>').prop('disabled', true).trigger('change');
                row.find('.unit-select').html('<option value="">Select unit...</option>').prop('disabled', true).prop('required', true);
                row.find('.item-price-input').val(0);
                row.find('.stock-display').val('-');
                calculateItemTotal(row);
            }

            function loadProductUnits(row, productId) {
                const unitSelect = row.find('.unit-select');
                unitSelect.html('<option value="">Loading...</option>');

                $.get('{{ url("admin/orders/product-units") }}/' + productId, function (response) {
                    let options = '<option value="">Select unit...</option>';
                    if (response.units && response.units.length > 0) {
                        response.units.forEach(function (unit) {
                            const tiersData = JSON.stringify(unit.tiers || []);
                            options += `<option value="${unit.id}" data-pricing-type="${unit.pricing_type}" data-base-price="${unit.base_price}" data-tiers='${tiersData}'>${unit.name}</option>`;
                        });
                        // Auto-suggest tax slab from first unit (all units share same product tax slab)
                        if (response.units[0].tax_slab_id) {
                            setTaxSlab(row, response.units[0].tax_slab_id);
                        }
                    }
                    unitSelect.html(options).prop('disabled', false);
                }).fail(function () {
                    unitSelect.html('<option value="">No units available</option>');
                });
            }

            function loadProductVariants(row, productId) {
                const variantSelect = row.find('.variant-select');
                variantSelect.html('<option value="">Loading...</option>');

                $.get('{{ url("admin/orders/product-variants") }}/' + productId, function (response) {
                    let options = '<option value="">Select variant...</option>';
                    if (response.variants && response.variants.length > 0) {
                        response.variants.forEach(function (variant) {
                            options += `<option value="${variant.id}" data-tax-slab-id="${variant.tax_slab_id || ''}" data-tax-percentage="${variant.tax_percentage || 0}">${variant.name}</option>`;
                        });
                    }
                    variantSelect.html(options).prop('disabled', false);
                }).fail(function () {
                    variantSelect.html('<option value="">No variants available</option>');
                });
            }

            function loadVariantUnits(row, variantId) {
                const unitSelect = row.find('.unit-select');
                unitSelect.html('<option value="">Loading...</option>');

                $.get('{{ url("admin/orders/variant-units") }}/' + variantId, function (response) {
                    let options = '<option value="">Select unit...</option>';
                    if (response.units && response.units.length > 0) {
                        response.units.forEach(function (unit) {
                            const tiersData = JSON.stringify(unit.tiers || []);
                            options += `<option value="${unit.id}" data-pricing-type="${unit.pricing_type}" data-base-price="${unit.base_price}" data-tiers='${tiersData}'>${unit.name}</option>`;
                        });
                        // Auto-suggest tax slab from variant
                        if (response.units[0].tax_slab_id) {
                            setTaxSlab(row, response.units[0].tax_slab_id);
                        }
                    }
                    unitSelect.html(options).prop('disabled', false);
                }).fail(function () {
                    unitSelect.html('<option value="">No units available</option>');
                });
            }

            function loadBundlePrice(row, productId) {
                $.get('{{ url("admin/orders/bundle-price") }}/' + productId, function (response) {
                    row.find('.item-price-input').val(response.price || 0);
                    calculateItemTotal(row);
                });
            }

            function loadAvailableStock(row) {
                const warehouseId = row.find('.warehouse-select').val();
                const productId = row.find('.product-select').val();
                const variantId = row.find('.variant-select').val();
                const unitId = row.find('.unit-select').val();

                if (!warehouseId || !productId) {
                    row.find('.stock-display').val('-');
                    return;
                }

                $.post('{{ route("orders.available-stock") }}', {
                    warehouse_id: warehouseId,
                    product_id: productId,
                    variant_id: variantId || null,
                    unit_id: unitId || null,
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    row.find('.stock-display').val(response.available_stock);
                }).fail(function () {
                    row.find('.stock-display').val('Error');
                });
            }

            // Unit change - update price based on pricing type
            $(document).on('change', '.unit-select', function () {
                const row = $(this).closest('.item-row');
                updatePriceFromUnit(row);
                loadAvailableStock(row);
            });

            function updatePriceFromUnit(row) {
                const unitSelect = row.find('.unit-select');
                const selectedOption = unitSelect.find(':selected');
                const pricingType = selectedOption.data('pricing-type');
                const basePrice = parseFloat(selectedOption.data('base-price')) || 0;

                if (pricingType === 'fixed') {
                    row.find('.item-price-input').val(basePrice);
                } else if (pricingType === 'tiered') {
                    const tiers = selectedOption.data('tiers') || [];
                    const qty = parseInt(row.find('.item-quantity').val()) || 1;
                    const price = getTieredPrice(tiers, qty) || basePrice;
                    row.find('.item-price-input').val(price);
                }
                calculateItemTotal(row);
            }

            function getTieredPrice(tiers, qty) {
                if (!tiers || tiers.length === 0) return null;
                
                for (let tier of tiers) {
                    const minQty = parseInt(tier.min_qty);
                    const maxQty = tier.max_qty ? parseInt(tier.max_qty) : Infinity;
                    if (qty >= minQty && qty <= maxQty) {
                        return parseFloat(tier.price);
                    }
                }
                // If qty exceeds all tiers, use the last tier's price
                return parseFloat(tiers[tiers.length - 1].price);
            }

            // Calculate item total on change
            $(document).on('change keyup', '.item-quantity, .item-price-input, .item-discount-input, .tax-slab-select', function () {
                const row = $(this).closest('.item-row');
                
                // If quantity changed and pricing is tiered, recalculate price
                if ($(this).hasClass('item-quantity')) {
                    const unitSelect = row.find('.unit-select');
                    const pricingType = unitSelect.find(':selected').data('pricing-type');
                    if (pricingType === 'tiered') {
                        updatePriceFromUnit(row);
                        return; // updatePriceFromUnit already calls calculateItemTotal
                    }
                }
                calculateItemTotal(row);
            });

            function calculateItemTotal(row) {
                const qty = parseFloat(row.find('.item-quantity').val()) || 0;
                const price = parseFloat(row.find('.item-price-input').val()) || 0;
                const discount = parseFloat(row.find('.item-discount-input').val()) || 0;

                // Calculate tax amount from selected tax slab
                const taxSlabOption = row.find('.tax-slab-select option:selected');
                const taxPct = parseFloat(taxSlabOption.data('percentage')) || 0;
                const taxAmount = parseFloat(((price * qty * taxPct) / 100).toFixed(2));
                row.find('.item-tax-amount-hidden').val(taxAmount);

                const total = (qty * price) - discount;

                row.find('.item-total-display').val(window.formatCurrency(total));
                calculateOrderSummary();
            }

            // Remove item
            $(document).on('click', '.remove-item', function () {
                const container = $('#itemsContainer');
                if (container.find('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                    calculateOrderSummary();
                } else {
                    Swal.fire('Warning', 'Order must have at least one item', 'warning');
                }
            });

            // Shipping total change
            $('#shippingTotal').on('change keyup', function () {
                calculateOrderSummary();
            });

            function calculateOrderSummary() {
                let subtotal = 0;
                let discount = 0;
                let tax = 0;

                $('#itemsContainer .item-row').each(function () {
                    const qty = parseFloat($(this).find('.item-quantity').val()) || 0;
                    const price = parseFloat($(this).find('.item-price-input').val()) || 0;
                    const itemDiscount = parseFloat($(this).find('.item-discount-input').val()) || 0;
                    const itemTax = parseFloat($(this).find('.item-tax-amount-hidden').val()) || 0;

                    subtotal += qty * price;
                    discount += itemDiscount;
                    tax += itemTax;
                });

                const shipping = parseFloat($('#shippingTotal').val()) || 0;
                const grandTotal = (subtotal + tax + shipping) - discount;

                $('#summarySubtotal').text(window.formatCurrency(subtotal));
                $('#summaryTax').text(window.formatCurrency(tax));
                $('#summaryShipping').text(window.formatCurrency(shipping));
                $('#summaryDiscount').text('-' + window.formatCurrency(discount));
                $('#summaryGrandTotal').text(window.formatCurrency(grandTotal));
            }
        });
    </script>
@endpush

