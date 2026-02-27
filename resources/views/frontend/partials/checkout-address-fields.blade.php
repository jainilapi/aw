{{-- Checkout Address Fields Partial --}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="address_line_1" id="guest_address_line_1" required>
    </div>
    <div class="col-12">
        <label class="form-label">Address Line 2</label>
        <input type="text" class="form-control" name="address_line_2" id="guest_address_line_2">
    </div>
    <div class="col-md-6">
        <label class="form-label">Country <span class="text-danger">*</span></label>
        <select class="form-select" name="country_id" id="guest_country_id" required>
            <option value="">Select Country</option>
            @foreach(\App\Models\Country::orderBy('name')->get() as $country)
                <option value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">State <span class="text-danger">*</span></label>
        <select class="form-select" name="state_id" id="guest_state_id" required></select>
    </div>
    <div class="col-md-6">
        <label class="form-label">City</label>
        <select class="form-select" name="city_id" id="guest_city_id"></select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Zipcode <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="zipcode" id="guest_zipcode" required>
    </div>
</div>