<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('contact'));
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        $contact = $this->route('contact');
        
        return [
            'type' => ['sometimes', Rule::in(['tenant', 'owner', 'vendor', 'other'])],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('contacts')->where('organization_id', $orgId)->ignore($contact->id)
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
        if ($this->has('address.state') && $this->input('address.state')) {
            $address = $this->input('address', []);
            $address['state'] = strtoupper($address['state']);
            $this->merge(['address' => $address]);
        }
    }
}