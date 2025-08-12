<?php

namespace App\Http\Requests;

use App\Models\MaintenanceTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MaintenanceTicket::class);
    }

    public function rules(): array
    {
        $orgId = $this->user()->current_organization_id;
        
        return [
            'property_id' => [
                'required',
                Rule::exists('properties', 'id')->where('organization_id', $orgId)
            ],
            'unit_id' => [
                'nullable',
                Rule::exists('units', 'id')
                    ->where('organization_id', $orgId)
                    ->where('property_id', $this->property_id)
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'emergency'])],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:10240'], // 10MB max per image
        ];
    }

    protected function passedValidation(): void
    {
        $data = [
            'organization_id' => $this->user()->current_organization_id,
        ];
        
        // Set contact_id if tenant
        if ($this->user()->hasOrganizationRole('tenant')) {
            $contact = $this->user()->currentOrganization->contacts()
                ->where('email', $this->user()->email)
                ->first();
            
            if ($contact) {
                $data['contact_id'] = $contact->id;
            }
        }
        
        $this->merge($data);
    }
}