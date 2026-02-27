@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@section('product-content')
<div class="accordion" id="variantUnitsAccordion">
    @foreach($product->variants as $vIndex => $variant)
    <div class="accordion-item mb-3 border shadow-sm">
        <h2 class="accordion-header">
            <button class="accordion-button {{ $vIndex == 0 ? '' : 'collapsed' }} bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $variant->id }}">
                <div class="d-flex align-items-center">
                    <img src="{{ $variant->images->where('position', 0)->first() ? asset('storage/'.$variant->images->where('position', 0)->first()->image_path) : asset('assets/img/placeholder.png') }}" 
                         class="rounded me-2" style="width: 35px; height: 35px; object-fit: contain;"
                         onerror="this.onerror=null; this.src='{{ asset('no-image-found.jpg') }}';">
                    <strong>{{ $variant->name }}</strong> 
                    <span class="badge bg-secondary ms-2 small">{{ $variant->sku }}</span>
                </div>
            </button>
        </h2>
        <div id="collapse{{ $variant->id }}" class="accordion-collapse collapse {{ $vIndex == 0 ? 'show' : '' }}" data-bs-parent="#variantUnitsAccordion">
            <div class="accordion-body">
                @php
                    $vBaseUnit = $variant->units->where('is_base', 1)->first();
                    $vAddUnits = $variant->units->where('is_base', 0)->sortBy('conversion_factor');
                @endphp

                <div class="base-unit-container border p-3 mb-3 bg-light rounded">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <label class="fw-bold">Base Unit (Smallest)</label>
                            <select name="variant_units[{{ $variant->id }}][base_unit_id]" class="form-control select2-unit base-unit-select" required>
                                <option value="">Select Base Unit</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" data-name="{{ $u->name }}" {{ ($vBaseUnit && $vBaseUnit->unit_id == $u->id) ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="form-check form-switch pt-4">
                                <input class="form-check-input" type="radio" name="variant_units[{{ $variant->id }}][default_selling_unit]" value="base" {{ ($vBaseUnit && $vBaseUnit->is_default_selling) ? 'checked' : '' }}>
                                <label class="form-check-label">Default Selling Unit</label>
                            </div>
                        </div>
                        <div class="col-md-3 text-end pt-3">
                            <button type="button" class="btn btn-primary btn-sm add-unit-btn" data-variant-id="{{ $variant->id }}">
                                <i class="fa fa-plus"></i> Add Pack Size
                            </button>
                        </div>
                    </div>
                </div>

                <div class="additional-units-wrapper" id="wrapper-{{ $variant->id }}">
                    @foreach($vAddUnits as $idx => $ap_unit)
                        <div class="unit-row border p-3 mb-2 rounded shadow-sm bg-white position-relative">
                            <button type="button" class="btn btn-outline-danger btn-sm position-absolute end-0 top-0 m-2 remove-row"><i class="fa fa-trash"></i></button>
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label class="small fw-bold">Unit Name</label>
                                    <select name="variant_units[{{ $variant->id }}][units][{{ $idx }}][unit_id]" class="form-control select2-unit unit-select" required>
                                        @foreach($units as $u)
                                            <option value="{{ $u->id }}" data-name="{{ $u->name }}" {{ $ap_unit->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @php
                                    $parent = ($idx === 0) ? $vBaseUnit : $vAddUnits->values()[$idx-1];
                                    $qty = $ap_unit->conversion_factor / $parent->conversion_factor;
                                @endphp
                                <div class="col-md-2">
                                    <label class="small fw-bold">Quantity</label>
                                    <input type="number" name="variant_units[{{ $variant->id }}][units][{{ $idx }}][quantity]" class="form-control unit-qty" step="any" value="{{ round($qty, 4) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">Per Parent</label>
                                    <input type="text" class="form-control bg-light parent-unit-display" readonly>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="form-check form-switch pt-4">
                                        <input class="form-check-input" type="radio" name="variant_units[{{ $variant->id }}][default_selling_unit]" value="{{ $idx }}" {{ $ap_unit->is_default_selling ? 'checked' : '' }}>
                                        <label class="form-check-label">Default Selling</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 p-2 bg-light rounded hierarchy-summary text-primary border-start border-primary border-3 small"></div>
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
    const unitsData = @json($units);

    function updateChain(container) {
        let baseSelect = container.find('.base-unit-select');
        let baseUnitName = baseSelect.find('option:selected').data('name') || '[Base]';
        let runningFactor = 1;

        container.find('.unit-row').each(function(index) {
            let row = $(this);
            let currentUnitName = row.find('.unit-select option:selected').data('name') || `[Unit ${index + 1}]`;
            let qty = parseFloat(row.find('.unit-qty').val()) || 1;
            
            let prevUnitName = (index === 0) 
                ? baseUnitName 
                : row.prev('.unit-row').find('.unit-select option:selected').data('name') || `[Unit ${index}]`;
            
            runningFactor *= qty;
            row.find('.parent-unit-display').val(prevUnitName);
            row.find('.hierarchy-summary').html(`<strong>1 ${currentUnitName}</strong> = ${qty} ${prevUnitName} = ${runningFactor} ${baseUnitName}`);
        });
    }

    $('.accordion-body').each(function() { updateChain($(this)); });
    $('.select2-unit').select2({ width: '100%' });

    $(document).on('change', '.base-unit-select, .unit-select, .unit-qty', function() {
        updateChain($(this).closest('.accordion-body'));
    });

    $('.add-unit-btn').on('click', function() {
        let vId = $(this).data('variant-id');
        let container = $(this).closest('.accordion-body');
        let wrapper = $('#wrapper-' + vId);
        let rowCount = wrapper.find('.unit-row').length;

        if (!container.find('.base-unit-select').val()) {
            alert("Select Base Unit for this variant first."); return;
        }

        const html = `
            <div class="unit-row border p-3 mb-2 rounded shadow-sm bg-white position-relative animate__animated animate__fadeIn">
                <button type="button" class="btn btn-outline-danger btn-sm position-absolute end-0 top-0 m-2 remove-row"><i class="fa fa-trash"></i></button>
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="small fw-bold">Unit Name</label>
                        <select name="variant_units[${vId}][units][${rowCount}][unit_id]" class="form-control select2-unit unit-select" required>
                            <option value="">Select Unit</option>
                            ${unitsData.map(u => `<option value="${u.id}" data-name="${u.name}">${u.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Quantity</label>
                        <input type="number" name="variant_units[${vId}][units][${rowCount}][quantity]" class="form-control unit-qty" min="1" step="any" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Per Parent</label>
                        <input type="text" class="form-control bg-light parent-unit-display" readonly>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="form-check form-switch pt-4">
                            <input class="form-check-input" type="radio" name="variant_units[${vId}][default_selling_unit]" value="${rowCount}">
                            <label class="form-check-label">Default Selling</label>
                        </div>
                    </div>
                </div>
                <div class="mt-2 p-2 bg-light rounded hierarchy-summary text-primary border-start border-primary border-3 small"></div>
            </div>`;
        
        wrapper.append(html);
        wrapper.find('.select2-unit').last().select2({ width: '100%' });
        updateChain(container);
    });

    $(document).on('click', '.remove-row', function() {
        let container = $(this).closest('.accordion-body');
        $(this).closest('.unit-row').remove();
        updateChain(container);
    });
});
</script>
@endpush