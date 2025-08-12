<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'end_date' => ['required', 'date', 'after:start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0', 'max:999999'],
            'late_fee_amount' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'late_fee_type' => ['required_with:late_fee_amount', Rule::in(['fixed', 'percentage'])],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'status' => ['sometimes', Rule::in(['pending', 'active', 'expired', 'terminated'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert amounts to cents
        if ($this->has('rent_amount')) {
            $this->merge(['rent_amount_cents' => $this->rent_amount * 100]);
        }
        
        // Prepare late fee rules
        if ($this->filled('late_fee_amount')) {
            $this->merge([
                'late_fee_rules' => [
                    'amount' => $this->late_fee_amount,
                    'type' => $this->late_fee_type,
                    'grace_period_days' => $this->grace_period_days ?? 5,
                ]
            ]);
        }
    }
}