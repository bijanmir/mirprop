<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Announcement::class);
    }

    public function rules(): array
    {
        return [
            'audience' => ['required', Rule::in(['tenants', 'owners', 'all'])],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'send_immediately' => ['boolean'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'organization_id' => $this->user()->current_organization_id,
            'created_by' => $this->user()->id,
        ]);
    }
}