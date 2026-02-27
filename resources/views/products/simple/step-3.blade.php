@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@push('product-css')

@endpush

@section('product-content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Unit Pricing & Tier Management</h5>
    </div>
    <div class="card-body">
        <ul class="nav nav-pills mb-4 bg-light p-2 rounded" id="pricingTab" role="tablist">
            @foreach($allUnits as $index => $pUnit)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                            id="tab-btn-{{ $pUnit->id }}" 
                            data-bs-toggle="pill" 
                            data-bs-target="#pane-{{ $pUnit->id }}" 
                            type="button" role="tab">
                        {{ $pUnit->unit->name }} 
                        {!! $pUnit->is_base ? '<span class="badge bg-dark ms-1">Base</span>' : '' !!}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="pricingTabContent">
            @foreach($allUnits as $index => $pUnit)
                @php $price = $pUnit->price; @endphp
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }} unit-pane" 
                        id="pane-{{ $pUnit->id }}" 
                        role="tabpanel" 
                        data-unit-name="{{ $pUnit->unit->name }}">
                    
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Pricing Method</label>
                            <input type="hidden" name="pricing[{{ $pUnit->id }}][unit_id]" value="{{ $pUnit->unit_id }}">
                            <select name="pricing[{{ $pUnit->id }}][pricing_type]" class="form-select pricing-type-selector">
                                <option value="fixed" {{ optional($price)->pricing_type == 'fixed' ? 'selected' : '' }}>Fixed Price</option>
                                <option value="tiered" {{ optional($price)->pricing_type == 'tiered' ? 'selected' : '' }}>Tiered Pricing</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Base Price ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="pricing[{{ $pUnit->id }}][base_price]" 
                                        class="form-control base-price-input" 
                                        step="0.01" value="{{ optional($price)->base_price ?? '0.00' }}">
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm copy-btn" data-unit-id="{{ $pUnit->id }}">
                                Copy Price to Other Units
                            </button>
                        </div>
                    </div>

                    <div class="tier-section {{ optional($price)->pricing_type == 'tiered' ? '' : 'd-none' }}">
                        <table class="table table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th>Min Qty</th>
                                    <th>Max Qty</th>
                                    <th>Price ($)</th>
                                    <th>Discount (%)</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody class="tier-body">
                                @if($price && $price->pricing_type == 'tiered')
                                    @foreach($price->tiers as $tIndex => $tier)
                                        <tr class="tier-row">
                                            <td><input type="number" name="pricing[{{ $pUnit->id }}][tiers][{{ $tIndex }}][min_qty]" class="form-control" value="{{ $tier->min_qty }}"></td>
                                            <td><input type="number" name="pricing[{{ $pUnit->id }}][tiers][{{ $tIndex }}][max_qty]" class="form-control" value="{{ $tier->max_qty }}" placeholder="∞"></td>
                                            <td><input type="number" name="pricing[{{ $pUnit->id }}][tiers][{{ $tIndex }}][price]" class="form-control tier-price-input" step="0.01" value="{{ $tier->price }}"></td>
                                            <td class="discount-label pt-3 fw-bold text-success">0%</td>
                                            <td><button type="button" class="btn btn-link text-danger remove-tier p-0"><i class="fa fa-trash"></i></button></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-dark btn-sm add-tier-btn" data-unit-id="{{ $pUnit->id }}">
                            + Add New Pricing Tier
                        </button>
                    </div>

                    <div class="mt-4 p-3 bg-light border rounded row text-center">
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block">Base Price</small>
                            <span class="fw-bold summary-base">$0.00</span>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block">Best Price</small>
                            <span class="fw-bold summary-best text-success">$0.00</span>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block">Active Tiers</small>
                            <span class="fw-bold summary-tiers">0</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Max Discount</small>
                            <span class="fw-bold summary-discount">0%</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>        
    </div>
</div>
@endsection

@push('product-js')
<script>
$(document).ready(function() {
    $('.unit-pane').each(function() { calculatePaneSummary($(this)); });

    $(document).on('change', '.pricing-type-selector', function() {
        const pane = $(this).closest('.unit-pane');
        if ($(this).val() === 'tiered') {
            pane.find('.tier-section').removeClass('d-none');
        } else {
            pane.find('.tier-section').addClass('d-none');
        }
    });

    $(document).on('click', '.copy-btn', function() {
        const sourcePane = $(this).closest('.unit-pane');
        const sourceMode = sourcePane.find('.pricing-type-selector').val();
        const sourceBasePrice = sourcePane.find('.base-price-input').val();
        
        $('.unit-pane').not(sourcePane).each(function() {
            const targetPane = $(this);
            const targetUnitId = targetPane.data('unit-id');
            
            targetPane.find('.pricing-type-selector').val(sourceMode).trigger('change');
            targetPane.find('.base-price-input').val(sourceBasePrice);
            
            if (sourceMode === 'tiered') {
                const targetBody = targetPane.find('.tier-body');
                targetBody.empty();
                
                sourcePane.find('.tier-row').each(function(idx) {
                    const min = $(this).find('input[name*="[min_qty]"]').val();
                    const max = $(this).find('input[name*="[max_qty]"]').val();
                    const price = $(this).find('input[name*="[price]"]').val();
                    
                    const newRow = `
                        <tr class="tier-row">
                            <td><input type="number" name="pricing[${targetUnitId}][tiers][${idx}][min_qty]" class="form-control" value="${min}"></td>
                            <td><input type="number" name="pricing[${targetUnitId}][tiers][${idx}][max_qty]" class="form-control" value="${max}"></td>
                            <td><input type="number" name="pricing[${targetUnitId}][tiers][${idx}][price]" class="form-control tier-price-input" step="0.01" value="${price}"></td>
                            <td class="discount-label pt-3 fw-bold text-success">0%</td>
                            <td><button type="button" class="btn btn-link text-danger remove-tier p-0"><i class="fa fa-trash"></i></button></td>
                        </tr>`;
                    targetBody.append(newRow);
                });
            }
            calculatePaneSummary(targetPane);
        });
        
        alert("Pricing structure copied to all units. Don't forget to save!");
    });

    $(document).on('click', '.add-tier-btn', function() {
        const unitId = $(this).data('unit-id');
        const body = $(this).closest('.tier-section').find('.tier-body');
        const idx = body.find('tr').length;

        const row = `
            <tr class="tier-row">
                <td><input type="number" name="pricing[${unitId}][tiers][${idx}][min_qty]" class="form-control" required></td>
                <td><input type="number" name="pricing[${unitId}][tiers][${idx}][max_qty]" class="form-control" placeholder="∞"></td>
                <td><input type="number" name="pricing[${unitId}][tiers][${idx}][price]" class="form-control tier-price-input" step="0.01" required></td>
                <td class="discount-label pt-3 fw-bold text-success">0%</td>
                <td><button type="button" class="btn btn-link text-danger remove-tier p-0"><i class="fa fa-trash"></i></button></td>
            </tr>`;
        body.append(row);
    });

    function calculatePaneSummary(pane) {
        const basePrice = parseFloat(pane.find('.base-price-input').val()) || 0;
        let minPrice = basePrice;
        let tierCount = 0;

        pane.find('.tier-row').each(function() {
            const tPrice = parseFloat($(this).find('.tier-price-input').val()) || 0;
            if (tPrice > 0) {
                if (tPrice < minPrice) minPrice = tPrice;
                tierCount++;
                
                const disc = basePrice > 0 ? ((basePrice - tPrice) / basePrice * 100).toFixed(1) : 0;
                $(this).find('.discount-label').text(disc + '%');
            }
        });

        const maxDisc = basePrice > 0 ? ((basePrice - minPrice) / basePrice * 100).toFixed(1) : 0;

        pane.find('.summary-base').text('$' + basePrice.toFixed(2));
        pane.find('.summary-best').text('$' + minPrice.toFixed(2));
        pane.find('.summary-tiers').text(tierCount);
        pane.find('.summary-discount').text(maxDisc + '%');
    }

    $(document).on('input', '.base-price-input, .tier-price-input', function() {
        calculatePaneSummary($(this).closest('.unit-pane'));
    });

    $(document).on('click', '.remove-tier', function() {
        const pane = $(this).closest('.unit-pane');
        $(this).closest('tr').remove();
        calculatePaneSummary(pane);
    });
});
</script>
@endpush