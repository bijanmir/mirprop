<?php

namespace App\Http\Requests;

use App\Models\Lease;
use App\Models\Unit;
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
                    $unit = Unit::find($value);
                    if ($unit && $unit->activeLease()->exists()) {
                        $fail('This unit already has an active lease.');
                    }
                }
            ],
            'primary_contact_id' => [
                'required',
                Rule::exists('contacts', 'id')
                    ->where('organization_id', $orgId)
                    ->whereIn('type', ['tenant', 'other'])
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0', 'max:999999'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'frequency' => ['required', Rule::in(['monthly', 'weekly', 'yearly'])],
            'late_fee_amount' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'late_fee_type' => ['required_with:late_fee_amount', Rule::in(['fixed', 'percentage'])],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert amounts to cents
        if ($this->has('rent_amount')) {
            $this->merge(['rent_amount_cents' => $this->rent_amount * 100]);
        }
        
        if ($this->has('deposit_amount')) {
            $this->merge(['deposit_amount_cents' => $this->deposit_amount * 100]);
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

    protected function passedValidation(): void
    {
        $this->merge([
            'organization_id' => $this->user()->current_organization_id,
        ]);
    }
}