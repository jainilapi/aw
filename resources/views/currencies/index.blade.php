@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Currencies</h5>
                    <a href="{{ route('currencies.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Currency
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="currencies-table" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Name</th>
                                    <th>ISO Code</th>
                                    <th>Symbol</th>
                                    <th>Exchange Rate</th>
                                    <th>Sample Format</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this currency?</p>
                    <p class="text-danger mb-0">
                        <small>This action cannot be undone.</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .badge {
            font-weight: 500;
        }

        #currencies-table td {
            vertical-align: middle;
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            var table = $('#currencies-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('currencies.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'iso_code', name: 'iso_code' },
                    { data: 'symbol', name: 'symbol' },
                    { data: 'formatted_rate', name: 'exchange_rate', orderable: true, searchable: false },
                    { data: 'sample_format', name: 'sample_format', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'is_active', orderable: true, searchable: false },
                    { data: 'base_badge', name: 'is_base', orderable: true, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 25
            });

            // Delete button click
            var deleteUrl = '';
            $(document).on('click', '.delete-btn', function () {
                deleteUrl = $(this).data('url');
                $('#deleteModal').modal('show');
            });

            // Confirm delete
            $('#confirmDelete').on('click', function () {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#deleteModal').modal('hide');
                        if (response.success) {
                            table.ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        $('#deleteModal').modal('hide');
                        var msg = xhr.responseJSON?.message || 'An error occurred.';
                        toastr.error(msg);
                    }
                });
            });
        });
    </script>
@endpush