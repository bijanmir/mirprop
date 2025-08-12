<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('property'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['residential', 'commercial', 'mixed'])],
            'address_line1' => ['sometimes', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'state' => ['sometimes', 'string', 'size:2'],
            'zip' => ['sometimes', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'state.size' => 'State must be a 2-letter abbreviation (e.g., CA)',
            'country.size' => 'Country must be a 2-letter ISO code (e.g., US)',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('state')) {
            $this->merge(['state' => strtoupper($this->state)]);
        }
        
        if ($this->filled('country')) {
            $this->merge(['country' => strtoupper($this->country)]);
        }
    }
}