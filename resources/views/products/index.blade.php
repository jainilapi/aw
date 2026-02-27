@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle, 'datatable' => true])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="btn-group float-end">
                        <button type="button" class="btn btn-primary float-end me-2" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="fa fa-file-excel"></i> Import Products
                        </button>
                        <button type="button" class="btn btn-primary float-end me-2" data-bs-toggle="modal"
                            data-bs-target="#imageImportModal">
                            <i class="fa fa-file-image"></i> Import Products Images
                        </button>
                        <button type="button" class="btn btn-primary float-end me-2" data-bs-toggle="modal"
                            data-bs-target="#importInventoryModal">
                            <i class="fa fa-boxes"></i> Import Inventory
                        </button>
                        <button type="button" class="btn btn-primary float-end me-2" data-bs-toggle="modal"
                            data-bs-target="#importHistory">
                            <i class="fa fa-refresh"></i> Import History
                        </button>

                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa fa-plus"></i> Add New Product
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('product-management', ['type' => encrypt('simple'), 'step' => encrypt(1)]) }}">
                                    <i class="fa fa-cube me-2"></i> Simple Product
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('product-management', ['type' => encrypt('variable'), 'step' => encrypt(1)]) }}">
                                    <i class="fa fa-tags me-2"></i> Variable Product
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('product-management', ['type' => encrypt('bundle'), 'step' => encrypt(1)]) }}">
                                    <i class="fa fa-archive me-2"></i> Bundle Product
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <table id="datatables-reponsive" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>





    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="type" value="0">
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose Excel File (.xlsx)</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx"
                                required>
                        </div>
                        <div class="alert alert-info p-2">
                            The file will be added to the queue shortly after upload. You can check the import status in the
                            "Import History".
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


    <div class="modal fade" id="imageImportModal" tabindex="-1" aria-labelledby="imageImportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageImportModalLabel">Import Product Images</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="type" value="1">
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose Zip File (.zip)</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".zip"
                                required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" name="override" type="checkbox" role="switch"
                                    id="flexSwitchCheckChecked" value="1">
                                <label class="form-check-label" for="flexSwitchCheckChecked">Override Secondary
                                    Images</label>
                            </div>
                        </div>

                        <div class="alert alert-info p-2">
                            <ul>
                                <li>
                                    To set the primary image, name the file <strong>"{PRODUCT/VARIANT_SKU}_0.png"</strong>.
                                </li>
                                <li>
                                    To set secondary images, name the files <strong>"{PRODUCT/VARIANT_SKU}_1.png"</strong>,
                                    <strong>"{PRODUCT/VARIANT_SKU}_2.jpg"</strong>, and so on.
                                </li>
                                <li>
                                    The file will be added to the queue shortly after upload. You can check the import
                                    status in the "Import History".
                                </li>
                            </ul>
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

    <div class="modal fade" id="importInventoryModal" tabindex="-1" aria-labelledby="importInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importInventoryModalLabel">Import Inventory Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.import-inventory') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose Excel File (.xlsx, .xls)</label>
                            <input type="hidden" name="type" value="2">
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls" required>
                        </div>
                        <div class="alert alert-info p-2">
                            The inventory details will be synced immediately. Make sure the headers align correctly.
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

    <div class="modal fade" id="importHistory" tabindex="-1" aria-labelledby="importHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importHistoryLabel">Import History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="datatables-history" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>File</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Imported By</th>
                                <th>Uploaded At</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {

            let dataTable = $('#datatables-reponsive').DataTable({
                pageLength: 10,
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route(Request::route()->getName()) }}",
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'sku'
                    },
                    {
                        data: 'product_type'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            let historyTable = $('#datatables-history').DataTable({
                pageLength: 10,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('import-history') }}",
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'file',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'type',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'imported_by',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'uploaded_at',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            $(document).on('click', '#deleteRow', function() {
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
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.success, 'success');
                                    dataTable.ajax.reload();
                                } else if (response.error) {
                                    Swal.fire('Error', response.error, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'An error occurred.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
