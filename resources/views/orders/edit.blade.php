@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@push('css')
    <style>
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
        .order-header-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .order-header-edit h4 {
            margin: 0;
        }
        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 100px 120px 120px 100px 50px;
            gap: 0.75rem;
            align-items: center;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .item-row input, .item-row select {
            font-size: 0.875rem;
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
    </style>
@endpush

@section('content')
    <!-- Order Header -->
    <div class="order-header-edit d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4>Editing Order #{{ $order->order_number }}</h4>
            <div class="mt-2">
                {!! $order->status_badge !!}
                {!! $order->payment_status_badge !!}
            </div>
        </div>
        <div>
            <a href="{{ route('orders.show', $order) }}" class="btn btn-light btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Back to Details
            </a>
        </div>
    </div>

    <form action="{{ route('orders.update', $order) }}" method="POST" id="orderForm">
        @csrf
        @method('PUT')

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
                            <input type="checkbox" class="form-check-input" id="sameAsShipping">
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
                <span class="text-muted small">Items are managed from the order detail page</span>
            </div>
            <div class="section-body">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                    @if($item->variant)
                                        <br><small class="text-muted">{{ $item->variant->name ?? '' }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $item->sku }}</code></td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ currency_format($item->unit_price) }}</td>
                                <td class="text-end text-danger">-{{ currency_format($item->discount_amount) }}</td>
                                <td class="text-end"><strong>{{ currency_format($item->total) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end">{{ currency_format($order->sub_total) }}</td>
                        </tr>
                    </tfoot>
                </table>
                <div class="alert alert-info mb-0">
                    <i class="fa fa-info-circle me-1"></i>
                    To add, edit, or remove items, use the <a href="{{ route('orders.show', $order) }}">order detail page</a>.
                </div>
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
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}" {{ $order->payment_method == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Cost</label>
                            <input type="number" name="shipping_total" class="form-control" 
                                   value="{{ old('shipping_total', $order->shipping_total) }}" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $order->notes) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="internal_notes" class="form-control" rows="2">{{ old('internal_notes', $order->internal_notes) }}</textarea>
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
                                <span>{{ currency_format($order->sub_total) }}</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax</span>
                                <span>{{ currency_format($order->tax_total) }}</span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span id="summaryShipping">{{ currency_format($order->shipping_total) }}</span>
                            </div>
                            <div class="summary-row">
                                <span>Discount</span>
                                <span>-{{ currency_format($order->discount_total) }}</span>
                            </div>
                            <div class="summary-row total">
                                <span>Grand Total</span>
                                <span id="summaryGrandTotal">{{ currency_format($order->grand_total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">
                        <i class="fa fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
             const carribianCountries = @json(\App\Helpers\Helper::$carribianCountries);

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

                // Reset dependent fields if manually changed by user (not on initial load)
                // We need to be careful not to wipe out pre-loaded values on page load
                // But this function is triggered by 'change' event which usually means user interaction.
                // However, triggers can be manual. To be safe, we can check if it's user triggered?
                // Actually, if country changes, state/city should always reset.
                
                // NOTE: For edit page, we might trigger this on load or manually. 
                // We should only reset if the new country is different from what's currently there (which 'change' implies).
                // BUT, triggers can happen programmatically. Let's reset unless we add a flag.
                // Or simplified: Just update visibility labels here, let Select2 binding handle clearing if needed or user does it.
                // But standard behavior is clear downstream.
                
                 if (stateSelect.data('preloaded')) {
                     // If purely for initial setup, maybe skip reset? 
                     // But simpler: separate initial setup from change handler.
                     stateSelect.data('preloaded', false);
                 } else {
                     stateSelect.val(null).trigger('change');
                     citySelect.val(null).trigger('change');
                 }

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
            $('select[name$="_country_id"]').on('change', function (e) {
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

            // Handle existing values on page load
             $('select[name$="_country_id"]').each(function() {
                const countryId = $(this).val();
                if(countryId) {
                     // We manually check logic but avoid resetting values
                    const prefix = $(this).attr('name').replace('_country_id', '');
                    const stateSelect = $(`select[name="${prefix}_state_id"]`);
                    const citySelect = $(`select[name="${prefix}_city_id"]`);
                    const cityContainer = citySelect.closest('.col-md-6');
                    const stateLabelEl = stateSelect.siblings('label');
                    const isCaribbean = carribianCountries.includes(parseInt(countryId));

                    // Mark as preloaded so change handler doesn't wipe
                     stateSelect.data('preloaded', true);

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
            });

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
            
             // Initial Check
            if ($('#sameAsShipping').prop('checked')) {
                 $('#billingAddressSection').find('input, select').prop('readonly', true);
                 $('#billingAddressSection').find('select').prop('disabled', true);
            }

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
                         // Check if option exists in billing (Select2 AJAX needs option created if not there)
                         // Since billing connects to same AJAX source, we can just append option
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

            // Update shipping in summary
            $('input[name="shipping_total"]').on('change keyup', function () {
                const shipping = parseFloat($(this).val()) || 0;
                $('#summaryShipping').text(window.formatCurrency(shipping));

                // Recalculate grand total
                const subtotal = {{ $order->sub_total }};
                const tax = {{ $order->tax_total }};
                const discount = {{ $order->discount_total }};
                const grandTotal = (subtotal + tax + shipping) - discount;
                $('#summaryGrandTotal').text(window.formatCurrency(grandTotal));
            });
        });
    </script>
@endpush
