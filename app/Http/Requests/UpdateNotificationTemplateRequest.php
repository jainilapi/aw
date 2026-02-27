<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        $templateId = $this->route('notification_template') ?? $this->route('notification-templates');

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('notification_templates', 'slug')->ignore($templateId),
            ],
            'channel' => 'required|string|max:50',
            'template_type' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:255',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:500',
            'body' => 'required|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}

