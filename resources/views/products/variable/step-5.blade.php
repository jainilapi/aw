@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@section('product-content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Step 5: Variant Inventory & Stock Sources</h5>
    </div>
</div>

<div class="accordion" id="variantInventoryAccordion">
    @foreach($product->variants as $vIndex => $variant)
    <div class="accordion-item mb-3 border shadow-sm">
        <h2 class="accordion-header">
            <button class="accordion-button {{ $vIndex == 0 ? '' : 'collapsed' }} bg-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#inv-v-{{ $variant->id }}">
                <img src="{{ $variant->images->where('position', 0)->first() ? asset('storage/'.$variant->images->where('position', 0)->first()->image_path) : asset('assets/img/placeholder.png') }}" 
                     class="rounded me-3" style="width: 35px; height: 35px; object-fit: contain;"
                     onerror="this.onerror=null; this.src='{{ asset('no-image-found.jpg') }}';">
                {{ $variant->name }} <span class="badge bg-light text-dark border ms-2 small">{{ $variant->sku }}</span>
            </button>
        </h2>
        <div id="inv-v-{{ $variant->id }}" class="accordion-collapse collapse {{ $vIndex == 0 ? 'show' : '' }}" data-bs-parent="#variantInventoryAccordion">
            <div class="accordion-body bg-light">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Stock Sources</h6>
                    <button type="button" class="btn btn-dark btn-sm add-inventory-row" data-variant-id="{{ $variant->id }}">+ Add Stock Source</button>
                </div>

                <div class="inventory-container" id="container-v-{{ $variant->id }}">
                    @php
                        $iterbleVar = \App\Models\AwSupplierWarehouseProduct::where('product_id', $product->id)->where('variant_id', $variant->id)->get();
                    @endphp
                    @foreach ($iterbleVar as $index => $inv)
                        <div class="card mb-3 border shadow-none inventory-card">
                            <div class="card-header bg-white d-flex justify-content-between py-2">
                                <span class="fw-bold text-muted small mt-1">Source #{{ $index + 1 }}</span>
                                <button type="button" class="btn btn-link text-danger p-0 remove-inventory-card"><i class="fa fa-trash"></i></button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Supplier</label>
                                        <select name="variant_inventory[{{ $variant->id }}][{{ $index }}][supplier_id]" class="form-select select2">
                                            @foreach ($suppliers as $s)
                                                <option value="{{ $s->id }}" {{ $inv->supplier_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Warehouse *</label>
                                        <select name="variant_inventory[{{ $variant->id }}][{{ $index }}][warehouse_id]" class="form-select select2">
                                            @foreach ($warehouses as $w)
                                                <option value="{{ $w->id }}" {{ $inv->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Cost Price ($)</label>
                                        <input type="number" name="variant_inventory[{{ $variant->id }}][{{ $index }}][cost_price]" class="form-control" step="0.01" value="{{ $inv->cost_price }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Quantity on Hand *</label>
                                        <input type="number" name="variant_inventory[{{ $variant->id }}][{{ $index }}][quantity]" class="form-control qty-input" value="{{ $inv->quantity }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Reorder Level</label>
                                        <input type="number" name="variant_inventory[{{ $variant->id }}][{{ $index }}][reorder_level]" class="form-control" value="{{ $inv->reorder_level }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Max Stock Level</label>
                                        <input type="number" name="variant_inventory[{{ $variant->id }}][{{ $index }}][max_stock]" class="form-control" value="{{ $inv->max_stock }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Stock Unit</label>
                                        <select name="variant_inventory[{{ $variant->id }}][{{ $index }}][unit_id]" class="form-select">
                                            @foreach ($variant->units as $vu)
                                                <option value="{{ $vu->unit_id }}" {{ $inv->unit_id == $vu->unit_id ? 'selected' : '' }}>{{ $vu->unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Notes / Handling Instructions</label>
                                        <textarea name="variant_inventory[{{ $variant->id }}][{{ $index }}][notes]" class="form-control" rows="1" placeholder="Notes...">{{ $inv->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between py-2">
                                <small class="text-muted">
                                    <i class="fa fa-clock"></i> Last updated: <span class="update-ts">{{ $inv->updated_at->format('M d, Y') }}</span>
                                </small>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary adjust-stock-btn" data-id="{{ $inv->id }}" data-qty="{{ $inv->quantity }}" data-unit="{{ $inv->unit->name }}"> <i class="fa fa-plus-minus"></i> Adjust</button>
                                    <button type="button" class="btn btn-sm btn-outline-dark view-history-btn" data-warehouse="{{ $inv->warehouse_id }}" data-product="{{ $product->id }}" data-variant="{{ $variant->id }}"> <i class="fa fa-history"></i> History</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fa fa-history me-2"></i>Inventory Movement History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="small text-uppercase">
                                <th>Date</th>
                                <th>Reason</th>
                                <th>Change</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody id="history-content">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Stock Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <div class="alert alert-info p-2  small">This will create a movement log entry automatically.</div>
                <input type="hidden" id="adj-mapping-id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Stock: <span id="adj-current-val" class="badge bg-secondary">0</span></label>
                    <div class="input-group">
                        <select id="adj-type" class="form-select" style="max-width: 120px;">
                            <option value="add">Add (+)</option>
                            <option value="subtract">Deduct (-)</option>
                        </select>
                        <input type="number" id="adj-value" class="form-control" placeholder="Enter quantity">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Adjustment Reason</label>
                    <textarea id="adj-note" class="form-control" rows="2" placeholder="e.g., Damaged, Found in warehouse..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" id="save-adjustment">Apply Adjustment</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('product-js')
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });

    const variantUnitsMap = @json($product->variants->keyBy('id')->map->units);
    console.log(variantUnitsMap);
    

    $('.add-inventory-row').on('click', function() {
        const vId = $(this).data('variant-id');
        const container = $('#container-v-' + vId);
        const index = container.find('.inventory-card').length;
        const vUnits = [];

        const html = `
            <div class="card mb-3 border shadow-none inventory-card animate__animated animate__fadeIn">
                <div class="card-header bg-white d-flex justify-content-between py-2">
                    <span class="fw-bold text-primary small mt-1">New Stock Source</span>
                    <button type="button" class="btn btn-link text-danger p-0 remove-inventory-card"><i class="fa fa-trash"></i></button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                      <div class="col-md-4">
                          <label class="form-label fw-bold">Supplier</label>
                          <select name="variant_inventory[${vId}][${index}][supplier_id]" class="form-select select2-dynamic" required>
                              <option value="">Select Supplier</option>
                              @foreach ($suppliers as $s)
                                  <option value="{{ $s->id }}">{{ $s->name }}</option>
                              @endforeach
                          </select>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label fw-bold">Warehouse / Location *</label>
                          <select name="variant_inventory[${vId}][${index}][warehouse_id]" class="form-select select2-dynamic" required>
                              <option value="">Select Warehouse</option>
                              @foreach ($warehouses as $w)
                                  <option value="{{ $w->id }}">{{ $w->name }}</option>
                              @endforeach
                          </select>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label fw-bold">Cost Price ($)</label>
                          <div class="input-group">
                              <span class="input-group-text">$</span>
                              <input type="number" name="variant_inventory[${vId}][${index}][cost_price]" class="form-control" step="0.01" placeholder="0.00" required>
                          </div>
                      </div>

                      <div class="col-md-3">
                          <label class="form-label fw-bold">Quantity on Hand *</label>
                          <div class="input-group">
                              <input type="number" name="variant_inventory[${vId}][${index}][quantity]" class="form-control" placeholder="0" required>
                              <span class="input-group-text bg-light text-muted">Units</span>
                          </div>
                      </div>
                      <div class="col-md-3">
                          <label class="form-label fw-bold">Reorder Level</label>
                          <input type="number" name="variant_inventory[${vId}][${index}][reorder_level]" class="form-control" value="0">
                      </div>
                      <div class="col-md-3">
                          <label class="form-label fw-bold">Max Stock Level</label>
                          <input type="number" name="variant_inventory[${vId}][${index}][max_stock]" class="form-control" value="0">
                      </div>
                      <div class="col-md-3">
                          <label class="form-label fw-bold">Stock Unit</label>
                          <select name="variant_inventory[${vId}][${index}][unit_id]" class="form-select select2-dynamic" required>
                              @foreach ($allUnits as $pu)
                                  <option value="{{ $pu->unit_id }}">{{ $pu->unit->name }}</option>
                              @endforeach
                          </select>
                      </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes / Handling Instructions</label>
                            <textarea name="variant_inventory[${vId}][${index}][notes]" class="form-control" rows="1" placeholder="Notes..."></textarea>
                        </div>
                    </div>
                </div>
            </div>`;

        container.append(html);
        container.find('.select2-dynamic').select2({ width: '100%' });
    });

    $(document).on('click', '.view-history-btn', function() {
        const btn = $(this);
        const warehouseId = btn.data('warehouse');
        const productId = btn.data('product');
        const variantId = btn.data('variant');

        $('#history-content').html('<tr><td colspan="4" class="text-center py-4"><i class="fa fa-spinner fa-spin me-2"></i>Fetching history...</td></tr>');
        $('#historyModal').modal('show');

        $.get(`/admin/inventory/history/${productId}/${warehouseId}?variant_id=${variantId}`, function(data) {
            let html = '';
            if (data.length > 0) {
                data.forEach(move => {
                    const badgeClass = move.quantity_change > 0 ? 'bg-success' : 'bg-danger';
                    const date = new Date(move.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    html += `
                        <tr>
                            <td class="small">${date}</td>
                            <td class="text-capitalize small">${move.reason}</td>
                            <td><span class="badge ${badgeClass}">${move.quantity_change > 0 ? '+' : ''}${move.quantity_change}</span></td>
                            <td class="small text-muted">${move.reference || '-'}</td>
                        </tr>`;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center py-4 text-muted">No movement history found for this source.</td></tr>';
            }
            $('#history-content').html(html);
        }).fail(function() {
            $('#history-content').html('<tr><td colspan="4" class="text-center text-danger py-4">Failed to load history.</td></tr>');
        });
    });

    $(document).on('click', '.adjust-stock-btn', function() {
        const data = $(this).data();
        $('#adj-mapping-id').val(data.id);
        $('#adj-current-val').text(data.qty + ' ' + data.unit);
        $('#adj-value').val('');
        $('#adj-note').val('');
        $('#adjustStockModal').modal('show');
    });

    $(document).on('click', '#save-adjustment', function() {
        const btn = $(this);
        const mappingId = $('#adj-mapping-id').val();
        const adjType = $('#adj-type').val();
        const rawValue = parseFloat($('#adj-value').val()) || 0;
        const finalAdj = (adjType === 'add') ? rawValue : -rawValue;
        const reason = $('#adj-note').val();

        if (rawValue <= 0) {
            alert("Please enter a valid quantity.");
            return;
        }

        if (!reason) {
            alert("Please provide a reason for the adjustment.");
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Saving...');

        $.ajax({
            url: "{{ route('inventory.adjust') }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                mapping_id: mappingId,
                adjustment_qty: finalAdj,
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    const cardBtn = $(`.adjust-stock-btn[data-id="${mappingId}"]`);
                    const card = cardBtn.closest('.inventory-card');
                    
                    card.find('.qty-input').val(response.new_qty);
                    
                    cardBtn.attr('data-qty', response.new_qty);
                    
                    card.find('.update-ts').text('Just now');

                    $('#adjustStockModal').modal('hide');
                    
                    alert("Stock updated successfully to " + response.new_qty);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : "Adjustment failed.";
                alert("Error: " + msg);
            },
            complete: function() {
                btn.prop('disabled', false).text('Commit Adjustment');
            }
        });
    });

    $(document).on('click', '.remove-inventory-card', function() {
        if (confirm('Remove this source?')) $(this).closest('.inventory-card').remove();
    });
});
</script>
@endpush