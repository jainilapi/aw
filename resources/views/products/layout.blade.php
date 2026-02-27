@extends('layouts.app', ['title' => 'Product Management', 'subTitle' => 'Enter the information for your product', 'select2' => true, 'editor' => true])

@push('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
@stack('product-css')
<style>
    label.error {
        color: red;
    }
    .note-editor.border-danger {
        border: 1px solid #dc3545 !important;
    }

    .select2-container .select2-selection--single {
        height: 44px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-top: 5px;
    }
    .step-item:not(.active):not(.completed):hover .step-label {
        color: #203c70;
        /* font-size: 15px; */
        font-weight: bolder;
        text-decoration: none;
    }

    .step-item:not(.active):not(.completed):hover .step-circle {
        /* width: 45px;
        height: 45px;
        font-size: 25px; */
        font-weight: bolder;
        box-shadow: 8px 6px 15px;
    }

.step-item a:hover,
.step-item a:focus,
.step-item a:active {
    text-decoration: none;
}
</style>
@endpush

@section('content')
@include('products.steps', ['currentStep' => $step, 'type' => $type])

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt($step), 'id' => encrypt($product->id)]) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf

    @yield('product-content')

    <div class="mt-4 d-flex @if($step - 1 > 0) justify-content-between @else justify-content-end @endif">
            @if($step - 1 > 0)
                <a class="btn btn-secondary" href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt($step - 1), 'id' => encrypt($product->id)]) }}"> Back </a>
            @endif
        <button type="submit" class="btn btn-primary">Save & Continue</button>
    </div>
</form>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>
<script>

</script>
@stack('product-js')
@endpush