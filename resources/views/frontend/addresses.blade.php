@extends('frontend.layouts.app')

@push('css')
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        .addresses-page { padding: 40px 0; }
        .page-header {
            background: #fff; padding: 30px; border-radius: 12px; margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); display: flex; justify-content: space-between; align-items: center;
        }
        .page-header h1 { font-size: 28px; font-weight: 600; color: #203A72; margin: 0; }
        .btn-add {
            display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px;
            background: #203A72; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
        }
        .addresses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }
        .address-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); transition: all 0.3s ease; }
        .address-card:hover { box-shadow: 0 4px 16px rgba(32, 58, 114, 0.1); }
        .address-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
        .address-name { font-size: 18px; font-weight: 600; color: #203A72; margin: 0; }
        .address-actions { display: flex; gap: 8px; }
        .btn-action { width: 32px; height: 32px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .btn-edit { background: #E3F2FD; color: #1976D2; }
        .btn-delete { background: #FFEBEE; color: #D30606; }
        .address-detail { font-size: 14px; color: #666; line-height: 1.8; }
        .address-detail i { width: 20px; color: #203A72; }
        .empty-state { text-align: center; padding: 80px 40px; background: #fff; border-radius: 12px; }
        .modal-content { border-radius: 16px; border: none; }
        .modal-header { background: #203A72; color: #fff; border-radius: 16px 16px 0 0; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        .form-label { font-weight: 500; color: #203A72; }
        .text-danger { color: #D30606 !important; font-size: 12px; }
        
        .empty-state-icon {
            width: 100px;
            height: 100px;
            background: #F5FAFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .empty-state-icon i {
            font-size: 48px;
            color: #9CADC0;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #203A72;
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 16px;
            color: #666;
            margin-bottom: 24px;
        }
        
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #D9D9D9;
            border-radius: 8px;
            min-height: 45px;
            padding: 6px;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="bred-pro">
            <div class="container">
                <div class="breadcrumb-container">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li class="active">Addresses</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="addresses-page">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($addresses->count() > 0)
                <div class="addresses-grid">
                    @foreach($addresses as $address)
                        <div class="address-card" id="address-{{ $address->id }}">
                            <div class="address-header">
                                <h3 class="address-name">{{ $address->name }}</h3>
                                <div class="address-actions">
                                    <button type="button" class="btn-action btn-edit" onclick="openEditModal({{ $address->id }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn-action btn-delete" onclick="deleteAddress({{ $address->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="address-detail">
                                <p><i class="bi bi-house"></i> {{ $address->address_line_1 }}</p>
                                @if($address->address_line_2)<p style="padding-left: 24px;">{{ $address->address_line_2 }}</p>@endif
                                <p><i class="bi bi-geo"></i> {{ $address->city?->name }}, {{ $address->state?->name }}, {{ $address->country?->name }} {{ $address->zipcode }}</p>
                                <p><i class="bi bi-envelope"></i> {{ $address->email }}</p>
                                <p><i class="bi bi-telephone"></i> {{ $address->contact_number }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h3>No Addresses Yet</h3>
                    <p>Add your first address to get started!</p>
                    <button onclick="openAddModal()" class="mt-2 btn-add">
                        <i class="bi bi-plus-lg"></i> Add New Address
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addressForm">
                    @csrf
                    <input type="hidden" id="addressId" name="address_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Location Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Home, Office" required>
                                <span class="text-danger" id="name-error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" required>
                                <span class="text-danger" id="email-error"></span>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address_line_1" id="address_line_1" required>
                                <span class="text-danger" id="address_line_1-error"></span>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" class="form-control" name="address_line_2" id="address_line_2">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <select class="form-select" name="country_id" id="country_id" required>
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger" id="country_id-error"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-select" name="state_id" id="state_id" required></select>
                                <span class="text-danger" id="state_id-error"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <select class="form-select" name="city_id" id="city_id" required></select>
                                <span class="text-danger" id="city_id-error"></span>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Zipcode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="zipcode" id="zipcode" required>
                                <span class="text-danger" id="zipcode-error"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contact_number" id="contact_number" required>
                                <span class="text-danger" id="contact_number-error"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fax</label>
                                <input type="text" class="form-control" name="fax" id="fax">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-add btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-add btn-submit" id="submitBtn">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    
    <script>
        const addresses = @json($addresses);
        let editMode = false;
        let addressModal;

        $(document).ready(function () {
            addressModal = new bootstrap.Modal(document.getElementById('addressModal'));

            $('#country_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addressModal')
            });

            $('#state_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addressModal'),
                allowClear: true,
                placeholder: 'Select state',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            country_id: $('#country_id').val(),
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: data.items,
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });

            $('#city_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addressModal'),
                allowClear: true,
                placeholder: 'Select city',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            state_id: $('#state_id').val(),
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: data.items,
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });

            $('#country_id').on('change', function() {
                if (!editMode) {
                    $('#state_id').val(null).trigger('change');
                    $('#city_id').val(null).trigger('change');
                }
            });

            $('#state_id').on('change', function() {
                if (!editMode) {
                    $('#city_id').val(null).trigger('change');
                }
            });

            $('.form-select').on('change', function() {
                $(this).valid();
            });

            $('#addressForm').validate({
                rules: {
                    name: "required",
                    email: { required: true, email: true },
                    address_line_1: "required",
                    country_id: "required",
                    state_id: "required",
                    city_id: "required",
                    zipcode: "required",
                    contact_number: "required"
                },
                errorPlacement: function (error, element) {
                    if (element.hasClass("select2-hidden-accessible")) {
                        error.appendTo($('#' + element.attr('id') + '-error'));
                    } else {
                        error.appendTo($('#' + element.attr('name') + '-error'));
                    }
                },
                submitHandler: function (form, e) {
                    e.preventDefault();
                    submitForm();
                }
            });
        });

        function openAddModal() {
            editMode = false;
            $('#modalTitle').text('Add New Address');
            $('#addressId').val('');
            $('#addressForm')[0].reset();
            
            $('#country_id').val(null).trigger('change');
            $('#state_id').empty().trigger('change');
            $('#city_id').empty().trigger('change');
            
            $('.text-danger').text('');
            addressModal.show();
        }

        function openEditModal(id) {
            editMode = true;
            const address = addresses.find(a => a.id === id);
            if (!address) return;

            $('#modalTitle').text('Edit Address');
            $('#addressId').val(address.id);
            $('#name').val(address.name);
            $('#email').val(address.email);
            $('#address_line_1').val(address.address_line_1);
            $('#address_line_2').val(address.address_line_2);
            $('#zipcode').val(address.zipcode);
            $('#contact_number').val(address.contact_number);
            $('#fax').val(address.fax);

            // 1. Set Country
            $('#country_id').val(address.country_id).trigger('change');

            // 2. Set State (Inject option for AJAX select)
            if (address.state_id) {
                const stateName = address.state ? address.state.name : 'Selected State';
                const stateOption = new Option(stateName, address.state_id, true, true);
                $('#state_id').append(stateOption).trigger('change');
            }

            // 3. Set City (Inject option for AJAX select)
            if (address.city_id) {
                const cityName = address.city ? address.city.name : 'Selected City';
                const cityOption = new Option(cityName, address.city_id, true, true);
                $('#city_id').append(cityOption).trigger('change');
            }

            $('.text-danger').text('');
            editMode = false; // Reset flag after loading data
            addressModal.show();
        }

        function submitForm() {
            const formData = new FormData(document.getElementById('addressForm'));
            const addressId = $('#addressId').val();
            let url = '{{ route("customer.addresses.store") }}';

            if (addressId) {
                url = '{{ url("addresses") }}/' + addressId;
                formData.append('_method', 'PUT');
            }

            $('#submitBtn').prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false, // Required for FormData
                contentType: false, // Required for FormData
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                success: function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error saving address');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Save Address');
                }
            });
        }

        function deleteAddress(id) {
            if (!confirm('Delete this address?')) return;

            $.ajax({
                url: '{{ url("addresses") }}/' + id,
                type: 'POST', // Using POST with _method DELETE for compatibility
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                headers: {
                    'Accept': 'application/json'
                },
                success: function(data) {
                    if (data.success) {
                        $('#address-' + id).fadeOut(300, function() { 
                            $(this).remove();
                            if ($('.address-card').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert(data.message || 'Failed to delete address');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('An error occurred while deleting.');
                }
            });
        }
    </script>
@endpush