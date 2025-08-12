<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        
        return [
            'lease_id' => [
                'required',
                Rule::exists('leases', 'id')->where('organization_id', $orgId)
            ],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999'],
            'payment_method' => ['required', Rule::in(['ach', 'card'])],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Payment amount must be at least $1.00',
            'amount.max' => 'Payment amount cannot exceed $999,999.00',
        ];
    }
}