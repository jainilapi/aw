@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@push('product-css')
<style>
.category-tree {
    background-color: #f8f9fa;
}

.category-item {
    margin-bottom: 5px;
}

.category-toggle {
    cursor: pointer;
    color: #6c757d;
    text-decoration: none;
    margin-right: 5px;
}

.category-toggle:hover {
    color: #495057;
}

.category-children {
    margin-left: 20px;
    margin-top: 5px;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    cursor: pointer;
}

.card-subtitle {
    color: #6c757d;
    font-size: 0.875rem;
}

.additional-categories .form-check {
    padding-left: 1.5rem;
}

.additional-categories .form-check-label {
    cursor: pointer;
}

.additional-categories .form-check-label:hover {
    color: #0d6efd;
}
</style>
@endpush

@section('product-content')
<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-4">
            <label for="primary_category" class="form-label">
                Primary Category <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="categorySearch" placeholder="Search categories...">
            </div>
            <div class="category-tree mt-3 border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                @if(isset($categories) && count($categories) > 0)
                    @include('products.category-tree', [
                        'categories' => $categories,
                        'selectedPrimary' => $selectedPrimaryCategory ?? null,
                        'type' => 'primary'
                    ])
                @else
                    <p class="text-muted">No categories available</p>
                @endif
            </div>
            <small class="form-text text-muted">
                Select the most specific category that applies to your product
            </small>
            @error('primary_category')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-4">
            <label class="form-label">Additional Categories</label>
            <div class="additional-categories border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                @if(isset($additionalCategories) && count($additionalCategories) > 0)
                    @foreach($additionalCategories as $category)
                        <div class="form-check mb-2">
                            <input class="form-check-input additional-category-checkbox" 
                                    type="checkbox" 
                                    name="additional_categories[]" 
                                    value="{{ $category->id }}" 
                                    id="additional_{{ $category->id }}"
                                    {{ in_array($category->id, $selectedAdditionalCategories ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="additional_{{ $category->id }}">
                                <i class="fas fa-{{ $category->icon ?? 'folder' }}"></i>
                                {{ $category->name }}
                            </label>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No additional categories available</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">SEO Settings</h5>
            </div>
            <div class="card-body">

                <div class="form-group mb-3">
                    <label for="seoTitle" class="form-label">Meta Title</label>
                    <input type="text" 
                            class="form-control @error('seo_title') is-invalid @enderror" 
                            id="seoTitle" 
                            name="seo_title" 
                            placeholder="Custom SEO title"
                            value="{{ old('seo_title', $product->meta_title ?? '') }}"
                            maxlength="60">
                    <small class="form-text text-muted">
                        Leave blank to use product name
                        <span class="float-end" id="titleCounter">0/60</span>
                    </small>
                    @error('seo_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-0">
                    <label for="seoDescription" class="form-label">Meta Description</label>
                    <textarea class="form-control @error('seo_description') is-invalid @enderror" 
                                id="seoDescription" 
                                name="seo_description" 
                                rows="4" 
                                placeholder="SEO description for search engines"
                                maxlength="160">{{ old('seo_description', $product->meta_description ?? '') }}</textarea>
                    <small class="form-text text-muted">
                        Recommended: 150-160 characters
                        <span class="float-end" id="descCounter">0/160</span>
                    </small>
                    @error('seo_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('product-js')
<script>
$(document).ready(function() {
    function updateCounter(inputId, counterId, max) {
        var length = $('#' + inputId).val().length;
        $('#' + counterId).text(length + '/' + max);
    }

    $('#seoTitle').on('input', function() {
        updateCounter('seoTitle', 'titleCounter', 60);
    });

    $('#seoDescription').on('input', function() {
        updateCounter('seoDescription', 'descCounter', 160);
    });

    updateCounter('seoTitle', 'titleCounter', 60);
    updateCounter('seoDescription', 'descCounter', 160);

    $('#categorySearch').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.category-tree .category-item').each(function() {
            var categoryName = $(this).find('label').text().toLowerCase();
            if (categoryName.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        $('.additional-categories .form-check').each(function() {
            var categoryName = $(this).find('label').text().toLowerCase();
            if (categoryName.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('input[name="primary_category"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('input[name="primary_category"]').not(this).prop('checked', false);
        }
    });

    $('.category-toggle').on('click', function(e) {
        e.preventDefault();
        var icon = $(this).find('i');
        var children = $(this).closest('.category-item').find('> .category-children');
        
        if (children.is(':visible')) {
            children.slideUp(200);
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else {
            children.slideDown(200);
            icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        }
    });

    $('#step4Form').on('submit', function(e) {
        var primarySelected = $('input[name="primary_category"]:checked').length;
        
        if (primarySelected === 0) {
            e.preventDefault();
            alert('Please select a primary category for your product.');
            return false;
        }
    });

    $('input[name="primary_category"]').on('change', function() {
        if ($(this).is(':checked')) {
            var categoryId = $(this).val();
            $('.additional-category-checkbox[value="' + categoryId + '"]').prop('checked', false).attr('disabled', true);
        } else {
            $('.additional-category-checkbox').attr('disabled', false);
        }
    });

    var selectedPrimary = $('input[name="primary_category"]:checked').val();
    if (selectedPrimary) {
        $('.additional-category-checkbox[value="' + selectedPrimary + '"]').prop('checked', false).attr('disabled', true);
    }
});
</script>
@endpush