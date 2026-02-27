@extends('frontend.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
    <style>
        .profile-page {
            padding: 40px 0;
        }

        .page-header {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #203A72;
            margin: 0;
        }

        .profile-card {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 32px;
        }

        .avatar-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #203A72 0%, #1a2d5a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 48px;
            font-weight: 600;
            color: #fff;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 600;
            color: #203A72;
            margin: 0;
        }

        .profile-email {
            font-size: 14px;
            color: #666;
        }

        .form-section {
            margin-bottom: 24px;
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #F5FAFF;
        }

        .form-label {
            font-weight: 500;
            color: #203A72;
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: #203A72;
            box-shadow: 0 0 0 3px rgba(32, 58, 114, 0.1);
        }

        .form-hint {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            background: #203A72;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background: #1a2d5a;
        }

        .btn-save:disabled {
            background: #9CADC0;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .iti {
            width: 100%;
        }

        .iti__flag-container {
            z-index: 2;
        }

        .error-text {
            color: #D30606;
            font-size: 12px;
            margin-top: 4px;
        }

        .email-verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            margin-left: 8px;
        }

        .badge-verified {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-unverified {
            background: #FFF3E0;
            color: #E65100;
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
                        <li class="active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="profile-page">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <div class="avatar-circle">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <h2 class="profile-name">{{ $customer->name }}</h2>
                            <p class="profile-email">
                                {{ $customer->email }}
                                @if($customer->email_verified_at)
                                    <span class="email-verified-badge badge-verified">
                                        <i class="bi bi-check-circle"></i> Verified
                                    </span>
                                @else
                                    <span class="email-verified-badge badge-unverified">
                                        <i class="bi bi-exclamation-circle"></i> Not Verified
                                    </span>
                                @endif
                            </p>
                        </div>

                        <form id="profileForm" action="{{ route('customer.profile.update') }}" method="POST">
                            @csrf

                            <div class="form-section">
                                <h4 class="form-section-title">Personal Information</h4>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            name="name" id="name" value="{{ old('name', $customer->name) }}" required>
                                        @error('name')
                                            <span class="error-text">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h4 class="form-section-title">Contact Information</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            name="email" id="email" value="{{ old('email', $customer->email) }}" required>
                                        <span class="form-hint">Changing email will require re-verification</span>
                                        @error('email')
                                            <span class="error-text">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            name="phone" id="phone" value="{{ old('phone', $customer->phone) }}">
                                        @error('phone')
                                            <span class="error-text">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn-save" id="saveBtn">
                                    <i class="bi bi-check-lg"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script>
        // International telephone input
        const phoneInput = document.querySelector('#phone');
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: 'us',
            separateDialCode: true,
            utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
        });

        // Form validation
        $(document).ready(function () {
            $('#profileForm').validate({
                rules: {
                    name: {
                        required: true,
                        maxlength: 255
                    },
                    email: {
                        required: true,
                        email: true,
                        maxlength: 255
                    },
                    phone: {
                        maxlength: 30
                    }
                },
                messages: {
                    name: {
                        required: 'Please enter your name'
                    },
                    email: {
                        required: 'Please enter your email',
                        email: 'Please enter a valid email address'
                    }
                },
                errorClass: 'error-text',
                errorElement: 'span',
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                },
                submitHandler: function (form) {
                    // Update phone with country code
                    const fullPhone = iti.getNumber();
                    phoneInput.value = fullPhone;

                    $('#saveBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');
                    form.submit();
                }
            });
        });
    </script>
@endpush