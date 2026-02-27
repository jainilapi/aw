@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@section('product-content')
    <div class="accordion" id="pricingVariantAccordion">
        @foreach ($product->variants as $vIndex => $variant)
            <div class="accordion-item mb-3 border shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $vIndex == 0 ? '' : 'collapsed' }} bg-white fw-bold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#pricing-v-{{ $variant->id }}">
                        <img src="{{ $variant->images->where('position', 0)->first() ? asset('storage/' . $variant->images->where('position', 0)->first()->image_path) : asset('assets/img/placeholder.png') }}"
                            class="rounded me-3" style="width: 35px; height: 35px; object-fit: contain;"
                            onerror="this.onerror=null; this.src='{{ asset('no-image-found.jpg') }}';">
                        {{ $variant->name }} <span
                            class="badge bg-light text-dark border ms-2 small">{{ $variant->sku }}</span>
                    </button>
                </h2>
                <div id="pricing-v-{{ $variant->id }}" class="accordion-collapse collapse {{ $vIndex == 0 ? 'show' : '' }}"
                    data-bs-parent="#pricingVariantAccordion">
                    <div class="accordion-body">

                        <ul class="nav nav-pills mb-3 bg-light p-2 rounded" id="v-tab-{{ $variant->id }}" role="tablist">
                            @foreach ($variant->units as $uIndex => $pUnit)
                                <li class="nav-item">
                                    <button class="nav-link {{ $uIndex === 0 ? 'active' : '' }}" data-bs-toggle="pill"
                                        data-bs-target="#pane-v-{{ $variant->id }}-u-{{ $pUnit->unit_id }}"
                                        type="button">
                                        {{ $pUnit->unit->name }}
                                        @if ($pUnit->is_base)
                                            <span class="badge bg-dark ms-1">Base</span>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mt-4">
                            @foreach ($variant->units as $uIndex => $pUnit)
                                @php
                                    $price = \App\Models\AwPrice::with('tiers')
                                        ->where('product_id', $product->id)
                                        ->where('variant_id', $variant->id)
                                        ->where('unit_id', $pUnit->id)
                                        ->first();
                                    $basePrice = optional($price)->base_price ?? 0;
                                    $bestPrice = $basePrice;
                                    $tierCount = 0;
                                    $maxDisc = 0;

                                    if ($price && $price->pricing_type == 'tiered' && $price->tiers->count() > 0) {
                                        $bestPrice = $price->tiers->min('price');
                                        $tierCount = $price->tiers->count();
                                        $maxDisc = $basePrice > 0 ? (($basePrice - $bestPrice) / $basePrice) * 100 : 0;
                                    }
                                @endphp
                                <div class="tab-pane fade {{ $uIndex === 0 ? 'show active' : '' }} unit-pane"
                                    id="pane-v-{{ $variant->id }}-u-{{ $pUnit->unit_id }}"
                                    data-variant-id="{{ $variant->id }}" data-unit-id="{{ $pUnit->unit_id }}">

                                    <div class="row g-3 align-items-end mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Pricing Method</label>
                                            <input type="hidden" name="variant_pricing[{{ $variant->id }}][{{ $pUnit->id }}][unit_id]" value="{{ $pUnit->unit_id }}">
                                            <select
                                                name="variant_pricing[{{ $variant->id }}][{{ $pUnit->id }}][pricing_type]"
                                                class="form-select pricing-type-selector">
                                                <option value="fixed"
                                                    {{ optional($price)->pricing_type == 'fixed' ? 'selected' : '' }}>Fixed
                                                    Price</option>
                                                <option value="tiered"
                                                    {{ optional($price)->pricing_type == 'tiered' ? 'selected' : '' }}>
                                                    Tiered Pricing</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Base Price ($)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number"
                                                    name="variant_pricing[{{ $variant->id }}][{{ $pUnit->id }}][base_price]"
                                                    class="form-control base-price-input" step="0.01"
                                                    value="{{ optional($price)->base_price ?? '0.00' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm copy-variant-btn">Copy to other
                                                Units</button>
                                        </div>
                                    </div>

                                    <div
                                        class="tier-section {{ optional($price)->pricing_type == 'tiered' ? '' : 'd-none' }}">
                                        <table class="table table-sm border">
                                            <thead class="table-light">
                                                <tr class="small">
                                                    <th>Min Qty</th>
                                                    <th>Max Qty</th>
                                                    <th>Price ($)</th>
                                                    <th>Discount</th>
                                                    <th width="40"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="tier-body">
                                                @if ($price && $price->pricing_type == 'tiered')
                                                    @foreach ($price->tiers as $tIndex => $tier)
                                                        <tr class="tier-row">
                                                            <td><input type="number"
                                                                    name="variant_pricing[{{ $variant->id }}][{{ $pUnit->id }}][tiers][{{ $tIndex }}][min_qty]"
                                                                    class="form-control form-control-sm"
                                                                    value="{{ $tier->min_qty }}"></td>
                                                            <td><input type="number"
                                                                    name="variant_pricing[{{ $variant->id }}][{{ $pUnit->id }}][tiers][{{ $tIndex }}][max_qty]"
                                                                    class="form-control form-control-sm"
                                                                    value="{{ $tier->max_qty }}" placeholder="∞"></td>
                                                            <td><input type="number"
                                                                    name="variant_pricing[{{ $variant->id }}][{{ $pUnit->id }}][tiers][{{ $tIndex }}][price]"
                                                                    class="form-control form-control-sm tier-price-input"
                                                                    step="0.01" value="{{ $tier->price }}"></td>
                                                            <td class="discount-label pt-2 fw-bold text-success small">0%
                                                            </td>
                                                            <td><button type="button"
                                                                    class="btn btn-link text-danger remove-tier p-0"><i
                                                                        class="fa fa-trash"></i></button></td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-dark btn-sm add-tier-btn"
                                            data-variant-id="{{ $variant->id }}" data-unit-id="{{ $pUnit->id }}">
                                            + Add New Pricing Tier
                                        </button>
                                    </div>

                                    <div class="mt-4 p-3 bg-light border rounded row text-center mx-0 g-0 shadow-sm border-start border-primary border-4">
                                        <div class="col-md-3 border-end">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Base Price</small>
                                            <span class="fw-bold summary-base" style="font-size: 1.1rem;">${{ number_format($basePrice, 2) }}</span>
                                        </div>
                                        <div class="col-md-3 border-end">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Best Price</small>
                                            <span class="fw-bold summary-best text-success" style="font-size: 1.1rem;">${{ number_format($bestPrice, 2) }}</span>
                                        </div>
                                        <div class="col-md-3 border-end">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Active Tiers</small>
                                            <span class="fw-bold summary-tiers" style="font-size: 1.1rem;">{{ $tierCount }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Max Discount</small>
                                            <span class="fw-bold summary-discount text-primary" style="font-size: 1.1rem;">{{ number_format($maxDisc, 1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('product-js')
    <script>
        $(document).ready(function() {
            $('.unit-pane').each(function() {
                calculatePaneSummary($(this));
            });

            $(document).on('change', '.pricing-type-selector', function() {
                const pane = $(this).closest('.unit-pane');
                const isTiered = $(this).val() === 'tiered';
                pane.find('.tier-section').toggleClass('d-none', !isTiered);
                calculatePaneSummary(pane);
            });

            $(document).on('click', '.add-tier-btn', function() {
                const vId = $(this).data('variant-id');
                const uId = $(this).data('unit-id');
                const body = $(this).closest('.tier-section').find('.tier-body');
                const idx = body.find('tr').length;

                const row = `
            <tr class="tier-row">
                <td><input type="number" name="variant_pricing[${vId}][${uId}][tiers][${idx}][min_qty]" class="form-control form-control-sm" required></td>
                <td><input type="number" name="variant_pricing[${vId}][${uId}][tiers][${idx}][max_qty]" class="form-control form-control-sm" placeholder="∞"></td>
                <td><input type="number" name="variant_pricing[${vId}][${uId}][tiers][${idx}][price]" class="form-control form-control-sm tier-price-input" step="0.01" required></td>
                <td class="discount-label pt-2 fw-bold text-success small">0%</td>
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
                            
                            const disc = basePrice > 0 ? (((basePrice - tPrice) / basePrice) * 100).toFixed(1) : 0;
                            $(this).find('.discount-label').text(disc + '%');
                        }
                    });

                    const maxDisc = basePrice > 0 ? (((basePrice - minPrice) / basePrice) * 100).toFixed(1) : 0;

                    pane.find('.summary-base').text('$' + basePrice.toFixed(2));
                    pane.find('.summary-best').text('$' + minPrice.toFixed(2));
                    pane.find('.summary-tiers').text(tierCount);
                    pane.find('.summary-discount').text(maxDisc + '%');
                    
                    if (minPrice < basePrice) {
                        pane.find('.summary-best').addClass('text-success').removeClass('text-dark');
                    } else {
                        pane.find('.summary-best').removeClass('text-success').addClass('text-dark');
                    }
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
