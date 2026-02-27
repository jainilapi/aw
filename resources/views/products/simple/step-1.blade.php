@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@push('product-css')
<style>
    .image-upload-container {
        position: relative;
    }

    .upload-box {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background-color: #f8f9fa;
    }

    .upload-box:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }

    .image-preview-box {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #f8f9fa;
    }

    .image-preview-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 100%;
        overflow: hidden;
    }

    .image-preview-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .image-preview-box:hover .image-overlay {
        opacity: 1;
    }

    /* Gallery Styles */
    .gallery-preview-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .gallery-item {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #f8f9fa;
        cursor: move;
    }

    .gallery-item-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 100%;
        overflow: hidden;
    }

    .gallery-media {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }

    .drag-handle {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 255, 255, 0.9);
        padding: 5px 8px;
        border-radius: 4px;
        cursor: move;
        z-index: 10;
    }

    .video-indicator {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        pointer-events: none;
    }

    .gallery-item.dragging {
        opacity: 0.5;
    }

    .gallery-item.drag-over {
        border: 2px solid #0d6efd;
    }

    #modalMediaContainer img,
    #modalMediaContainer video {
        max-width: 100%;
        max-height: 70vh;
        border-radius: 8px;
    }
</style>
@endpush

@section('product-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h4>Product Basics</h4></div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="form-group mb-3" id="sku_container">
                            <label>SKU <span class="text-danger">*</span></label>
                            <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                        </div>

                        <div class="form-group mb-3" id="tax_slab_container">
                            <label>Tax Slab <span class="text-danger">*</span></label>
                            <select name="tax_slab_id" id="tax_slab_id" class="form-control select2">
                                <option value="">-- Select Tax Slab --</option>
                                @foreach($taxSlabs as $taxSlab)
                                    <option value="{{ $taxSlab->id }}" {{ old('tax_slab_id', $product->tax_slab_id) == $taxSlab->id ? 'selected' : '' }}>{{ $taxSlab->name }} ({{ $taxSlab->tax_percentage }}%)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Brand <span class="text-danger">*</span></label>
                                <select name="brand_id" class="form-control select2" required>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tags</label>
                                <select name="tags[]" class="form-control select2-tags" multiple="multiple">
                                    @foreach($allTags as $tag)
                                        <option value="{{ $tag->name }}" {{ in_array($tag->id, $productTagIds) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="mb-3">
                                <label class="form-label">Product Type <span class="text-danger">*</span></label>
                                <div class="">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type_switch" id="typeSimple" value="simple" {{ $product->product_type === 'simple' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="typeSimple">Simple - Single product with no variations </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type_switch" id="typeVariable" value="variable" {{ $product->product_type === 'variable' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="typeVariable">Variable - Product with attributes & variants</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type_switch" id="typeBundled" value="bundle" {{ $product->product_type === 'bundle' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="typeBundled">Bundled - Multiple products sold together</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Short Description (Min 100 chars) <span class="text-danger">*</span></label>
                            <div class="summernote-wrapper">
                                <textarea name="short_description" id="short_desc" class="summernote">{{ old('short_description', $product->short_description) }}</textarea>
                                <span class="summernote-error"></span>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Long Description (Min 200 chars) <span class="text-danger">*</span></label>
                            <div class="summernote-wrapper">
                                <textarea name="long_description" id="long_desc" class="summernote">{{ old('long_description', $product->long_description) }}</textarea>
                                <span class="summernote-error"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <label>Product Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" name="status" type="checkbox" role="switch" id="flexSwitchCheckChecked" value="1" @if ($product->status == 'active') checked @endif>
                                <label class="form-check-label" for="flexSwitchCheckChecked">Active</label>
                            </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <label>In Stock Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" name="in_stock" type="checkbox" role="switch" id="flexSwitchCheckInStock" value="1" @if ($product->in_stock ?? 1) checked @endif>
                                <label class="form-check-label" for="flexSwitchCheckInStock">In Stock</label>
                            </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">

                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Main Image (800x800)</span>
                            <small class="text-muted">Max 3MB</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="image-upload-container">
                            <input type="file" name="main_image" id="mainImageInput" accept="image/jpeg,image/png,image/webp" style="display: none;">
                            
                            <div id="mainImagePreview" class="image-preview-box {{ $mainImage ? '' : 'd-none' }}">
                                <div class="image-preview-wrapper">
                                    @if($mainImage)
                                        <img src="{{ asset('storage/' . $mainImage->image_path) }}" alt="Main Image" class="img-fluid">
                                    @endif
                                </div>
                                <div class="image-overlay">
                                    <button type="button" class="btn btn-sm btn-light me-2" onclick="previewMainImage()">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeMainImage()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="mainImageUploadBox" class="upload-box {{ $mainImage ? 'd-none' : '' }}" onclick="$('#mainImageInput').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                <p class="mb-1">Click to upload main image</p>
                                <small class="text-muted">JPEG, PNG, WEBP (Max 3MB)</small>
                            </div>
                            <span class="text-danger main-image-error d-none"></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Gallery (Max 5 Media)</span>
                            <small class="text-muted">Max 5MB each</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="gallery-upload-container">
                            <input type="file" name="secondary_media[]" id="galleryInput" accept="image/jpeg,image/png,image/webp,video/mp4,video/wav" multiple style="display: none;">
                            <input type="hidden" name="existing_gallery_ids" id="existing_gallery_ids">

                            <div id="galleryPreview" class="gallery-preview-grid">
                                @foreach($gallery as $img)
                                <div class="gallery-item" data-id="{{ $img->id }}" data-existing="true">
                                    <div class="gallery-item-wrapper">
                                        @if(in_array(pathinfo($img->image_path, PATHINFO_EXTENSION), ['mp4', 'wav']))
                                            <video class="gallery-media">
                                                <source src="{{ asset('storage/' . $img->image_path) }}" type="video/{{ pathinfo($img->image_path, PATHINFO_EXTENSION) }}">
                                            </video>
                                            <div class="video-indicator">
                                                <i class="fas fa-play-circle"></i>
                                            </div>
                                        @else
                                            <img src="{{ asset('storage/' . $img->image_path) }}" alt="Gallery" class="gallery-media">
                                        @endif
                                    </div>
                                    <div class="gallery-overlay">
                                        <button type="button" class="btn btn-sm btn-light me-1" onclick="previewGalleryItem(this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeGalleryItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="drag-handle">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="upload-box mt-2" id="galleryUploadBox" onclick="$('#galleryInput').click()">
                                <i class="fas fa-images fa-2x text-muted mb-2"></i>
                                <p class="mb-1">Click to add gallery media</p>
                                <small class="text-muted">JPEG, PNG, WEBP, MP4, WAV (Max 5 items)</small>
                            </div>
                            <span class="text-danger gallery-error d-none"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Media Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="modalMediaContainer"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('product-js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.select2-tags').select2({ tags: true, tokenSeparators: [',', ' '] });

        $('.summernote').summernote({
            height: 200,
            placeholder: 'Write here...',
            toolbar: [
                ['style', ['bold', 'italic', 'underline']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['codeview']]
            ]
        });

        function getSummernoteTextLength(selector) {
            let content = $(selector).summernote('code');
            return $('<div>').html(content).text().trim().length;
        }

        $.validator.addMethod('minSummernote', function (value, element, params) {
            return getSummernoteTextLength(element) >= params;
        });

        let mainImageFile = null;
        let hasExistingMainImage = {{ $mainImage ? 'true' : 'false' }};

        $('#mainImageInput').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const maxSize = 3 * 1024 * 1024;

            if (!validTypes.includes(file.type)) {
                showMainImageError('Please select a valid image file (JPEG, PNG, or WEBP)');
                $(this).val('');
                return;
            }

            if (file.size > maxSize) {
                showMainImageError('Image size must not exceed 3MB');
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#mainImagePreview .image-preview-wrapper').html(`<img src="${e.target.result}" alt="Preview" class="img-fluid">`);
                $('#mainImagePreview').removeClass('d-none');
                $('#mainImageUploadBox').addClass('d-none');
                hideMainImageError();
            };
            reader.readAsDataURL(file);

            mainImageFile = file;
            hasExistingMainImage = false;
        });

        function showMainImageError(message) {
            $('.main-image-error').text(message).removeClass('d-none');
        }

        function hideMainImageError() {
            $('.main-image-error').text('').addClass('d-none');
        }

        window.removeMainImage = function() {
            $('#mainImageInput').val('');
            $('#mainImagePreview').addClass('d-none');
            $('#mainImageUploadBox').removeClass('d-none');
            mainImageFile = null;
            hasExistingMainImage = false;
        };

        window.previewMainImage = function() {
            const imgSrc = $('#mainImagePreview img').attr('src');
            $('#modalMediaContainer').html(`<img src="${imgSrc}" alt="Preview" class="img-fluid">`);
            new bootstrap.Modal($('#mediaPreviewModal')).show();
        };

        let galleryFiles = [];
        const maxGalleryItems = 5;

        $('#galleryInput').on('change', function(e) {
            const files = Array.from(e.target.files);
            const currentCount = $('.gallery-item').length;
            const availableSlots = maxGalleryItems - currentCount;

            if (files.length > availableSlots) {
                showGalleryError(`You can only add ${availableSlots} more item(s). Maximum is ${maxGalleryItems}.`);
                $(this).val('');
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/wav'];
            const maxSize = 5 * 1024 * 1024;

            for (let file of files) {
                if (!validTypes.includes(file.type)) {
                    showGalleryError('Please select valid files (JPEG, PNG, WEBP, MP4, or WAV)');
                    $(this).val('');
                    return;
                }

                if (file.size > maxSize) {
                    showGalleryError('Each file must not exceed 5MB');
                    $(this).val('');
                    return;
                }
            }

            hideGalleryError();

            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addGalleryItem(e.target.result, file);
                };
                reader.readAsDataURL(file);
            });

            $(this).val('');
            updateGalleryUploadBox();
        });

        function addGalleryItem(src, file) {
            const isVideo = file.type.startsWith('video/');
            const mediaElement = isVideo 
                ? `<video class="gallery-media"><source src="${src}" type="${file.type}"></video><div class="video-indicator"><i class="fas fa-play-circle"></i></div>`
                : `<img src="${src}" alt="Gallery" class="gallery-media">`;

            const itemHtml = `
                <div class="gallery-item" data-existing="false">
                    <div class="gallery-item-wrapper">
                        ${mediaElement}
                    </div>
                    <div class="gallery-overlay">
                        <button type="button" class="btn btn-sm btn-light me-1" onclick="previewGalleryItem(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeGalleryItem(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="drag-handle">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                </div>
            `;

            $('#galleryPreview').append(itemHtml);
            galleryFiles.push(file);
        }

        function showGalleryError(message) {
            $('.gallery-error').text(message).removeClass('d-none');
        }

        function hideGalleryError() {
            $('.gallery-error').text('').addClass('d-none');
        }

        function updateGalleryUploadBox() {
            const currentCount = $('.gallery-item').length;
            if (currentCount >= maxGalleryItems) {
                $('#galleryUploadBox').addClass('d-none');
            } else {
                $('#galleryUploadBox').removeClass('d-none');
            }
        }

        window.removeGalleryItem = function(btn) {
            const item = $(btn).closest('.gallery-item');
            const index = item.index();
            const isExisting = item.data('existing') === true;

            if (!isExisting) {
                const newFileIndex = $('.gallery-item[data-existing="false"]').index(item);
                galleryFiles.splice(newFileIndex, 1);
            }

            item.remove();
            updateGalleryUploadBox();
            hideGalleryError();
        };

        window.previewGalleryItem = function(btn) {
            const item = $(btn).closest('.gallery-item');
            const mediaElement = item.find('.gallery-media')[0];
            
            let previewHtml;
            if (mediaElement.tagName === 'VIDEO') {
                const src = $(mediaElement).find('source').attr('src');
                const type = $(mediaElement).find('source').attr('type');
                previewHtml = `<video controls style="max-width: 100%; max-height: 70vh;"><source src="${src}" type="${type}"></video>`;
            } else {
                const src = $(mediaElement).attr('src');
                previewHtml = `<img src="${src}" alt="Preview" class="img-fluid">`;
            }

            $('#modalMediaContainer').html(previewHtml);
            new bootstrap.Modal($('#mediaPreviewModal')).show();
        };

        const galleryPreview = document.getElementById('galleryPreview');
        Sortable.create(galleryPreview, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'dragging',
            onEnd: function() {

            }
        });

        function toggleSkuField() {
            if ($('#typeSimple').is(':checked')) {
                $('#sku_container').show();
                $('#tax_slab_container').show();
            } else {
                $('#sku_container').hide();
                $('#tax_slab_container').hide();
            }
        }

        $('input[name="type_switch"]').on('change', toggleSkuField);
        toggleSkuField();

        $('#productForm').validate({
            ignore: [],
            rules: {
                sku: {
                    required: function() {
                        return $('#typeSimple').is(':checked');
                    }
                },
                tax_slab_id: {
                    required: function() {
                        return $('#typeSimple').is(':checked');
                    }
                },
                short_description: {
                    required: true,
                    minSummernote: 100
                },
                long_description: {
                    required: true,
                    minSummernote: 200
                }
            },
            messages: {
                sku: {
                    required: 'SKU is required for simple products.'
                },
                tax_slab_id: {
                    required: 'Tax Slab is required for simple products.'
                },
                short_description: {
                    required: 'Short Description is required.',
                    minSummernote: 'Short Description must be at least 100 characters.'
                },
                long_description: {
                    required: 'Long Description is required.',
                    minSummernote: 'Long Description must be at least 200 characters.'
                }
            },
            errorElement: 'span',
            errorClass: 'text-danger d-block mt-1',

            errorPlacement: function (error, element) {
                if ($(element).hasClass('summernote')) {
                    element
                        .closest('.summernote-wrapper')
                        .find('.summernote-error')
                        .html(error);
                } else {
                    error.insertAfter(element);
                }
            },

            highlight: function (element) {
                if ($(element).hasClass('summernote')) {
                    $(element).next('.note-editor').addClass('border border-danger');
                }
            },

            unhighlight: function (element) {
                if ($(element).hasClass('summernote')) {
                    $(element).next('.note-editor').removeClass('border border-danger');
                    $(element)
                        .closest('.summernote-wrapper')
                        .find('.summernote-error')
                        .empty();
                }
            },

            submitHandler: function (form) {
                if (!hasExistingMainImage && !mainImageFile) {
                    showMainImageError('Main image is required');
                    return false;
                }

                const existingIds = [];
                $('.gallery-item[data-existing="true"]').each(function() {
                    const id = $(this).data('id');
                    if (id) {
                        existingIds.push(id);
                    }
                });
                $('#existing_gallery_ids').val(JSON.stringify(existingIds));

                const formData = new FormData(form);
                
                formData.delete('main_image');
                formData.delete('secondary_media[]');

                if (mainImageFile) {
                    formData.append('main_image', mainImageFile);
                }

                galleryFiles.forEach((file, index) => {
                    formData.append('secondary_media[]', file);
                });

                $.ajax({
                    url: $(form).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        window.location.href = response.redirect || $(form).attr('action');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(key => {
                                alert(errors[key][0]);
                            });
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                    }
                });

                return false;
            }
        });

        updateGalleryUploadBox();
    });
</script>
@endpush