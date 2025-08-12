<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMaintenanceTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        
        return [
            'vendor_id' => [
                'required',
                Rule::exists('vendors', 'id')
                    ->where('organization_id', $orgId)
                    ->where('is_active', true)
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}