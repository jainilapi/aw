@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">Tax Slab Details</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">Name</th>
                        <td>{{ $taxSlab->name }}</td>
                    </tr>
                    <tr>
                        <th>Tax Percentage</th>
                        <td>{{ $taxSlab->tax_percentage }} %</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $taxSlab->description ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($taxSlab->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td>{{ $taxSlab->createdBy ? $taxSlab->createdBy->name : 'System' }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $taxSlab->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="{{ route('tax-slabs.index') }}" class="btn btn-secondary">Back to List</a>
                    @if(auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('tax-slabs.edit'))
                    <a href="{{ route('tax-slabs.edit', encrypt($taxSlab->id)) }}" class="btn btn-primary">Edit</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
