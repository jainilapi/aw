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