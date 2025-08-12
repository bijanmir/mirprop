<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled based on parent entity
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480'], // 20MB max
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'Document size cannot exceed 20MB',
        ];
    }
}