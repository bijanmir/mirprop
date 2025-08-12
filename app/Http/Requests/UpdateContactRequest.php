<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        
        return [
            'type' => ['required', Rule::in(['tenant', 'owner', 'vendor', 'other'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('contacts')
                    ->where('organization_id', $orgId)
                    ->ignore($this->route('contact')->id)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address.line1' => ['nullable', 'string', 'max:255'],
            'address.line2' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'size:2'],
            'address.zip' => ['nullable', 'string', 'max:10'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('address.state')) {
            $address = $this->address;
            $address['state'] = strtoupper($address['state'] ?? '');
            $this->merge(['address' => $address]);
        }
    }
}