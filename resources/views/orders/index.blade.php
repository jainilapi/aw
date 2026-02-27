@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle, 'datatable' => true])

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        .filter-card {
            /* background: linear-gradient(135deg, #667eea00 0%, #764ba200 100%); */
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .filter-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            cursor: pointer;
        }

        .filter-card .card-header:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .status-badge-lg {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }

        .order-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .order-stat-card {
            flex: 1;
            min-width: 140px;
            padding: 1rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            text-align: center;
            transition: transform 0.2s;
        }

        .order-stat-card:hover {
            transform: translateY(-2px);
        }

        .order-stat-card.pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffc107 100%);
        }

        .order-stat-card.processing {
            background: linear-gradient(135deg, #cce5ff 0%, #0d6efd 100%);
            color: white;
        }

        .order-stat-card.shipped {
            background: linear-gradient(135deg, #d1ecf1 0%, #17a2b8 100%);
            color: white;
        }

        .order-stat-card.delivered {
            background: linear-gradient(135deg, #d4edda 0%, #28a745 100%);
            color: white;
        }

        .order-stat-card h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: bold;
        }

        .order-stat-card small {
            opacity: 0.8;
        }

        .bulk-action-bar {
            display: none;
            padding: 1rem;
            background: #e3f2fd;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            align-items: center;
            gap: 1rem;
        }

        .bulk-action-bar.show {
            display: flex;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Filters Card -->
            <div class="card filter-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse">
                    <h6 class="mb-0"><i class="fa fa-filter me-2"></i>Filters</h6>
                    <i class="fa fa-chevron-down"></i>
                </div>
                <div class="collapse show" id="filterCollapse">
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Order Number</label>
                                    <input type="text" name="order_number" class="form-control"
                                        placeholder="Search order #">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Customer</label>
                                    <select name="customer_id" class="form-control select2-customers" style="width: 100%">
                                        <option value="">All Customers</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status[]" class="form-control select2-status" multiple
                                        style="width: 100%">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-control">
                                        <option value="">All Payment Status</option>
                                        @foreach($paymentStatuses as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Source</label>
                                    <select name="source" class="form-control">
                                        <option value="">All Sources</option>
                                        <option value="customer">Customer Placed</option>
                                        <option value="admin">Admin Created</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date Range</label>
                                    <input type="text" name="date_range" class="form-control" id="dateRange"
                                        placeholder="Select date range">
                                </div>
                                <div class="col-md-6 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search me-1"></i>Apply
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="clearFilters">
                                        <i class="fa fa-times me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div class="bulk-action-bar" id="bulkActionBar">
                <span><strong id="selectedCount">0</strong> orders selected</span>
                <select class="form-control" style="width: 200px" id="bulkStatus">
                    <option value="">Change Status To...</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm" id="applyBulkAction">Apply</button>
                <button class="btn btn-secondary btn-sm" id="clearSelection">Clear Selection</button>
            </div>

            <!-- Orders Table Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Orders</h5>
                    @if(auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('orders.create'))
                        <a href="{{ route('orders.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus me-1"></i> Create Order
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="ordersTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="30"><input type="checkbox" id="selectAll"></th>
                                    <th width="50">#</th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Source</th>
                                    <th>Created</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2 for customers
            $('.select2-customers').select2({
                placeholder: 'Search customer...',
                allowClear: true,
                ajax: {
                    url: '{{ route("orders.customers") }}',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term,
                            _token: '{{ csrf_token() }}'
                        };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    }
                }
            });

            // Initialize Select2 for status
            $('.select2-status').select2({
                placeholder: 'Select status...',
                allowClear: true
            });

            // Initialize Date Range Picker
            $('#dateRange').daterangepicker({
                autoUpdateInput: false,
                locale: { cancelLabel: 'Clear' },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRange').on('cancel.daterangepicker', function () {
                $(this).val('');
            });

            // Track selected orders
            let selectedOrders = [];

            // Initialize DataTable
            const table = $('#ordersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('orders.index') }}",
                    data: function (d) {
                        d.order_number = $('input[name="order_number"]').val();
                        d.customer_id = $('select[name="customer_id"]').val();
                        d.status = $('select[name="status[]"]').val();
                        d.payment_status = $('select[name="payment_status"]').val();
                        d.source = $('select[name="source"]').val();

                        const dateRange = $('input[name="date_range"]').val();
                        if (dateRange) {
                            const dates = dateRange.split(' - ');
                            d.date_from = dates[0];
                            d.date_to = dates[1];
                        }
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return '<input type="checkbox" class="order-checkbox" value="' + data.id + '">';
                        }
                    },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'order_number', name: 'order_number' },
                    { data: 'customer_name', name: 'customer.name' },
                    { data: 'items_count', name: 'items_count', orderable: false },
                    { data: 'grand_total_formatted', name: 'grand_total' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'payment_status_badge', name: 'payment_status' },
                    { data: 'source_badge', name: 'source' },
                    { data: 'created_date', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[9, 'desc']],
                responsive: true,
                drawCallback: function () {
                    // Re-check previously selected orders
                    selectedOrders.forEach(function (id) {
                        $('.order-checkbox[value="' + id + '"]').prop('checked', true);
                    });
                    updateBulkActionBar();
                }
            });

            // Filter form submit
            $('#filterForm').on('submit', function (e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Clear filters
            $('#clearFilters').on('click', function () {
                $('#filterForm')[0].reset();
                $('.select2-customers').val(null).trigger('change');
                $('.select2-status').val(null).trigger('change');
                table.ajax.reload();
            });

            // Select all checkbox
            $('#selectAll').on('change', function () {
                const isChecked = $(this).prop('checked');
                $('.order-checkbox').each(function () {
                    $(this).prop('checked', isChecked);
                    const id = $(this).val();
                    if (isChecked && !selectedOrders.includes(id)) {
                        selectedOrders.push(id);
                    } else if (!isChecked) {
                        selectedOrders = selectedOrders.filter(oid => oid !== id);
                    }
                });
                updateBulkActionBar();
            });

            // Individual checkbox
            $(document).on('change', '.order-checkbox', function () {
                const id = $(this).val();
                if ($(this).prop('checked')) {
                    if (!selectedOrders.includes(id)) selectedOrders.push(id);
                } else {
                    selectedOrders = selectedOrders.filter(oid => oid !== id);
                }
                updateBulkActionBar();
            });

            function updateBulkActionBar() {
                $('#selectedCount').text(selectedOrders.length);
                if (selectedOrders.length > 0) {
                    $('#bulkActionBar').addClass('show');
                } else {
                    $('#bulkActionBar').removeClass('show');
                }
            }

            // Clear selection
            $('#clearSelection').on('click', function () {
                selectedOrders = [];
                $('.order-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                updateBulkActionBar();
            });

            // Apply bulk action
            $('#applyBulkAction').on('click', function () {
                const status = $('#bulkStatus').val();
                if (!status) {
                    Swal.fire('Error', 'Please select a status', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Confirm Bulk Update',
                    text: `Update ${selectedOrders.length} orders to "${status}" status?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                    input: 'textarea',
                    inputPlaceholder: 'Optional comment...'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("orders.bulk-update-status") }}',
                            type: 'POST',
                            data: {
                                order_ids: selectedOrders,
                                status: status,
                                comment: result.value || '',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire('Success', response.message, 'success');
                                selectedOrders = [];
                                $('#selectAll').prop('checked', false);
                                updateBulkActionBar();
                                table.ajax.reload();
                            },
                            error: function () {
                                Swal.fire('Error', 'Failed to update orders', 'error');
                            }
                        });
                    }
                });
            });

            // Delete handler
            $(document).on('click', '#deleteRow', function () {
                const route = $(this).data('row-route');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This order will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: route,
                            type: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (response) {
                                Swal.fire('Deleted!', response.success, 'success');
                                table.ajax.reload();
                            },
                            error: function () {
                                Swal.fire('Error!', 'Failed to delete order.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush