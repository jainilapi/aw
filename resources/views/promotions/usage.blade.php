@extends('layouts.app',['title' => $title, 'subTitle' => $subTitle,'datatable' => true, 'select2' => true, 'datepicker' => true])

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">Report Filters</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-success" id="exportBtn">
                            <i class="fa fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="exportForm" action="{{ route('reports.promotion-usage.export') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Promotion Code</label>
                            <select class="form-select select2" id="filter-promotion-code" name="filter_promotion_code">
                                <option value="">All Promotions</option>
                                @foreach($promotions as $code => $name)
                                    <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer</label>
                            <input type="text" class="form-control" id="filter-customer" name="filter_customer" placeholder="Name or Email">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order Status</label>
                            <select class="form-select" id="filter-status" name="filter_status">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="filter-date-range" placeholder="Select Date Range">
                            <input type="hidden" id="filter-date-from" name="filter_date_from">
                            <input type="hidden" id="filter-date-to" name="filter_date_to">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-primary me-2" id="applyFilters">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-secondary" id="clearFilters">
                                <i class="fa fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Usage Details</h5>
            </div>
            <div class="card-body">
                <table id="datatables-reponsive" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order Number</th>
                            <th>Order Date</th>
                            <th>Customer</th>
                            <th>Promotion</th>
                            <th>Discount</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align:right">Total:</th>
                            <th id="totalDiscount"></th>
                            <th id="totalAmount"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    let dataTable = $('#datatables-reponsive').DataTable({
        pageLength: 20,
        searching: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('promotions.usage.report') }}",
            type: "GET",
            data: function(d) {
                d.filter_promotion_code = $('#filter-promotion-code').val();
                d.filter_customer = $('#filter-customer').val();
                d.filter_status = $('#filter-status').val();
                d.filter_date_from = $('#filter-date-from').val();
                d.filter_date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_number_link', name: 'order_number' },
            { data: 'order_date_formatted', name: 'order_date' },
            { data: 'customer_info', name: 'customer.name' },
            { data: 'promotion_details', name: 'promotion_code' },
            { data: 'discount_amount_formatted', name: 'promotion_discount' },
            { data: 'total_amount_formatted', name: 'total_amount' },
            { data: 'status_badge', name: 'status' }
        ],
        footerCallback: function (row, data, start, end, display) {
            let api = this.api();
            
            let intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            let pageTotalDiscount = api
                .column(5, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            let pageTotalAmount = api
                .column(6, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            $(api.column(5).footer()).html('$' + pageTotalDiscount.toFixed(2));
            $(api.column(6).footer()).html('$' + pageTotalAmount.toFixed(2));
        }
    });

    $('#filter-date-range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#filter-date-range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        $('#filter-date-from').val(picker.startDate.format('YYYY-MM-DD'));
        $('#filter-date-to').val(picker.endDate.format('YYYY-MM-DD'));
    });

    $('#filter-date-range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
    });

    $('#applyFilters').on('click', function() {
        dataTable.ajax.reload();
    });

    $('#clearFilters').on('click', function() {
        $('#filter-promotion-code').val('').trigger('change');
        $('#filter-customer').val('');
        $('#filter-status').val('');
        $('#filter-date-range').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        dataTable.ajax.reload();
    });

    $('#exportBtn').on('click', function() {
        $('#exportForm').submit();
    });
});
</script>
@endpush
