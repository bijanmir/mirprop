<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Unit::class);
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        
        return [
            'property_id' => [
                'required',
                Rule::exists('properties', 'id')->where('organization_id', $orgId)
            ],
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->where(function ($query) {
                    return $query->where('property_id', $this->property_id);
                })
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

    protected function passedValidation(): void
    {
        $this->merge([
            'organization_id' => $this->user()->current_organization_id,
        ]);
    }
}