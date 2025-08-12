<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class ContactService
{
    public function create(array $data): Contact
    {
        $contact = Contact::create($data);
        
        Log::info('Contact created', [
            'contact_id' => $contact->id,
            'type' => $contact->type,
            'name' => $contact->name,
        ]);
        
        return $contact;
    }
    
    public function update(Contact $contact, array $data): Contact
    {
        $contact->update($data);
        
        Log::info('Contact updated', [
            'contact_id' => $contact->id,
            'updated_fields' => array_keys($data),
        ]);
        
        return $contact;
    }
    
    public function delete(Contact $contact): bool
    {
        if ($contact->leases()->exists()) {
            throw new \Exception('Cannot delete contact with existing leases');
        }
        
        $result = $contact->delete();
        
        Log::info('Contact deleted', ['contact_id' => $contact->id]);
        
        return $result;
    }
    
    public function paginate(array $filters = [], int $perPage = 15)
    {
        $query = Contact::query();
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        
        return $query->orderBy($sort, $direction)->paginate($perPage);
    }
}