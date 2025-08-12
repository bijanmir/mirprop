<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['residential', 'commercial', 'mixed'])],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'zip' => ['required', 'string', 'max:10'],
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
        $this->merge([
            'state' => strtoupper($this->state ?? ''),
            'country' => strtoupper($this->country ?? 'US'),
        ]);
    }
}