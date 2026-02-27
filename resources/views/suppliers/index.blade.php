@extends('layouts.app',['title' => $title, 'subTitle' => $subTitle,'datatable' => true, 'select2' => true, 'datepicker' => true])

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="btn-group float-end">
                    <button type="button" class="btn btn-primary float-end me-2" data-bs-toggle="modal"
                        data-bs-target="#importSupplierModal">
                        <i class="fa fa-file-excel"></i> Import Suppliers
                    </button>
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary float-end"> 
                        <i class="fa fa-plus"></i> Add New Supplier 
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="datatables-reponsive" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Supplier Modal -->
<div class="modal fade" id="importSupplierModal" tabindex="-1" aria-labelledby="importSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importSupplierModalLabel">Import Suppliers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('suppliers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Choose Excel File (.xlsx, .xls)</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>

    $(document).ready(function () {

        let dataTable = $('#datatables-reponsive').DataTable({
            pageLength : 10,
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{ route(Request::route()->getName()) }}",
                "type": "GET",
                "data" : {
                    filter_status:function() {
                        return $("#filter-status").val();
                    }
                }
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'name',
                },
                {
                    data: 'email',
                },
                {
                    data: 'phone_number',
                },
                {
                    data: 'location',
                },
                {
                    data: 'status',
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                }
            ],
        });

        $(document).on('click', '#deleteRow', function () {
            let url = $(this).data('row-route');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.success, 'success');
                                dataTable.ajax.reload();
                            } else if (response.error) {
                                Swal.fire('Error', response.error, 'error');
                            }
                        },
                        error: function (xhr) {
                            let msg = 'An error occurred.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                msg = xhr.responseJSON.error;
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });
    });

</script>
@endpush
