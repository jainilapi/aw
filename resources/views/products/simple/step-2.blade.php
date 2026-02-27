@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@section('product-content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h4>Unit Hierarchy / Pack Sizes</h4>
        <button type="button" class="btn btn-primary btn-sm" id="add-unit-btn">+ Add Unit</button>
    </div>
    <div class="card-body">
        <div class="base-unit-container border p-3 mb-3 bg-light rounded">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="fw-bold">Base Unit <span class="text-danger">*</span></label>
                    <select name="base_unit_id" id="base_unit_id" class="form-control select2-unit" required>
                        <option value="">Select Base Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @if(isset($baseProductUnit->id) && $unit->id == $baseProductUnit->unit_id) selected @endif data-name="{{ $unit->name }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Smallest unit of measure</small>
                </div>
                <div class="col-md-4 text-center">
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="radio" name="default_selling_unit" value="base" @if(isset($baseProductUnit->id) && $baseProductUnit->is_default_selling) checked @endif>
                        <label class="form-check-label">Default Selling Unit</label>
                    </div>
                </div>
            </div>
        </div>

        <div id="additional-units-wrapper">
            @if(isset($additionalUnits) && $additionalUnits->count() > 0)
                @foreach($additionalUnits as $index => $ap_unit)
                    <div class="unit-row border p-3 mb-2 rounded shadow-sm bg-white position-relative" data-index="{{ $index }}">
                        <button type="button" class="btn btn-outline-danger btn-sm position-absolute end-0 top-0 m-2 remove-row">
                            <i class="fa fa-trash"></i>
                        </button>
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="small fw-bold">Unit Name</label>
                                <select name="units[{{ $index }}][unit_id]" class="form-control select2-unit unit-select" required>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" data-name="{{ $u->name }}" 
                                            {{ $ap_unit->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                $parentUnit = ($index === 0) ? $baseProductUnit : $additionalUnits[$index-1];
                                $relativeQty = $ap_unit->conversion_factor / $parentUnit->conversion_factor;
                            @endphp
                            <div class="col-md-2">
                                <label class="small fw-bold">Quantity</label>
                                <input type="number" name="units[{{ $index }}][quantity]" class="form-control unit-qty" 
                                    min="0.0001" step="any" value="{{ round($relativeQty, 4) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold">Per Parent Unit</label>
                                <input type="text" class="form-control bg-light parent-unit-display" readonly>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="form-check form-switch pt-4">
                                    <input class="form-check-input" type="radio" name="default_selling_unit" value="{{ $index }}"
                                        {{ $ap_unit->is_default_selling ? 'checked' : '' }}>
                                    <label class="form-check-label">Default Selling</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 p-2 bg-light rounded hierarchy-summary text-primary border-start border-primary border-3"></div>
                    </div>
                @endforeach
            @endif
        </div>
        
        <div id="unit-validation-error" class="alert alert-danger d-none mt-2"></div>
    </div>
</div>
@endsection

@push('product-css')

@endpush

@push('product-js')
<script>
$(document).ready(function() {
    let unitsData = @json($units);

    function initStep2() {
        if ($('.unit-row').length > 0) {
            $('.select2-unit').select2();
            updateFullChain();
        }
    }

    initStep2();
    $('.select2-unit').select2();

    function updateFullChain() {
        let baseUnitName = $('#base_unit_id option:selected').data('name') || '[Base]';
        let runningFactor = 1;
        let chainPath = baseUnitName;

        $('.unit-row').each(function(index) {
            let row = $(this);
            let currentUnitName = row.find('.unit-select option:selected').data('name') || `[Unit ${index + 1}]`;
            let qty = parseFloat(row.find('.unit-qty').val()) || 1;
            
            let prevUnitName = (index === 0) 
                ? baseUnitName 
                : row.prev('.unit-row').find('.unit-select option:selected').data('name') || `[Unit ${index}]`;
            
            runningFactor *= qty;

            row.find('.parent-unit-display').val(prevUnitName);
            
            let formulaHtml = `<strong>1 ${currentUnitName}</strong> = ${qty} ${prevUnitName}`;
            
            if (index > 0) {
                formulaHtml += ` = ${runningFactor} ${baseUnitName}`;
            }

            row.find('.hierarchy-summary').html(formulaHtml);
        });
    }

    $(document).on('change', '#base_unit_id', function() {
        updateFullChain();
    });

    $(document).on('change keyup', '.unit-qty, .unit-select', function() {
        updateFullChain();
    });

    $('#add-unit-btn').on('click', function() {
        const rowCount = $('.unit-row').length;
        if (!$('#base_unit_id').val()) {
            alert("Please select a Base Unit first.");
            return;
        }

        const html = `
            <div class="unit-row border p-3 mb-2 rounded shadow-sm bg-white position-relative">
                <button type="button" class="btn btn-outline-danger btn-sm position-absolute end-0 top-0 m-2 remove-row">
                    <i class="fa fa-trash"></i>
                </button>
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="small fw-bold">Unit Name</label>
                        <select name="units[${rowCount}][unit_id]" class="form-control select2-unit unit-select" required>
                            <option value="">Select Unit</option>
                            ${unitsData.map(u => `<option value="${u.id}" data-name="${u.name}">${u.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Quantity</label>
                        <input type="number" name="units[${rowCount}][quantity]" class="form-control unit-qty" min="1" step="any" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Per Parent Unit</label>
                        <input type="text" class="form-control bg-light parent-unit-display" readonly>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="form-check form-switch pt-4">
                            <input class="form-check-input" type="radio" name="default_selling_unit" value="${rowCount}">
                            <label class="form-check-label">Default Selling</label>
                        </div>
                    </div>
                </div>
                <div class="mt-2 p-2 bg-light rounded hierarchy-summary text-primary border-start border-primary border-3">
                    </div>
            </div>
        `;
        
        $('#additional-units-wrapper').append(html);
        $('.select2-unit').select2();
        updateFullChain();
    });

    $(document).on('click', '.remove-row', function() {
        $(this).closest('.unit-row').remove();
        updateFullChain();
    });
});
</script>
@endpush