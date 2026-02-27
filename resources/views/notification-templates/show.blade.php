@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-6">
                <h4 class="mb-0">{{ $title }}</h4>
                <small class="text-muted">{{ $subTitle }}</small>
            </div>
            <div class="col-6 text-end">
                <a href="{{ route('notification-templates.index') }}" class="btn btn-secondary">
                    Back to List
                </a>
                @can('notification-templates.edit')
                    <a href="{{ route('notification-templates.edit', $template) }}" class="btn btn-primary">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                @endcan
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">{{ $template->name }}</h5>
                        <p class="mb-1"><strong>Slug:</strong> {{ $template->slug }}</p>
                        <p class="mb-1"><strong>Channel:</strong> {{ ucfirst(str_replace('_', ' ', $template->channel)) }}</p>
                        <p class="mb-1"><strong>Type:</strong>
                            {{ $template->template_type ? ucfirst(str_replace('_', ' ', $template->template_type)) : '-' }}
                        </p>
                        <p class="mb-1"><strong>Status:</strong>
                            @if($template->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </p>
                        @if($template->subject)
                            <p class="mb-1"><strong>Subject:</strong> {{ $template->subject }}</p>
                        @endif
                        @if($template->from_name || $template->from_email)
                            <p class="mb-1">
                                <strong>From:</strong>
                                {{ $template->from_name ?: '' }}
                                @if($template->from_email)
                                    &lt;{{ $template->from_email }}&gt;
                                @endif
                            </p>
                        @endif
                        @if($template->description)
                            <p class="mt-2"><strong>Description:</strong> {{ $template->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <strong>Body Preview</strong>
                    </div>
                    <div class="card-body">
                        <div class="border rounded p-3" style="min-height: 150px;">
                            {!! $template->body !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <strong>Meta</strong>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Created At:</strong> {{ $template->created_at?->format('Y-m-d H:i') }}</p>
                        <p class="mb-1"><strong>Updated At:</strong> {{ $template->updated_at?->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

