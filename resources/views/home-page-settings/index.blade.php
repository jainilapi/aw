@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Home Page Settings</h4>
                <div class="page-title-right">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reorderModal">
                        Reorder Sections
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        @foreach($settings as $index => $setting)
                            <li class="nav-item">
                                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-{{ $setting->key }}" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        @foreach($settings as $index => $setting)
                            @php
                                $isEditable = $setting->value->is_editable ?? true;
                            @endphp
                            <div class="tab-pane {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $setting->key }}" role="tabpanel">
                                @if(!$isEditable)
                                    <div class="alert alert-warning">
                                        This section is not editable.
                                    </div>
                                @endif
                                <form action="{{ route('home-page-settings.update', $setting->key) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    @if(isset($setting->value->visible))
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="visible-{{ $setting->key }}" name="visible" {{ $setting->value->visible ? 'checked' : '' }} {{ !$isEditable ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="visible-{{ $setting->key }}">Visible</label>
                                        </div>
                                    @endif

                                    @if($setting->key === 'banner_carousel')
                                        <div id="slides-container">
                                            @foreach($setting->value->slides as $i => $slide)
                                                <div class="card border mb-3 slide-item">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <span>Slide <span class="slide-number">{{ $i + 1 }}</span></span>
                                                        @if($isEditable)
                                                            <button type="button" class="btn btn-danger btn-sm remove-slide">Remove</button>
                                                        @endif
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Image</label>
                                                                <input type="file" class="form-control" name="slides[{{ $i }}][image]" {{ !$isEditable ? 'disabled' : '' }}>
                                                                @if(!empty($slide->image))
                                                                    <div class="mt-2">
                                                                        <img src="{{ Storage::url($slide->image) }}" alt="Slide Image" style="max-height: 100px;">
                                                                    </div>
                                                                @endif
                                                                <input type="hidden" name="slides[{{ $i }}][image]" value="{{ $slide->image ?? '' }}">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Heading</label>
                                                                <input type="text" class="form-control" name="slides[{{ $i }}][heading]" value="{{ $slide->heading }}" {{ !$isEditable ? 'disabled' : '' }}>
                                                            </div>
                                                            <div class="col-md-12 mb-3">
                                                                <label class="form-label">Description</label>
                                                                <textarea class="form-control" name="slides[{{ $i }}][description]" {{ !$isEditable ? 'disabled' : '' }}>{{ $slide->description }}</textarea>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Button Redirect URL</label>
                                                                <input type="text" class="form-control" name="slides[{{ $i }}][redirect]" value="{{ $slide->redirect }}" {{ !$isEditable ? 'disabled' : '' }}>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Button Title</label>
                                                                <input type="text" class="form-control" name="slides[{{ $i }}][button_title]" value="{{ $slide->button_title }}" {{ !$isEditable ? 'disabled' : '' }}>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" name="slides[{{ $i }}][has_button]" {{ $slide->has_button ? 'checked' : '' }} {{ !$isEditable ? 'disabled' : '' }}>
                                                                    <label class="form-check-label">Has Button</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($isEditable)
                                            <button type="button" class="btn btn-success btn-sm" id="add-slide">Add Slide</button>
                                        @endif

                                    @elseif($setting->key === 'top_categories_grid' || $setting->key === 'top_categories_linear')
                                        <div id="categories-container-{{ $setting->key }}">
                                            @foreach($setting->value->categories as $i => $category)
                                                <div class="card border mb-3 category-group-item">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <span>Category Group <span class="group-number">{{ $i + 1 }}</span></span>
                                                        @if($isEditable)
                                                            <button type="button" class="btn btn-danger btn-sm remove-category-group">Remove</button>
                                                        @endif
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Title</label>
                                                            <input type="text" class="form-control" name="categories[{{ $i }}][title]" value="{{ $category->title }}" {{ !$isEditable ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Link</label>
                                                            <input type="text" class="form-control" name="categories[{{ $i }}][link]" value="{{ $category->link }}" {{ !$isEditable ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Products</label>
                                                            <select class="form-control select2" name="categories[{{ $i }}][items][]" multiple {{ !$isEditable ? 'disabled' : '' }}>
                                                                @foreach($products as $product)
                                                                    <option value="{{ $product->id }}" {{ in_array($product->id, $category->items) ? 'selected' : '' }}>{{ $product->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($isEditable)
                                            <button type="button" class="btn btn-success btn-sm add-category-group" data-key="{{ $setting->key }}">Add Category Group</button>
                                        @endif

                                    @elseif($setting->key === 'top_selling_products')
                                        <div class="mb-3">
                                            <label class="form-label">Products</label>
                                            <select class="form-control select2" name="products[]" multiple {{ !$isEditable ? 'disabled' : '' }}>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ in_array($product->id, $setting->value->products) ? 'selected' : '' }}>{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($setting->key === 'footer')
                                        <div class="row">
                                            <div class="col-md-6">
                                                 <div class="mb-3">
                                                    <label class="form-label">About Title</label>
                                                    <input type="text" class="form-control" name="footer_content[about_title]" value="{{ $setting->value->about_title ?? 'About Anjo Wholesale' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">About Subtitle</label>
                                                    <input type="text" class="form-control" name="footer_content[about_subtitle]" value="{{ $setting->value->about_subtitle ?? 'Mon-Fri 8AM-4PM' }}">
                                                </div>
                                                 <div class="mb-3">
                                                    <label class="form-label">About Description</label>
                                                    <textarea class="form-control" name="footer_content[about_description]" rows="3">{{ $setting->value->about_description ?? 'Our goods are delivered at no extra charge once our delivery requirements are met! Give us a call to find out more information.' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone Title</label>
                                                    <input type="text" class="form-control" name="footer_content[phone_title]" value="{{ $setting->value->phone_title ?? 'Phone' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Phone Numbers (One per line)</label>
                                                    <textarea class="form-control" name="footer_content[phone_numbers]" rows="4">{{ $setting->value->phone_numbers ?? "(268) 480-3080 (AR)\n(268) 736 5814 (Whatsapp)\n(268) 480-3046/7 (Coolidge)\n(268) 480-3086 (Fax)" }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                             <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Email Title</label>
                                                    <input type="text" class="form-control" name="footer_content[email_title]" value="{{ $setting->value->email_title ?? 'Email' }}">
                                                </div>
                                                 <div class="mb-3">
                                                    <label class="form-label">Emails (One per line)</label>
                                                    <textarea class="form-control" name="footer_content[emails]" rows="4">{{ $setting->value->emails ?? "anjo.w@candw.ag\nHR@anjowholesale.com\ninfo@anjowholesale.com\nmario.winter@anjowholesale.com" }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                 <div class="mb-3">
                                                    <label class="form-label">Address Title</label>
                                                    <input type="text" class="form-control" name="footer_content[address_title]" value="{{ $setting->value->address_title ?? 'P.O. Box' }}">
                                                </div>
                                                 <div class="mb-3">
                                                    <label class="form-label">Address Content</label>
                                                    <textarea class="form-control" name="footer_content[address_content]" rows="4">{{ $setting->value->address_content ?? "Anjo Wholesale\nP.O. Box 104 St. John's, \nAntigua & Barbuda" }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Facebook URL</label>
                                                    <input type="text" class="form-control" name="footer_content[facebook_url]" value="{{ $setting->value->facebook_url ?? '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Instagram URL</label>
                                                    <input type="text" class="form-control" name="footer_content[instagram_url]" value="{{ $setting->value->instagram_url ?? '' }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Bottom Footer Text</label>
                                            <input type="text" class="form-control" name="footer_content[bottom_text]" value="{{ $setting->value->bottom_text ?? 'Anjo Wholesale' }}">
                                        </div>

                                         <div class="mb-3">
                                            <label class="form-label">Bottom Address Lines (One per line)</label>
                                            <textarea class="form-control" name="footer_content[bottom_address]" rows="3">{{ $setting->value->bottom_address ?? "American Road St. John's, Antigua & Barbuda\nCoolidge, Corner of Sir George Walter Highway & Powells Main Road" }}</textarea>
                                        </div>
                                    @endif

                                    @if($isEditable)
                                        <div class="text-end mt-3">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#add-slide').click(function() {
            let index = $('.slide-item').length;
            let template = `
                <div class="card border mb-3 slide-item">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Slide <span class="slide-number">${index + 1}</span></span>
                        <button type="button" class="btn btn-danger btn-sm remove-slide">Remove</button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="slides[${index}][image]">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Heading</label>
                                <input type="text" class="form-control" name="slides[${index}][heading]">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="slides[${index}][description]"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button Redirect URL</label>
                                <input type="text" class="form-control" name="slides[${index}][redirect]">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button Title</label>
                                <input type="text" class="form-control" name="slides[${index}][button_title]">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="slides[${index}][has_button]">
                                    <label class="form-check-label">Has Button</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#slides-container').append(template);
        });

        $(document).on('click', '.remove-slide', function() {
            $(this).closest('.slide-item').remove();
            updateSlideIndices();
        });

        function updateSlideIndices() {
            $('.slide-item').each(function(index) {
                $(this).find('.slide-number').text(index + 1);
                $(this).find('input, textarea').each(function() {
                    let name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/slides\[\d+\]/, `slides[${index}]`));
                    }
                });
            });
        }

        $('.add-category-group').click(function() {
            let key = $(this).data('key');
            let container = $(`#categories-container-${key}`);
            let index = container.find('.category-group-item').length;
            
            let template = `
                <div class="card border mb-3 category-group-item">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Category Group <span class="group-number">${index + 1}</span></span>
                        <button type="button" class="btn btn-danger btn-sm remove-category-group">Remove</button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="categories[${index}][title]">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link</label>
                            <input type="text" class="form-control" name="categories[${index}][link]">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Products</label>
                            <select class="form-control select2-new" name="categories[${index}][items][]" multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            `;
            container.append(template);
            container.find('.select2-new').removeClass('select2-new').addClass('select2').select2();
        });

        $(document).on('click', '.remove-category-group', function() {
            let container = $(this).closest('.category-group-item').parent();
            $(this).closest('.category-group-item').remove();
            updateCategoryIndices(container);
        });

        function updateCategoryIndices(container) {
            container.find('.category-group-item').each(function(index) {
                $(this).find('.group-number').text(index + 1);
                $(this).find('input, select').each(function() {
                    let name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/categories\[\d+\]/, `categories[${index}]`));
                    }
                });
            });
        }
    });
</script>
@endpush