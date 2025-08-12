<?php

namespace App\Http\Requests;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Vendor::class);
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        
        return [
            'contact_id' => [
                'required',
                Rule::exists('contacts', 'id')
                    ->where('organization_id', $orgId)
                    ->where('type', 'vendor')
            ],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'organization_id' => $this->user()->current_organization_id,
        ]);
    }
}