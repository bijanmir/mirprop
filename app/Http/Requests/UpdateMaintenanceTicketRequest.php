<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:5000'],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'emergency'])],
            'status' => ['sometimes', Rule::in(['open', 'assigned', 'in_progress', 'completed', 'closed'])],
        ];
    }
}