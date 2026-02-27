@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@section('product-content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="fa fa-exchange-alt text-primary me-2"></i> Substitute Products</h5>
        <p class="text-muted small mb-0">Select alternative products to show customers when this item is out of stock.</p>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <label class="form-label fw-bold">Search Products to Add as Substitute</label>
            <div class="input-group">
                <select id="substituteSearch" class="form-control"></select>
                <button type="button" class="btn btn-primary mt-2" id="addSubstituteBtn">Add to List</button>
            </div>
        </div>

        <div id="substitute-list" class="row g-3">
            @forelse($product->substitutes as $sub)
                <div class="col-md-6 substitute-item" data-id="{{ $sub->id }}">
                    <div class="card border shadow-none h-100">
                        <div class="card-body d-flex align-items-center p-2">
                            <img src="{{ $sub->main_image_url }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small fw-bold">{{ $sub->name }}</h6>
                                <span class="badge bg-light text-dark border small">{{ $sub->brand->name }}</span>
                                <input type="hidden" name="substitutes[]" value="{{ $sub->id }}">
                            </div>
                            <button type="button" class="btn btn-link text-danger remove-sub">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 no-data-msg">
                    <div class="alert alert-light text-center border-dashed py-5">
                        <i class="fa fa-search fa-2x m-4 text-muted"></i>
                        <p class="mb-0 text-secondary m-4">No substitute products linked yet. Use the search bar above.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('product-js')
<script>
$(document).ready(function() {

    $('#substituteSearch').select2({
        placeholder: 'Enter SKU or Product Name...',
        minimumInputLength: 2,
        ajax: {
            url: "{{ route('search-substitutes') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { 
                    q: params.term,
                    exclude: '{{ $product->id }}'
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.name,
                        image: item.image_path,
                        sku: item.sku,
                        brand: item.brand_name
                    }))
                };
            },
        },
        templateResult: formatProduct,
        templateSelection: formatProductSelection
    });

    function formatProduct(product) {
        if (product.loading) return product.text;
        
        return $(`
            <div class="d-flex align-items-center">
                <img src="${product.image}" class="rounded me-2" style="width: 35px; height: 35px; object-fit: cover;">
                <div>
                    <div class="fw-bold small">${product.text}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        SKU: ${product.sku} | Brand: ${product.brand}
                    </div>
                </div>
            </div>
        `);
    }

    function formatProductSelection(product) {
        return product.name || product.text;
    }    

    $('#addSubstituteBtn').on('click', function() {
        let data = $('#substituteSearch').select2('data')[0];
        if (!data) return;

        if ($(`.substitute-item[data-id="${data.id}"]`).length > 0) {
            alert("This product is already in the list.");
            return;
        }

        $('.no-data-msg').remove();

        const html = `
            <div class="col-md-6 substitute-item animate__animated animate__fadeIn" data-id="${data.id}">
                <div class="card border border-primary bg-light shadow-none h-100">
                    <div class="card-body d-flex align-items-center p-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small fw-bold text-primary">${data.text}</h6>
                            <input type="hidden" name="substitutes[]" value="${data.id}">
                        </div>
                        <button type="button" class="btn btn-link text-danger remove-sub">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        
        $('#substitute-list').append(html);
        $('#substituteSearch').val(null).trigger('change');
    });

    $(document).on('click', '.remove-sub', function() {
        $(this).closest('.substitute-item').remove();
        if ($('.substitute-item').length === 0) {
            location.reload();
        }
    });
});
</script>
@endpush