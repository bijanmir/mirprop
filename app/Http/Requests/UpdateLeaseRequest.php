<?php

namespace App\Http\Requests;

use App\Models\Lease;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lease'));
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        $lease = $this->route('lease');
        
        return [
            'primary_contact_id' => [
                'sometimes',
                Rule::exists('contacts', 'id')
                    ->where('organization_id', $orgId)
                    ->where('type', 'tenant')
            ],
            'start_date' => [
                'sometimes',
                'date'
            ],
            'end_date' => [
                'sometimes',
                'date',
                'after:start_date'
            ],
            'rent_amount' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:999999'
            ],
            'deposit_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999'
            ],
            'frequency' => [
                'sometimes',
                Rule::in(['monthly', 'weekly', 'yearly'])
            ],
            'status' => [
                'sometimes',
                Rule::in(['pending', 'active', 'expired', 'terminated'])
            ],
            'late_fee_rules' => ['nullable', 'array'],
            'late_fee_rules.amount' => ['nullable', 'numeric', 'min:0'],
            'late_fee_rules.type' => ['nullable', Rule::in(['fixed', 'percentage'])],
            'late_fee_rules.grace_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'The lease end date must be after the start date.',
            'rent_amount.min' => 'Rent amount must be greater than $0.',
            'rent_amount.max' => 'Rent amount cannot exceed $999,999.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert dollar amounts to cents
        if ($this->has('rent_amount')) {
            $this->merge([
                'rent_amount_cents' => round($this->rent_amount * 100),
            ]);
        }

        if ($this->has('deposit_amount')) {
            $this->merge([
                'deposit_amount_cents' => round($this->deposit_amount * 100),
            ]);
        }
    }
}