<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control"
                           value="{{ old('name', $template->name ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control"
                           value="{{ old('slug', $template->slug ?? '') }}"
                           placeholder="Auto-generated from name (you can override)">
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Channel <span class="text-danger">*</span></label>
                            <select name="channel" id="channel" class="form-select" required>
                                @foreach($channels as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('channel', $template->channel ?? 'email') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Template Type</label>
                            <select name="template_type" id="template_type" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('template_type', $template->template_type ?? '') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3 form-check mt-4">
                            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                                {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject (for Email)</label>
                    <input type="text" name="subject" id="subject" class="form-control"
                           value="{{ old('subject', $template->subject ?? '') }}">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">From Name</label>
                            <input type="text" name="from_name" id="from_name" class="form-control"
                                   value="{{ old('from_name', $template->from_name ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">From Email</label>
                            <input type="email" name="from_email" id="from_email" class="form-control"
                                   value="{{ old('from_email', $template->from_email ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <input type="text" name="description" id="description" class="form-control"
                           value="{{ old('description', $template->description ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Body <span class="text-danger">*</span></label>
                    <textarea name="body" id="body" class="form-control wysiwyg-editor" rows="10"
                              required>{{ old('body', $template->body ?? '') }}</textarea>
                </div>
            </div>
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

