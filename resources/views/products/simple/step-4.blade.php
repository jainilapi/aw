@extends('products.layout', ['step' => $step, 'type' => $type, 'product' => $product])

@push('product-css')
@endpush

@section('product-content')
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Supplier & Inventory Mapping</h5>
            <button type="button" class="btn btn-dark btn-sm" id="add-inventory-row">+ Add Stock Source</button>
        </div>
        <div class="card-body bg-light">
            <div id="inventory-container">
                @foreach ($existingInventory as $index => $inv)
                    <div class="card mb-4 border shadow-sm inventory-card">
                        <div class="card-header bg-white d-flex justify-content-between">
                            <span class="fw-bold text-uppercase text-muted" style="font-size: 0.8rem;">Stock Source
                                #{{ $index + 1 }}</span>
                            <button type="button" class="btn btn-link text-danger p-0 remove-inventory-card"><i
                                    class="fa fa-trash"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Supplier</label>
                                    <select name="inventory[{{ $index }}][supplier_id]" class="form-select select2">
                                        @foreach ($suppliers as $s)
                                            <option value="{{ $s->id }}"
                                                {{ $inv->supplier_id == $s->id ? 'selected' : '' }}>{{ $s->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Warehouse / Location *</label>
                                    <select name="inventory[{{ $index }}][warehouse_id]"
                                        class="form-select select2 warehouse-selector">
                                        @foreach ($warehouses as $w)
                                            <option value="{{ $w->id }}"
                                                {{ $inv->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cost Price ($)</label>
                                    <input type="number" name="inventory[{{ $index }}][cost_price]"
                                        class="form-control" step="0.01" value="{{ $inv->cost_price }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Quantity on Hand *</label>
                                    <div class="input-group">
                                        <input type="number" name="inventory[{{ $index }}][quantity]"
                                            class="form-control" value="{{ $inv->quantity }}">
                                        <span class="input-group-text bg-light text-muted">{{ $inv->unit->name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Reorder Level</label>
                                    <input type="number" name="inventory[{{ $index }}][reorder_level]"
                                        class="form-control" value="{{ $inv->reorder_level ?? 0 }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Max Stock Level</label>
                                    <input type="number" name="inventory[{{ $index }}][max_stock]"
                                        class="form-control" value="{{ $inv->max_stock ?? 0 }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Stock Unit</label>
                                    <select name="inventory[{{ $index }}][unit_id]" class="form-select">
                                        @foreach ($allUnits as $pu)
                                            <option value="{{ $pu->unit_id }}"
                                                {{ $inv->unit_id == $pu->unit_id ? 'selected' : '' }}>
                                                {{ $pu->unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Notes / Handling Instructions</label>
                                    <textarea name="inventory[{{ $index }}][notes]" class="form-control" rows="2"
                                        placeholder="Enter special instructions...">{{ $inv->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                          <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                              <div class="text-muted small">
                                  <i class="fa fa-clock"></i> Last updated: <span class="update-ts">{{ $inv->updated_at->format('M d, Y') }}</span>
                              </div>
                              <div class="btn-group">
                                  <button type="button" class="btn btn-sm btn-outline-primary adjust-stock-btn" 
                                          data-id="{{ $inv->id }}" 
                                          data-qty="{{ $inv->quantity }}"
                                          data-unit="{{ $inv->unit->name }}">
                                      <i class="fa fa-plus-minus"></i> Adjust Stock
                                  </button>
                                  <button type="button" class="btn btn-sm btn-outline-dark view-history-btn" 
                                          data-warehouse="{{ $inv->warehouse_id }}" 
                                          data-product="{{ $product->id }}">
                                      <i class="fa fa-history"></i> History
                                  </button>
                              </div>
                          </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fa fa-history me-2"></i>Inventory Movement History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
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

    <div class="modal fade" id="adjustStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Stock Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info p-2  small">This will create a movement log entry automatically.</div>
                    <input type="hidden" id="adj-mapping-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Stock: <span id="adj-current-val">0</span></label>
                        <div class="input-group">
                            <select id="adj-type" class="form-select" style="max-width: 100px;">
                                <option value="add">Add</option>
                                <option value="subtract">Deduct</option>
                            </select>
                            <input type="number" id="adj-value" class="form-control" placeholder="Quantity">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Adjustment Reason</label>
                        <textarea id="adj-note" class="form-control" rows="2"
                            placeholder="e.g., Damaged items, stock count correction..."></textarea>
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
            $('.select2').select2();

            $(document).on('click', '.view-history-btn', function() {
                const warehouseId = $(this).data('warehouse');
                const productId = $(this).data('product');

                $('#history-content').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
                $('#historyModal').modal('show');

                $.get(`/admin/inventory/history/${productId}/${warehouseId}`, function(data) {
                    let html = '';
                    data.forEach(move => {
                        const badgeClass = move.quantity_change > 0 ? 'bg-success' :
                            'bg-danger';
                        html += `
                  <tr>
                      <td>${new Date(move.created_at).toLocaleDateString()}</td>
                      <td class="text-capitalize">${move.reason}</td>
                      <td><span class="badge ${badgeClass}">${move.quantity_change > 0 ? '+' : ''}${move.quantity_change}</span></td>
                      <td>${move.reference || '-'}</td>
                  </tr>`;
                    });
                    $('#history-content').html(html ||
                        '<tr><td colspan="4" class="text-center">No history found.</td></tr>');
                });
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

                btn.prop('disabled', true).text('Processing...');

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
                            const card = $(`.adjust-stock-btn[data-id="${mappingId}"]`).closest('.inventory-card');
                            card.find('.qty-input').val(response.new_qty);
                            
                            $(`.adjust-stock-btn[data-id="${mappingId}"]`).attr('data-qty', response.new_qty);
                            
                            $('#adjustStockModal').modal('hide');
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert("Error: " + xhr.responseJSON.message);
                    },
                    complete: function() {
                        btn.prop('disabled', false).text('Apply Adjustment');
                    }
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

            $('#add-inventory-row').on('click', function() {
                let index = $('.inventory-card').length;

                let html = `
          <div class="card mb-4 border shadow-sm inventory-card animate__animated animate__fadeIn">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-uppercase text-primary" style="font-size: 0.8rem;">New Stock Source</span>
                  <button type="button" class="btn btn-link text-danger p-0 remove-inventory-card">
                      <i class="fa fa-trash"></i>
                  </button>
              </div>
              <div class="card-body">
                  <div class="row g-3">
                      <div class="col-md-4">
                          <label class="form-label fw-bold">Supplier</label>
                          <select name="inventory[${index}][supplier_id]" class="form-select select2-dynamic" required>
                              <option value="">Select Supplier</option>
                              @foreach ($suppliers as $s)
                                  <option value="{{ $s->id }}">{{ $s->name }}</option>
                              @endforeach
                          </select>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label fw-bold">Warehouse / Location *</label>
                          <select name="inventory[${index}][warehouse_id]" class="form-select select2-dynamic" required>
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
                              <input type="number" name="inventory[${index}][cost_price]" class="form-control" step="0.01" placeholder="0.00" required>
                          </div>
                      </div>

                      <div class="col-md-3">
                          <label class="form-label fw-bold">Quantity on Hand *</label>
                          <div class="input-group">
                              <input type="number" name="inventory[${index}][quantity]" class="form-control" placeholder="0" required>
                              <span class="input-group-text bg-light text-muted">Units</span>
                          </div>
                      </div>
                      <div class="col-md-3">
                          <label class="form-label fw-bold">Reorder Level</label>
                          <input type="number" name="inventory[${index}][reorder_level]" class="form-control" value="0">
                      </div>
                      <div class="col-md-3">
                          <label class="form-label fw-bold">Max Stock Level</label>
                          <input type="number" name="inventory[${index}][max_stock]" class="form-control" value="0">
                      </div>
                      <div class="col-md-3">
                          <label class="form-label fw-bold">Stock Unit</label>
                          <select name="inventory[${index}][unit_id]" class="form-select select2-dynamic" required>
                              @foreach ($allUnits as $pu)
                                  <option value="{{ $pu->unit_id }}">{{ $pu->unit->name }}</option>
                              @endforeach
                          </select>
                      </div>

                      <div class="col-12">
                          <label class="form-label fw-bold">Notes / Handling Instructions</label>
                          <textarea name="inventory[${index}][notes]" class="form-control" rows="2" placeholder="Enter special instructions for this stock..."></textarea>
                      </div>
                  </div>
              </div>
              <div class="card-footer bg-white text-end">
                  <small class="text-muted">Save product to view history for this source.</small>
              </div>
          </div>`;

                $('#inventory-container').append(html);

                $('.select2-dynamic').select2({
                    width: '100%'
                });
            });

            $(document).on('click', '.remove-inventory-card', function() {
                if (confirm('Are you sure you want to remove this stock source?')) {
                    $(this).closest('.inventory-card').remove();
                }
            });
        });
    </script>
@endpush
