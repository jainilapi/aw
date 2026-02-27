@extends('frontend.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .orders-page {
            padding: 40px 0;
            background: #F8FAFC;
            min-height: 80vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #203A72;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #203A72;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-btn:hover {
            color: #1a2d5a;
        }

        /* Filter Card */
        .filters-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
        }

        .filters-title {
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filters-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #666;
            margin-bottom: 6px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #203A72;
            box-shadow: 0 0 0 3px rgba(32, 58, 114, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn-apply {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #203A72;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-apply:hover {
            background: #1a2d5a;
        }

        .btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #fff;
            color: #666;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-reset:hover {
            background: #F5F5F5;
        }

        /* DataTable Card */
        .table-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        /* Custom DataTable Styling */
        .dataTables_wrapper .dataTables_length select {
            padding: 6px 30px 6px 12px;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 8px 14px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            margin-left: 8px;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #203A72;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 6px 12px;
            margin: 0 2px;
            border-radius: 6px !important;
            border: 1px solid #E0E0E0 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #203A72 !important;
            color: #fff !important;
            border-color: #203A72 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background: #F5FAFF !important;
            border-color: #203A72 !important;
            color: #203A72 !important;
        }

        table.dataTable thead th {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            padding: 14px 10px;
            border-bottom: 2px solid #F0F0F0 !important;
        }

        table.dataTable tbody td {
            padding: 16px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #F0F0F0;
        }

        table.dataTable tbody tr:hover {
            background: #F8FAFC !important;
        }

        .order-number-cell {
            font-weight: 600;
            color: #203A72;
        }

        .badge {
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .btn-outline-primary {
            color: #203A72;
            border-color: #203A72;
        }

        .btn-outline-primary:hover {
            background: #203A72;
            color: #fff;
        }

        /* Loading State */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9) !important;
            border: none !important;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px !important;
            font-weight: 500;
            color: #203A72 !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .filter-group {
                flex: 100%;
            }

            .filter-actions {
                flex: 100%;
                justify-content: stretch;
            }

            .filter-actions button {
                flex: 1;
            }
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
                        <li class="active">Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="orders-page">
        <div class="container">

            <!-- Filters -->
            <div class="filters-card">
                <h4 class="filters-title"><i class="bi bi-funnel"></i> Filter Orders</h4>
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="filterStatus">Order Status</label>
                        <select id="filterStatus">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterPayment">Payment Status</label>
                        <select id="filterPayment">
                            <option value="">All Payment Statuses</option>
                            @foreach($paymentStatuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterStartDate">From Date</label>
                        <input type="text" id="filterStartDate" placeholder="Select start date">
                    </div>
                    <div class="filter-group">
                        <label for="filterEndDate">To Date</label>
                        <input type="text" id="filterEndDate" placeholder="Select end date">
                    </div>
                    <div class="filter-actions">
                        <button type="button" class="btn-apply" onclick="applyFilters()">
                            <i class="bi bi-check-lg"></i> Apply Filters
                        </button>
                        <button type="button" class="btn-reset" onclick="resetFilters()">
                            <i class="bi bi-x-lg"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-card">
                <table id="ordersTable" class="table table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        let ordersTable;

        $(document).ready(function () {
            // Initialize date pickers
            flatpickr('#filterStartDate', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });

            flatpickr('#filterEndDate', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });

            // Initialize DataTable
            ordersTable = $('#ordersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("customer.orders.data") }}',
                    type: 'GET',
                    data: function (d) {
                        d.status = $('#filterStatus').val();
                        d.payment_status = $('#filterPayment').val();
                        d.start_date = $('#filterStartDate').val();
                        d.end_date = $('#filterEndDate').val();
                    }
                },
                columns: [
                    { data: 'order_number', className: 'order-number-cell' },
                    { data: 'date' },
                    { data: 'items_count', className: 'text-center' },
                    { data: 'grand_total', className: 'fw-bold' },
                    { data: 'status', orderable: true },
                    { data: 'payment_status', orderable: true },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    processing: '<div class="d-flex align-items-center gap-2"><div class="spinner-border spinner-border-sm text-primary"></div> Loading orders...</div>',
                    emptyTable: '<div class="text-center py-4"><i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i><p class="mt-2 text-muted">No orders found</p></div>',
                    zeroRecords: '<div class="text-center py-4"><i class="bi bi-search" style="font-size: 48px; color: #ccc;"></i><p class="mt-2 text-muted">No matching orders found</p></div>'
                },
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip'
            });
        });

        function applyFilters() {
            ordersTable.ajax.reload();
        }

        function resetFilters() {
            $('#filterStatus').val('');
            $('#filterPayment').val('');
            $('#filterStartDate').val('');
            $('#filterEndDate').val('');
            ordersTable.ajax.reload();
        }
    </script>
@endpush