<?php

namespace App\Http\Requests;

use App\Models\Lease;
use App\Models\Unit;
use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Lease::class);
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        
        return [
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('organization_id', $orgId),
                function ($attribute, $value, $fail) {
                    // Check if unit already has an active lease
                    $hasActiveLease = Lease::where('unit_id', $value)
                        ->where('status', 'active')
                        ->exists();
                        
                    if ($hasActiveLease) {
                        $fail('This unit already has an active lease.');
                    }
                }
            ],
            'primary_contact_id' => [
                'required',
                Rule::exists('contacts', 'id')
                    ->where('organization_id', $orgId)
                    ->where('type', 'tenant')
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date'
            ],
            'rent_amount' => [
                'required',
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
                'required',
                Rule::in(['monthly', 'weekly', 'yearly'])
            ],
            'late_fee_rules' => ['nullable', 'array'],
            'late_fee_rules.amount' => ['nullable', 'numeric', 'min:0'],
            'late_fee_rules.type' => ['nullable', Rule::in(['fixed', 'percentage'])],
            'late_fee_rules.grace_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'create_rent_charge' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'Please select a unit for this lease.',
            'primary_contact_id.required' => 'Please select a tenant for this lease.',
            'start_date.after_or_equal' => 'The lease start date must be today or in the future.',
            'end_date.after' => 'The lease end date must be after the start date.',
            'rent_amount.required' => 'Please enter the monthly rent amount.',
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

        // Set default values
        if (!$this->has('create_rent_charge')) {
            $this->merge(['create_rent_charge' => true]);
        }

        if (!$this->has('frequency')) {
            $this->merge(['frequency' => 'monthly']);
        }
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'organization_id' => $this->user()->current_organization_id,
        ]);
    }
}