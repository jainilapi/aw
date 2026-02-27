@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle, 'datatable' => true])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Promotions</h5>
                    @if(auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('promotions.create'))
                        <a href="{{ route('promotions.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus me-1"></i> Add Promotion
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="promotionsTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Discount</th>
                                    <th>Validity</th>
                                    <th>Status</th>
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
    <script>
        $(document).ready(function () {
            const table = $('#promotionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('promotions.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'type_label', name: 'type' },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'discount_info', name: 'discount_amount', orderable: false },
                    { data: 'date_range', name: 'start_date' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[1, 'asc']],
                responsive: true
            });

            // Delete handler
            $(document).on('click', '#deleteRow', function () {
                const route = $(this).data('row-route');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This promotion will be deleted!",
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
                                Swal.fire('Error!', 'Failed to delete promotion.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush