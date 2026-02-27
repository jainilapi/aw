@extends('layouts.app', ['title' => $title, 'subTitle' => $subTitle])

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                Add Notification Template
                <a href="{{ route('notification-templates.index') }}" class="btn btn-secondary btn-sm float-end">
                    Back to List
                </a>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="notificationTemplateForm" method="POST" action="{{ route('notification-templates.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}"
                                       placeholder="Auto-generated from name (you can override)">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Channel <span class="text-danger">*</span></label>
                                        <select name="channel" id="channel"
                                                class="form-select @error('channel') is-invalid @enderror" required>
                                            @foreach($channels as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ old('channel', 'email') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Template Type</label>
                                        <select name="template_type" id="template_type"
                                                class="form-select @error('template_type') is-invalid @enderror">
                                            <option value="">-- Select --</option>
                                            @foreach($types as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ old('template_type') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('template_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3 form-check mt-4">
                                        <input type="checkbox" name="is_active" id="is_active" value="1"
                                               class="form-check-input"
                                               {{ old('is_active', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject (for Email)</label>
                                <input type="text" name="subject" id="subject"
                                       class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject') }}">
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">From Name</label>
                                        <input type="text" name="from_name" id="from_name"
                                               class="form-control @error('from_name') is-invalid @enderror"
                                               value="{{ old('from_name') }}">
                                        @error('from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">From Email</label>
                                        <input type="email" name="from_email" id="from_email"
                                               class="form-control @error('from_email') is-invalid @enderror"
                                               value="{{ old('from_email') }}">
                                        @error('from_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Short Description</label>
                                <input type="text" name="description" id="description"
                                       class="form-control @error('description') is-invalid @enderror"
                                       value="{{ old('description') }}">
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Body <span class="text-danger">*</span></label>
                                <textarea name="body" id="body"
                                          class="form-control wysiwyg-editor @error('body') is-invalid @enderror"
                                          rows="10" required>{{ old('body') }}</textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <strong>Available Variables</strong>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    @foreach($variables as $group => $items)
                                        <h6 class="text-muted mb-2">{{ $group }}</h6>
                                        <ul class="list-unstyled mb-3">
                                            @foreach($items as $variable)
                                                <li class="d-flex align-items-center justify-content-between mb-1">
                                                    <div>
                                                        <code>{{ $variable['key'] }}</code>
                                                        <div class="small text-muted">{{ $variable['label'] }}</div>
                                                    </div>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary copy-variable-btn"
                                                            data-key="{{ $variable['key'] }}">
                                                        Copy
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Save
                        </button>
                        <a href="{{ route('notification-templates.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

    <script>
        $(function () {
            $('.wysiwyg-editor').summernote({
                height: 260,
                placeholder: 'Write your notification body here...',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['fontsize', 'color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['codeview']]
                ]
            });

            $('#name').on('input', function () {
                const text = $(this).val() || '';
                const slug = text
                    .toString()
                    .toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
                if (!$('#slug').data('touched')) {
                    $('#slug').val(slug);
                }
            });

            $('#slug').on('input', function () {
                $(this).data('touched', true);
            });

            $('.copy-variable-btn').on('click', function () {
                const key = $(this).data('key');
                if (!key) return;

                navigator.clipboard.writeText(key).then(function () {
                    alert('Copied: ' + key);
                }).catch(function () {
                    alert('Unable to copy. Please copy manually.');
                });
            });

            $('#notificationTemplateForm').validate({
                ignore: '.note-editor *',
                rules: {
                    name: {
                        required: true,
                        maxlength: 255
                    },
                    channel: {
                        required: true
                    },
                    subject: {
                        maxlength: 255
                    },
                    from_email: {
                        email: true,
                        maxlength: 255
                    },
                    description: {
                        maxlength: 500
                    },
                    body: {
                        required: true
                    }
                }
            });
        });
    </script>
@endpush

