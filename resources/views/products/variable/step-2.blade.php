@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@section('product-css')
<style>
    .bg-soft-primary { background: #eef2ff; }
    .bg-soft-dark { background: #f8f9fa; }
    .animate__animated { animation-duration: 0.4s; }
</style>
@endsection

@section('product-content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Step 2: Variant Attributes & Generation</h5>
        <div class="status-indicator">
            <span class="badge bg-soft-primary text-primary" id="variant-count-badge">0 Variants Generated</span>
        </div>
    </div>
    <div class="card-body">
        
        <div id="attribute-section" class="p-4 bg-light rounded border mb-4">
            <h6 class="fw-bold mb-3 text-uppercase small" style="letter-spacing: 1px;">Define Attributes</h6>
                <div id="attribute-wrapper">
                    @php
                        // Fetch unique attributes already linked to this product's variants
                        $existingAttributes = \App\Models\AwAttribute::whereHas('values.variants', function($q) use ($product) {
                            $q->where('product_id', $product->id);
                        })->with(['values' => function($q) use ($product) {
                            $q->whereHas('variants', function($v) use ($product) { $v->where('product_id', $product->id); });
                        }])->get();
                    @endphp

                    @forelse($existingAttributes as $index => $attr)
                        <div class="attribute-row mb-3 d-flex gap-2 align-items-start">
                            <div class="form-check pt-2"><input class="form-check-input attr-active" type="checkbox" checked></div>
                            <div style="width: 200px;">
                                <input name="attr_name[{{$index}}]" type="text" class="form-control attr-name" value="{{ $attr->name }}">
                            </div>
                            <div class="flex-grow-1">
                                <select name="attr_values[{{$index}}][]" class="form-control select2-tags attr-values" multiple>
                                    @foreach($attr->values as $val)
                                        <option value="{{ $val->value }}" selected>{{ $val->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-attribute"><i class="fa fa-trash"></i></button>
                        </div>
                    @empty
                        <div class="attribute-row mb-3 d-flex gap-2 align-items-start">
                            <div class="form-check pt-2"><input class="form-check-input attr-active" type="checkbox" checked></div>
                            <div style="width: 200px;"><input name="attr_name[0]" type="text" class="form-control attr-name" placeholder="Attribute (e.g. Color)"></div>
                            <div class="flex-grow-1"><select name="attr_values[0][]" class="form-control select2-tags attr-values" multiple></select></div>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-attribute"><i class="fa fa-trash"></i></button>
                        </div>
                    @endforelse
                </div>
            
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm" id="add-attribute">
                    <i class="fa fa-plus me-1"></i> Add Another Attribute
                </button>
                <button type="button" class="btn btn-primary btn-sm ms-2" id="generate-variants">
                    <i class="fa fa-sync me-1"></i> Generate Variants
                </button>
            </div>
        </div>

        <div id="variants-table-container" class="d-none animate__animated animate__fadeIn">
            <div class="table-responsive">
                <table class="table align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th width="100">Images</th>
                            <th>Variant Name</th>
                            <th>SKU</th>
                            <th>Barcode</th>
                            <th>Tax Slab</th>
                            <th>Attributes</th>
                            <th>Status</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="variant-tbody">
                        @foreach($product->variants as $i => $variant)
                            @php
                                $primaryImg = $variant->images->where('position', 0)->first();
                                $secondaryImgs = $variant->images->where('position', '!=', 0)->pluck('image_path')->toArray();
                                $attrData = $variant->attributes->mapWithKeys(function($item) {
                                    return [$item->attribute->name => $item->value];
                                })->toArray();
                            @endphp
                            <tr class="variant-row" data-index="{{ $i }}">
                                <td>
                                    <div class="text-center">
                                        @foreach($attrData as $key => $val)
                                            <input type="hidden" name="variants[{{$i}}][attr_data][{{$key}}]" value="{{$val}}">
                                        @endforeach
                                        <img src="{{ $primaryImg ? asset('storage/'.$primaryImg->image_path) : asset('assets/img/placeholder.png') }}" class="img-thumbnail variant-preview-img" style="width:45px; height:45px; object-fit:cover;">
                                        <br>
                                        <button type="button" class="btn btn-link btn-sm p-0 open-image-manager" style="font-size:11px">Manage</button>
                                        <input type="hidden" name="variants[{{$i}}][image_data]" class="variant-image-data" 
                                            value="{{ json_encode(['primary' => $primaryImg ? asset('storage/'.$primaryImg->image_path) : null, 'secondary' => array_map(fn($p) => asset('storage/'.$p), $secondaryImgs)]) }}">
                                    </div>
                                </td>
                                <td><input type="text" name="variants[{{$i}}][name]" class="form-control form-control-sm" value="{{ $variant->name }}"></td>
                                <td><input type="text" name="variants[{{$i}}][sku]" class="form-control form-control-sm" value="{{ $variant->sku }}"></td>
                                <td><input type="text" name="variants[{{$i}}][barcode]" class="form-control form-control-sm" value="{{ $variant->barcode }}"></td>
                                <td>
                                    <select name="variants[{{$i}}][tax_slab_id]" class="form-control form-control-sm">
                                        <option value="">-- None --</option>
                                        @foreach($taxSlabs as $taxSlab)
                                            <option value="{{ $taxSlab->id }}" {{ ($variant->tax_slab_id == $taxSlab->id) ? 'selected' : '' }}>{{ $taxSlab->name }} ({{ $taxSlab->tax_percentage }}%)</option>
                                        @endforeach
                                    </select>
                                    @if($loop->first)
                                        <button type="button" class="btn btn-primary btn-sm mt-2 slab-copy-to-all"> Copy to All Variants </button>
                                    @endif
                                </td>
                                <td>
                                    @foreach($attrData as $val)
                                        <span class="badge bg-soft-dark text-dark border me-1">{{ $val }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input variant-toggle" type="checkbox" name="variants[{{$i}}][status]" {{ $variant->status == 'active' ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td><button type="button" class="btn btn-link text-danger p-0 delete-row"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="bulk-generate-barcodes">Generate Barcodes</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="bulk-enable-all">Enable All</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="variantImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-dark">Manage Variant Images</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <input type="hidden" id="current_variant_index" value="">

                <div class="row">
                    <div class="col-md-12 mb-4">
                        <label class="form-label fw-bold text-primary">Primary Image <span class="text-danger">*</span></label>
                        <div class="primary-image-upload-box border rounded p-4 text-center bg-white">
                            <div id="primary-preview-container" class="mb-3">
                                <img id="primary-preview" src="{{ asset('assets/img/sketchbook-placeholder.png') }}" 
                                     class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: contain;">
                            </div>
                            <div class="input-group">
                                <input type="file" class="form-control" id="primary-image-input" accept="image/*">
                                <label class="input-group-text btn btn-primary" for="primary-image-input">Choose file</label>
                            </div>
                            <small class="text-muted d-block mt-2">Upload a single primary image (JPG, PNG, WEBP - Max 5MB)</small>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold text-primary">Secondary Images</label>
                        <div class="secondary-upload-box border rounded p-3 bg-white">
                            <div class="input-group mb-3">
                                <input type="file" class="form-control" id="secondary-images-input" multiple accept="image/*">
                                <label class="input-group-text btn btn-outline-primary" for="secondary-images-input">Choose files</label>
                            </div>
                            <small class="text-muted d-block mb-3">Upload multiple secondary images (JPG, PNG, WEBP - Max 5MB each)</small>
                            
                            <div id="secondary-gallery" class="row g-3">
                                <div class="col-md-4 gallery-item">
                                    <div class="card h-100 shadow-sm border-0">
                                        <img src="{{ asset('assets/img/gallery-1.png') }}" class="card-img-top rounded p-2" style="height: 150px; object-fit: cover;">
                                        <div class="card-footer bg-white border-0 p-2">
                                            <button type="button" class="btn btn-danger btn-sm w-100 delete-image-btn">
                                                <i class="fa fa-trash me-1"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary px-4 shadow-sm" id="save-variant-images">Save & Continue</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('product-js')
<script>
var taxSlabsJson = @json($taxSlabs);
$(document).ready(function() {
    function initSelect2(element) {
        $(element).select2({
            tags: true,
            tokenSeparators: [','],
            width: '100%',
            placeholder: $(element).data('placeholder')
        });
    }

    initSelect2('.select2-tags');

    $('#add-attribute').on('click', function() {
        let index = $('#attribute-wrapper .attribute-row').length;

        const html = `
            <div class="attribute-row mb-3 d-flex gap-2 align-items-start animate__animated animate__fadeIn">
                <div class="form-check pt-2">
                    <input class="form-check-input attr-active" type="checkbox" checked>
                </div>
                <div style="width: 200px;">
                    <input type="text" name="attr_name[${index}]" class="form-control attr-name" placeholder="Attribute (e.g. Size)">
                </div>
                <div class="flex-grow-1">
                    <select name="attr_values[${index}][]" class="form-control select2-dynamic attr-values" multiple data-placeholder="Add values..."></select>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-attribute"><i class="fa fa-trash"></i></button>
            </div>`;
        $('#attribute-wrapper').append(html);
        initSelect2('.select2-dynamic:last');
    });

    $(document).on('click', '.remove-attribute', function() {
        $(this).closest('.attribute-row').remove();
    });

    $('#generate-variants').on('click', function() {
        const productName = $('#product_name').val() || "Product";
        let attributes = [];

        $('.attribute-row').each(function() {
            const isActive = $(this).find('.attr-active').is(':checked');
            const name = $(this).find('.attr-name').val().trim();
            const values = $(this).find('.attr-values').val();

            if (isActive && name !== "" && values && values.length > 0) {
                attributes.push({ name: name, values: values });
            }
        });

        if (attributes.length === 0) {
            alert("Error: Please provide at least one attribute name and at least one value.");
            return;
        }

        const combinations = attributes.reduce((acc, curr) => {
            return acc.flatMap(a => curr.values.map(b => ({ ...a, [curr.name]: b })));
        }, [{}]);

        renderTable(combinations, productName);
    });

    function renderTable(combinations, productName) {
        const tbody = $('#variant-tbody');
        tbody.empty();
        $('#variant-count-badge').text(combinations.length + ' Variants Generated');

        combinations.forEach((combo, i) => {
            const attrLabels = Object.values(combo).join(' / ');
            
            let attrHiddenInputs = '';
            Object.entries(combo).forEach(([key, val]) => {
                attrHiddenInputs += `<input type="hidden" name="variants[${i}][attr_data][${key}]" value="${val}">`;
            });

            const attrBadges = Object.entries(combo).map(([key, val]) => 
                `<span class="badge bg-soft-dark text-dark border me-1">${val}</span>`
            ).join('');

            let taxSlabCopyToAllBtn = '';

            if (i === 0) {
                taxSlabCopyToAllBtn = `<button type="button" class="btn btn-primary btn-sm mt-2 slab-copy-to-all"> Copy to All Variants </button>`;
            }

            const row = `
                <tr class="variant-row" data-index="${i}">
                    <td>
                        <div class="text-center">
                            ${attrHiddenInputs} <img src="/assets/img/placeholder.png" class="img-thumbnail variant-preview-img" style="width:45px; height:45px; object-fit:cover;">
                            <br>
                            <button type="button" class="btn btn-link btn-sm p-0 open-image-manager" style="font-size:11px">Manage</button>
                            <input type="hidden" name="variants[${i}][image_data]" class="variant-image-data">
                        </div>
                    </td>
                    <td>
                        <input type="text" name="variants[${i}][name]" class="form-control form-control-sm" value="${productName} - ${attrLabels}">
                    </td>
                    <td>
                        <input type="text" name="variants[${i}][sku]" class="form-control form-control-sm" placeholder="SKU" value="SKU-${Math.random().toString(36).substr(2, 6).toUpperCase()}">
                    </td>
                    <td>
                        <input type="text" name="variants[${i}][barcode]" class="form-control form-control-sm barcode-input" placeholder="Barcode">
                    </td>
                    <td>
                        <select name="variants[${i}][tax_slab_id]" class="form-control form-control-sm">
                            <option value="">-- None --</option>
                            ${taxSlabsJson.map(ts => `<option value="${ts.id}">${ts.name} (${ts.tax_percentage}%)</option>`).join('')}
                        </select>
                        ${taxSlabCopyToAllBtn}
                    </td>
                    <td>${attrBadges}</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input variant-toggle" type="checkbox" name="variants[${i}][status]" checked>
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-link text-danger p-0 delete-row"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>`;

                taxSlabCopyToAllBtn = '';

            tbody.append(row);
        });

        $('#variants-table-container').removeClass('d-none');
    }

    $(document).on('click', '.slab-copy-to-all', function() {
        let selectedSlabId = $(this).siblings('select').val();
        let that = this;

        if (!selectedSlabId) {
            Swal.fire({
                title: 'No Tax Slab Selected',
                text: "Please select a tax slab to copy to all variants.",
                icon: 'info',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Copy Tax Slab to All Variants?',
            text: "This will overwrite the tax slab for all variants. Are you sure?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, copy it!',
            cancelButtonText: 'No, keep them'
        }).then((result) => {
            if (result.isConfirmed) {
                $('.variant-row').each(function() {
                    $(this).find('select[name$="[tax_slab_id]"]').val(selectedSlabId);
                });
            }
        });
    });

    $(document).on('click', '#save-variant-images', function() {
        const index = $('#current_variant_index').val();
        const primaryImg = $('#primary-preview').attr('src');
        
        const row = $(`.variant-row[data-index="${index}"]`);
        row.find('.variant-preview-img').attr('src', primaryImg);
        
        row.find('.variant-image-data').val(JSON.stringify({
            primary: primaryImg,
            secondary: []
        }));

        $('#variantImageModal').modal('hide');
    });

    $('#bulk-enable-all').on('click', function() {
        $('.variant-toggle').prop('checked', true);
    });

    $('#bulk-generate-barcodes').on('click', function() {
        $('.barcode-input').each(function() {
            $(this).val('BC' + Math.floor(Math.random() * 899999999 + 100000000));
        });
    });

    $(document).on('click', '.delete-row', function() {
        let that = this;

        if ($('.variant-row').length <= 1) {
            Swal.fire({
                title: 'Cannot Delete',
                text: "Variable product must have at least a variant.",
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Variant?',
            text: "This will permanently delete this variant. Are you sure?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                $(that).closest('tr').remove();

                if ($(that).parent().parent().find('.slab-copy-to-all').length) {
                    $(`<button type="button" class="btn btn-primary btn-sm mt-2 slab-copy-to-all"> Copy to All Variants </button>`).insertAfter($('#variant-tbody tr:eq(0)').find('td:eq(4) select'));
                }
            }
        });
    });

    $(document).on('click', '.open-image-manager', function() {
        const row = $(this).closest('tr');
        const index = row.data('index');
        
        $('#current_variant_index').val(index);
        
        let existingData = row.find('.variant-image-data').val();
        if (existingData) {
            let data = JSON.parse(existingData);
            $('#primary-preview').attr('src', data.primary || '/assets/img/placeholder.png');
        } else {
            $('#primary-preview').attr('src', '/assets/img/placeholder.png');
            $('#secondary-gallery').empty();
        }
        
        $('#variantImageModal').modal('show');
    });

    $(document).on('change', '#primary-image-input', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $('#primary-preview').attr('src', event.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    $(document).on('change', '#secondary-images-input', function(e) {
        const files = e.target.files;
        const gallery = $('#secondary-gallery');
        
        $.each(files, function(i, file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const html = `
                    <div class="col-md-4 gallery-item animate__animated animate__fadeIn">
                        <div class="card h-100 shadow-sm border-0 position-relative">
                            <img src="${event.target.result}" class="card-img-top rounded p-2 secondary-preview-img" style="height: 120px; object-fit: cover;">
                            <div class="card-footer bg-white border-0 p-1">
                                <button type="button" class="btn btn-danger btn-xs w-100 delete-gallery-img" style="font-size:10px">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>`;
                gallery.append(html);
            };
            reader.readAsDataURL(file);
        });
    });

    $(document).on('click', '.delete-gallery-img', function() {
        $(this).closest('.gallery-item').remove();
    });

    $(document).on('click', '#save-variant-images', function() {
        const index = $('#current_variant_index').val();
        const primarySrc = $('#primary-preview').attr('src');
        
        let secondaryImages = [];
        $('.secondary-preview-img').each(function() {
            secondaryImages.push($(this).attr('src'));
        });

        const row = $(`.variant-row[data-index="${index}"]`);
        
        row.find('.variant-preview-img').attr('src', primarySrc);
        
        row.find('.variant-image-data').val(JSON.stringify({
            primary: primarySrc,
            secondary: secondaryImages
        }));

        $('#variantImageModal').modal('hide');
    });

    if ($('#variant-tbody tr').length > 0) {
        $('#variants-table-container').removeClass('d-none');
        $('#variant-count-badge').text($('#variant-tbody tr').length + ' Variants Loaded');
    }
});
</script>
@endpush