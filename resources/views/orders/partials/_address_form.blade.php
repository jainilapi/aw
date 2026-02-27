<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
        <input type="text" name="{{ $prefix }}_address_line_1" class="form-control" required
            value="{{ old($prefix . '_address_line_1', $order->{$prefix . '_address_line_1'} ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Address Line 2</label>
        <input type="text" name="{{ $prefix }}_address_line_2" class="form-control"
            value="{{ old($prefix . '_address_line_2', $order->{$prefix . '_address_line_2'} ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Country <span class="text-danger">*</span></label>
        <select name="{{ $prefix }}_country_id" class="form-control select2" required>
            <option value="">Select Country</option>
            @foreach($countries ?? [] as $country)
                <option value="{{ $country->id }}" {{ old($prefix . '_country_id', $order->{$prefix . '_country_id'} ?? '') == $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">State <span class="text-danger">*</span></label>
        <select name="{{ $prefix }}_state_id" class="form-control select2" required>
            <option value="">Select State</option>
            @if(isset($order) && $order->{$prefix . 'State'})
                <option value="{{ $order->{$prefix . '_state_id'} }}" selected>
                    {{ $order->{$prefix . 'State'}->name }}
                </option>
            @endif
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">City <span class="text-danger">*</span></label>
        <select name="{{ $prefix }}_city_id" class="form-control select2" required>
            <option value="">Select City</option>
            @if(isset($order) && $order->{$prefix . 'City'})
                <option value="{{ $order->{$prefix . '_city_id'} }}" selected>
                    {{ $order->{$prefix . 'City'}->name }}
                </option>
            @endif
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Zip Code <span class="text-danger">*</span></label>
        <input type="text" name="{{ $prefix }}_zipcode" class="form-control" required
            value="{{ old($prefix . '_zipcode', $order->{$prefix . '_zipcode'} ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $recipientLabel ?? 'Name' }} <span class="text-danger">*</span></label>
        @php
            $nameField = $prefix === 'shipping' ? 'recipient_name' : 'billing_name';
        @endphp
        <input type="text" name="{{ $nameField }}" class="form-control" required
            value="{{ old($nameField, $order->{$nameField} ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Contact Number <span class="text-danger">*</span></label>
        @php
            $phoneField = $prefix === 'shipping' ? 'recipient_contact_number' : 'billing_contact_number';
        @endphp
        <input type="text" name="{{ $phoneField }}" class="form-control" required
            value="{{ old($phoneField, $order->{$phoneField} ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Email</label>
        @php
            $emailField = $prefix === 'shipping' ? 'recipient_email' : 'billing_email';
        @endphp
        <input type="email" name="{{ $emailField }}" class="form-control"
            value="{{ old($emailField, $order->{$emailField} ?? '') }}">
    </div>
</div>