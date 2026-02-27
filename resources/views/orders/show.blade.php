@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@push('css')
    <style>
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .order-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .order-header .badges {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .info-card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            height: 100%;
        }

        .info-card .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }

        .info-card .card-header h6 {
            margin: 0;
            font-weight: 600;
        }

        .info-card .card-body {
            padding: 1rem 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: #666;
            font-size: 0.875rem;
        }

        .info-row .value {
            font-weight: 500;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #666;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            background: transparent;
        }

        .nav-tabs .nav-link:hover {
            color: #667eea;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #667eea;
        }

        .timeline-item.success::before {
            background: #28a745;
            box-shadow: 0 0 0 2px #28a745;
        }

        .timeline-item.warning::before {
            background: #ffc107;
            box-shadow: 0 0 0 2px #ffc107;
        }

        .timeline-item.danger::before {
            background: #dc3545;
            box-shadow: 0 0 0 2px #dc3545;
        }

        .timeline-item .time {
            font-size: 0.75rem;
            color: #999;
        }

        .timeline-item .content {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }

        .items-table th {
            background: #f8f9fa;
            font-weight: 500;
        }

        .items-table td {
            vertical-align: middle;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .status-change-form {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <!-- Order Header -->
    <div class="order-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h4>Order #{{ $order->order_number }}</h4>
                <div class="badges">
                    {!! $order->status_badge !!}
                    {!! $order->payment_status_badge !!}
                    {!! $order->source_badge !!}
                </div>
            </div>
            <div class="action-buttons">
                @if($order->isEditable())
                    <a href="{{ route('orders.edit', $order) }}" class="btn btn-light btn-sm">
                        <i class="fa fa-edit me-1"></i>Edit Order
                    </a>
                @endif
                <button class="btn btn-light btn-sm" onclick="window.print()">
                    <i class="fa fa-print me-1"></i>Print
                </button>
                <a href="{{ route('orders.index') }}" class="btn btn-light btn-sm">
                    <i class="fa fa-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#itemsTab">
                        <i class="fa fa-shopping-cart me-1"></i>Items ({{ $order->items->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#timelineTab">
                        <i class="fa fa-history me-1"></i>Timeline
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Items Tab -->
                <div class="tab-pane fade show active" id="itemsTab">
                    <div class="info-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6><i class="fa fa-box me-2"></i>Order Items</h6>
                            @if($order->isEditable())
                                <button class="btn btn-success btn-sm" id="addItemBtn">
                                    <i class="fa fa-plus me-1"></i>Add Item
                                </button>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <table class="table items-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Unit</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Tax Slab</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Total</th>
                                        @if($order->isEditable())
                                            <th width="100">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <strong>{{ $item->product_name }}</strong>
                                                @if($item->is_free_gift)
                                                    <span class="badge bg-success ms-1">Free Gift</span>
                                                @endif
                                                @if($item->variant)
                                                    <br><small class="text-muted">{{ $item->variant->name ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td><code>{{ $item->sku }}</code></td>
                                            <td>{{ $item->unit?->name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ currency_format($item->unit_price) }}</td>
                                            <td class="text-end">
                                                @if($item->tax_slab_id)
                                                    @php $ts = $taxSlabs->firstWhere('id', $item->tax_slab_id); @endphp
                                                    @if($ts)
                                                        <span class="badge bg-info text-dark">{{ $ts->name }} ({{ $ts->tax_percentage }}%)</span>
                                                    @else
                                                        <span class="text-muted small">ID: {{ $item->tax_slab_id }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-danger">-{{ currency_format($item->discount_amount) }}
                                            </td>
                                            <td class="text-end"><strong>{{ currency_format($item->total) }}</strong></td>
                                            @if($order->isEditable())
                                                <td>
                                                    <button class="btn btn-outline-primary btn-sm edit-item"
                                                        data-item='@json($item)' title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm delete-item"
                                                        data-item-id="{{ $item->id }}" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="{{ $order->isEditable() ? 7 : 6 }}" class="text-end">
                                            <strong>Subtotal:</strong>
                                        </td>
                                        <td class="text-end">{{ currency_format($order->sub_total) }}</td>
                                        @if($order->isEditable())
                                        <td></td> @endif
                                    </tr>
                                    <tr>
                                        <td colspan="{{ $order->isEditable() ? 7 : 6 }}" class="text-end">Tax:</td>
                                        <td class="text-end">{{ currency_format($order->tax_total) }}</td>
                                        @if($order->isEditable())
                                        <td></td> @endif
                                    </tr>
                                    <tr>
                                        <td colspan="{{ $order->isEditable() ? 7 : 6 }}" class="text-end">Shipping:</td>
                                        <td class="text-end">{{ currency_format($order->shipping_total) }}</td>
                                        @if($order->isEditable())
                                        <td></td> @endif
                                    </tr>
                                    <tr>
                                        <td colspan="{{ $order->isEditable() ? 7 : 6 }}" class="text-end text-danger">
                                            Discount:</td>
                                        <td class="text-end text-danger">-{{ currency_format($order->discount_total) }}
                                        </td>
                                        @if($order->isEditable())
                                        <td></td> @endif
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="{{ $order->isEditable() ? 7 : 6 }}" class="text-end"><strong>Grand
                                                Total:</strong></td>
                                        <td class="text-end"><strong>{{ currency_format($order->grand_total) }}</strong>
                                        </td>
                                        @if($order->isEditable())
                                        <td></td> @endif
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Timeline Tab -->
                <div class="tab-pane fade" id="timelineTab">
                    <div class="info-card">
                        <div class="card-header">
                            <h6><i class="fa fa-history me-2"></i>Status History</h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @forelse($order->statusHistory as $history)
                                    @php
                                        $timelineClass = match ($history->status) {
                                            'delivered' => 'success',
                                            'cancelled', 'rejected' => 'danger',
                                            'pending' => 'warning',
                                            default => ''
                                        };
                                    @endphp
                                    <div class="timeline-item {{ $timelineClass }}">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ ucfirst($history->status) }}</strong>
                                            <span class="time">{{ $history->created_at->format('M d, Y H:i') }}</span>
                                        </div>
                                        @if($history->previous_status)
                                            <small class="text-muted">Changed from {{ ucfirst($history->previous_status) }}</small>
                                        @endif
                                        @if($history->comment)
                                            <div class="content">{{ $history->comment }}</div>
                                        @endif
                                        @if($history->user)
                                            <small class="text-muted">By: {{ $history->user->name }}</small>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted">No status history available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Status Change -->
            @if(count($allowedStatuses) > 0)
                <div class="info-card">
                    <div class="card-header">
                        <h6><i class="fa fa-exchange-alt me-2"></i>Update Status</h6>
                    </div>
                    <div class="card-body">
                        <form id="statusChangeForm">
                            <div class="mb-3">
                                <label class="form-label">New Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select status...</option>
                                    @foreach($allowedStatuses as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comment (Optional)</label>
                                <textarea name="comment" class="form-control" rows="2"
                                    placeholder="Add a note about this change..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-check me-1"></i>Update Status
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Customer Info -->
            <div class="info-card">
                <div class="card-header">
                    <h6><i class="fa fa-user me-2"></i>Customer</h6>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Name</span>
                        <span class="value">{{ $order->customer?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value">{{ $order->customer?->email ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value">{{ $order->customer?->phone ?? 'N/A' }}</span>
                    </div>
                    @if($order->createdBy)
                        <hr>
                        <div class="info-row">
                            <span class="label">Created By</span>
                            <span class="value">{{ $order->createdBy->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="info-card">
                <div class="card-header">
                    <h6><i class="fa fa-truck me-2"></i>Shipping Address</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->recipient_name }}</strong></p>
                    <p class="mb-1">{{ $order->formatted_shipping_address }}</p>
                    <p class="mb-1"><i class="fa fa-phone me-1"></i>{{ $order->recipient_contact_number }}</p>
                    @if($order->recipient_email)
                        <p class="mb-0"><i class="fa fa-envelope me-1"></i>{{ $order->recipient_email }}</p>
                    @endif
                </div>
            </div>

            <!-- Billing Address -->
            <div class="info-card">
                <div class="card-header">
                    <h6><i class="fa fa-file-invoice me-2"></i>Billing Address</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->billing_name }}</strong></p>
                    <p class="mb-1">{{ $order->formatted_billing_address }}</p>
                    <p class="mb-1"><i class="fa fa-phone me-1"></i>{{ $order->billing_contact_number }}</p>
                    @if($order->billing_email)
                        <p class="mb-0"><i class="fa fa-envelope me-1"></i>{{ $order->billing_email }}</p>
                    @endif
                </div>
            </div>

            <!-- Payment Info -->
            <div class="info-card">
                <div class="card-header">
                    <h6><i class="fa fa-credit-card me-2"></i>Payment</h6>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Method</span>
                        <span class="value">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status</span>
                        <span class="value">{!! $order->payment_status_badge !!}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Grand Total</span>
                        <span class="value">{{ currency_format($order->grand_total) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Amount Paid</span>
                        <span class="value text-success">{{ currency_format($order->amount_paid) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Amount Due</span>
                        <span class="value text-danger">{{ currency_format($order->amount_due) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes || $order->internal_notes)
                <div class="info-card">
                    <div class="card-header">
                        <h6><i class="fa fa-sticky-note me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        @if($order->notes)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Customer Notes</label>
                                <p class="mb-0">{{ $order->notes }}</p>
                            </div>
                        @endif
                        @if($order->internal_notes)
                            <div>
                                <label class="form-label text-muted small">Internal Notes</label>
                                <p class="mb-0 text-warning">{{ $order->internal_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editItemForm">
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="editItemId">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="editItemProduct" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" id="editItemQty" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit Price</label>
                            <input type="number" name="unit_price" class="form-control" id="editItemPrice" min="0"
                                step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Amount</label>
                            <input type="number" name="discount_amount" class="form-control" id="editItemDiscount" min="0"
                                step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            // Status change form
            $('#statusChangeForm').on('submit', function (e) {
                e.preventDefault();

                const status = $(this).find('select[name="status"]').val();
                const comment = $(this).find('textarea[name="comment"]').val();

                $.ajax({
                    url: '{{ route("orders.update-status", $order) }}',
                    type: 'POST',
                    data: {
                        status: status,
                        comment: comment,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to update status';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            // Edit item
            $(document).on('click', '.edit-item', function () {
                const item = $(this).data('item');
                $('#editItemId').val(item.id);
                $('#editItemProduct').val(item.product_name);
                $('#editItemQty').val(item.quantity);
                $('#editItemPrice').val(item.unit_price);
                $('#editItemDiscount').val(item.discount_amount);
                $('#editItemModal').modal('show');
            });

            $('#editItemForm').on('submit', function (e) {
                e.preventDefault();

                const itemId = $('#editItemId').val();

                $.ajax({
                    url: '{{ url("admin/orders") }}/{{ $order->id }}/items/' + itemId,
                    type: 'PUT',
                    data: {
                        quantity: $('#editItemQty').val(),
                        unit_price: $('#editItemPrice').val(),
                        discount_amount: $('#editItemDiscount').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to update item';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            // Delete item
            $(document).on('click', '.delete-item', function () {
                const itemId = $(this).data('item-id');

                Swal.fire({
                    title: 'Remove Item?',
                    text: 'This item will be removed from the order.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url("admin/orders") }}/{{ $order->id }}/items/' + itemId,
                            type: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (response) {
                                Swal.fire('Removed!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                const msg = xhr.responseJSON?.message || 'Failed to remove item';
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush