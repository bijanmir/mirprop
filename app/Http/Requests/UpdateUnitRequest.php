<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->where(function ($query) {
                    return $query->where('property_id', $this->route('unit')->property_id);
                })->ignore($this->route('unit')->id)
            ],
            'beds' => ['nullable', 'integer', 'min:0', 'max:10'],
            'baths' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'sqft' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'rent_amount' => ['required', 'numeric', 'min:0', 'max:999999'],
            'status' => ['required', Rule::in(['available', 'occupied', 'maintenance'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('rent_amount')) {
            $this->merge([
                'rent_amount_cents' => $this->rent_amount * 100,
            ]);
        }
    }
}