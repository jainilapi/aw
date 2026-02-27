@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $isEdit ? 'Edit Currency' : 'Add New Currency' }}</h5>
                <a href="{{ route('currencies.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" 
                      action="{{ $isEdit ? route('currencies.update', $currency->id) : route('currencies.store') }}">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Currency Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" 
                                   value="{{ old('name', $currency->name ?? '') }}"
                                   placeholder="e.g., US Dollar" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="iso_code" class="form-label">ISO Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('iso_code') is-invalid @enderror" 
                                   id="iso_code" name="iso_code" 
                                   value="{{ old('iso_code', $currency->iso_code ?? '') }}"
                                   placeholder="e.g., USD" maxlength="3" 
                                   style="text-transform: uppercase;" required>
                            <small class="text-muted">3-letter ISO 4217 code</small>
                            @error('iso_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="symbol" class="form-label">Symbol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('symbol') is-invalid @enderror" 
                                   id="symbol" name="symbol" 
                                   value="{{ old('symbol', $currency->symbol ?? '') }}"
                                   placeholder="e.g., $" maxlength="10" required>
                            @error('symbol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="exchange_rate" class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                            <input type="number" step="0.000001" min="0.000001" max="999999.999999"
                                   class="form-control @error('exchange_rate') is-invalid @enderror" 
                                   id="exchange_rate" name="exchange_rate" 
                                   value="{{ old('exchange_rate', $currency->exchange_rate ?? '1') }}"
                                   {{ ($isEdit && $currency->is_base) ? 'readonly' : '' }} required>
                            <small class="text-muted">Rate relative to base currency</small>
                            @error('exchange_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="decimal_places" class="form-label">Decimal Places <span class="text-danger">*</span></label>
                            <select class="form-select @error('decimal_places') is-invalid @enderror" 
                                    id="decimal_places" name="decimal_places" required>
                                @for($i = 0; $i <= 4; $i++)
                                    <option value="{{ $i }}" 
                                            {{ old('decimal_places', $currency->decimal_places ?? 2) == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            @error('decimal_places')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="symbol_position" class="form-label">Symbol Position <span class="text-danger">*</span></label>
                            <select class="form-select @error('symbol_position') is-invalid @enderror" 
                                    id="symbol_position" name="symbol_position" required>
                                <option value="before" {{ old('symbol_position', $currency->symbol_position ?? 'before') == 'before' ? 'selected' : '' }}>
                                    Before amount (e.g., $100)
                                </option>
                                <option value="after" {{ old('symbol_position', $currency->symbol_position ?? 'before') == 'after' ? 'selected' : '' }}>
                                    After amount (e.g., 100€)
                                </option>
                            </select>
                            @error('symbol_position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" name="sort_order" 
                                   value="{{ old('sort_order', $currency->sort_order ?? 0) }}">
                            <small class="text-muted">Lower numbers appear first</small>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $currency->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active (available for selection)</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_base" value="0">
                                <input type="checkbox" class="form-check-input" id="is_base" name="is_base" 
                                       value="1" {{ old('is_base', $currency->is_base ?? false) ? 'checked' : '' }}
                                       {{ ($isEdit && $currency->is_base) ? 'disabled' : '' }}>
                                <label class="form-check-label" for="is_base">Base Currency</label>
                                @if($isEdit && $currency->is_base)
                                    <input type="hidden" name="is_base" value="1">
                                    <small class="text-muted d-block">Cannot unset base currency</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Preview</h6>
                            <p class="mb-0">
                                Sample price: <strong id="preview-price">$1,234.56</strong>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('currencies.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Update Currency' : 'Create Currency' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Update preview on field change
    function updatePreview() {
        var symbol = $('#symbol').val() || '$';
        var position = $('#symbol_position').val();
        var decimals = parseInt($('#decimal_places').val()) || 2;
        
        var amount = (1234.56).toFixed(decimals);
        // Add thousand separator
        amount = parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
        
        var formatted = position === 'before' ? symbol + amount : amount + symbol;
        $('#preview-price').text(formatted);
    }

    $('#symbol, #symbol_position, #decimal_places').on('change input', updatePreview);
    updatePreview();

    // Uppercase ISO code
    $('#iso_code').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // When base currency is checked, set exchange rate to 1
    $('#is_base').on('change', function() {
        if ($(this).is(':checked')) {
            $('#exchange_rate').val('1').prop('readonly', true);
        } else {
            $('#exchange_rate').prop('readonly', false);
        }
    });
});
</script>
@endpush
