<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title>{{ Helper::title() }} - @yield('title')</title>

	<link href="{{ asset('assets/css/style-dark.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/css/intel-tel.css') }}" rel="stylesheet">

	<style>
		.login-bg {
			position: fixed;
			inset: 0;
			z-index: -1;
		}

		.login-bg svg {
			width: 100%;
			height: 100%;
		}

		div.iti--inline-dropdown { min-width: 100%!important; }
		.iti__selected-flag { height: 40px!important; }
		.iti--show-flags { width: 100%!important; }

		label.error {
			color: #dc2626;
			font-size: 13px;
			margin-top: 6px;
			display: block;
		}

		#phone_number {
			font-family: "Hind Vadodara",-apple-system,BlinkMacSystemFont,"Segoe UI","Helvetica Neue",Arial,sans-serif;
			font-size: 15px;
		}

		/* ===== Login UI Enhancements ===== */
		body#login-svg {
			min-height: 100vh;
		}

		.login-card {
			border-radius: 32px;
			backdrop-filter: blur(10px);
			box-shadow: 0 25px 50px rgba(0,0,0,.25);
			animation: fadeUp .6s ease;
		}

		@keyframes fadeUp {
			from { opacity: 0; transform: translateY(20px); }
			to { opacity: 1; transform: translateY(0); }
		}

		.login-header {
			text-align: center;
			margin-bottom: 24px;
		}

		.login-header img {
			height: 72px;
			margin-bottom: 10px;
		}

		.login-header h2 {
			font-size: 20px;
			font-weight: 600;
			margin-bottom: 4px;
		}

		.login-header p {
			font-size: 13px;
			color: #9ca3af;
		}

		.form-control-lg {
			border-radius: 14px;
			height: 48px;
			font-size: 15px;
		}

		.form-control:focus {
			box-shadow: 0 0 0 2px rgba(25,121,195,.25);
		}

		.btn-primary {
			border-radius: 14px;
			height: 48px;
			font-weight: 600;
			letter-spacing: .3px;
			transition: all .25s ease;
		}

		.btn-primary:hover {
			transform: translateY(-1px);
			box-shadow: 0 8px 20px rgba(25,121,195,.35);
		}

		.form-check-label {
			font-size: 13px;
			color: #d1d5db;
		}
	</style>
</head>

<body class="theme-blue" id="login-svg">
	<div class="login-bg">
		<svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
			<path fill="#1e293b" d="M0,200 C240,300 480,100 720,150 960,200 1200,100 1440,200 L1440,0 L0,0 Z"/>
			<path fill="#0f172a" opacity="0.85"
				d="M0,350 C240,450 480,250 720,300 960,350 1200,250 1440,350 L1440,0 L0,0 Z"/>
		</svg>
	</div>

	<main class="main h-100 w-100">
		<div class="container h-100">
			<div class="row h-100">
				<div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="card login-card">
							<div class="card-body">
								<div class="m-sm-4">

									<div class="login-header">
										<img src="{{ Helper::logo() }}">
										<h2>Admin Sign In</h2>
									</div>

									<form method="POST" action="{{ route('admin.login') }}" id="login-form">
										@csrf

										<div class="mb-3">
											<label class="form-label mb-2">Phone Number</label>
											<input type="hidden" name="dial_code" id="country_dial_code">

											<input id="phone_number"
												name="phone_number"
												type="text"
												class="form-control phone_number form-control-lg @error('phone_number') is-invalid @enderror"
												placeholder="Enter your phone number"
												autocomplete="new-phone">

											@if ($errors->has('phone_number'))
												<span class="text-danger">{{ $errors->first('phone_number') }}</span>
											@endif
										</div>

										<div class="mb-3">
											<label class="form-label mb-2">Password</label>

											<input name="password"
												type="password"
												class="form-control form-control-lg @error('password') is-invalid @enderror"
												placeholder="Enter your password"
												autocomplete="new-password">

											@if ($errors->has('password'))
												<span class="text-danger">{{ $errors->first('password') }}</span>
											@elseif(session()->has('error'))
												<span class="text-danger">{{ session()->get('error') }}</span>
											@endif
										</div>

										<div class="mb-3">
											<div class="form-check">
												<input id="customControlInline"
													type="checkbox"
													class="form-check-input"
													name="remember"
													{{ old('remember') ? 'checked' : '' }}>
												<label class="form-check-label" for="customControlInline">
													Remember me
												</label>
											</div>
										</div>

										<div class="d-grid mt-4">
											<button type="submit" class="btn btn-lg btn-primary">
												Sign in
											</button>
										</div>
									</form>

								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/js/intel-tel.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>

	<script>
	$(document).ready(function () {

		const input = document.querySelector('#phone_number');
		const errorMap = ["Phone number is invalid.", "Invalid country code", "Too short", "Too long"];

		const iti = window.intlTelInput(input, {
			initialCountry: "ag",
			separateDialCode: true,
			nationalMode: false,
			preferredCountries: @json(\App\Models\Country::select('iso2')->pluck('iso2')->toArray()),
			utilsScript: "{{ asset('assets/js/intel-tel-2.min.js') }}"
		});

		$.validator.addMethod('inttel', function (value) {
			return value.trim() !== '';
		}, function () {
			return errorMap[iti.getValidationError()] || errorMap[0];
		});

		input.addEventListener("countrychange", () => {
			if (iti.isValidNumber()) {
				$('#country_dial_code').val(iti.s.dialCode);
			}
		});

		input.addEventListener('keyup', () => {
			if (iti.isValidNumber()) {
				$('#country_dial_code').val(iti.s.dialCode);
			}
		});

		$('#login-form').validate({
			rules: {
				phone_number: { required: true, inttel: true },
				password: { required: true }
			},
			messages: {
				phone_number: { required: 'Phone number is required.' },
				password: { required: 'Password is required.' }
			},
			errorPlacement: function(error, element) {
				error.appendTo(element.parent("div"));
			},
			submitHandler: function (form) {
				$('#country_dial_code').val(iti.s.dialCode);
				form.submit();
			}
		});

	});
	</script>

</body>
</html>
