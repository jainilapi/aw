@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">Add New Tax Slab</div>
            <div class="card-body">
                <form id="taxSlabForm" method="POST" action="{{ route('tax-slabs.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="tax_percentage" class="form-label">Tax Percentage (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control @error('tax_percentage') is-invalid @enderror" id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage') }}" required>
                        @error('tax_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Tax Slab</button>
                    <a href="{{ route('tax-slabs.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('#taxSlabForm').validate({
        rules: { 
            name: { required: true }, 
            tax_percentage: { required: true, number: true, min: 0, max: 100 }
        },
        submitHandler: function (form) { form.submit(); }
    });
});
</script>
@endpush
