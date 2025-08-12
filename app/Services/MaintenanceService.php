<?php

namespace App\Services;

use App\Models\MaintenanceTicket;
use App\Models\MaintenanceEvent;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceService
{
    public function createTicket(array $data): MaintenanceTicket
    {
        return DB::transaction(function () use ($data) {
            $ticket = MaintenanceTicket::create($data);
            
            // Create initial event
            $ticket->events()->create([
                'actor_id' => auth()->id(),
                'type' => 'note',
                'notes' => 'Ticket created',
            ]);
            
            // Handle image uploads if present
            if (!empty($data['images'])) {
                $this->handleImageUploads($ticket, $data['images']);
            }
            
            Log::info('Maintenance ticket created', [
                'ticket_id' => $ticket->id,
                'property_id' => $ticket->property_id,
                'priority' => $ticket->priority,
            ]);
            
            return $ticket;
        });
    }
    
    public function updateTicket(MaintenanceTicket $ticket, array $data): MaintenanceTicket
    {
        return DB::transaction(function () use ($ticket, $data) {
            $changes = [];
            
            // Track status changes
            if (isset($data['status']) && $data['status'] !== $ticket->status) {
                $changes['status'] = [
                    'old' => $ticket->status,
                    'new' => $data['status']
                ];
                
                if ($data['status'] === 'completed') {
                    $data['completed_at'] = now();
                }
            }
            
            // Track priority changes
            if (isset($data['priority']) && $data['priority'] !== $ticket->priority) {
                $changes['priority'] = [
                    'old' => $ticket->priority,
                    'new' => $data['priority']
                ];
            }
            
            $ticket->update($data);
            
            // Create event for changes
            if (!empty($changes)) {
                $ticket->events()->create([
                    'actor_id' => auth()->id(),
                    'type' => 'status_change',
                    'meta' => $changes,
                    'notes' => 'Updated ticket',
                ]);
            }
            
            return $ticket;
        });
    }
    
    public function assignVendor(MaintenanceTicket $ticket, Vendor $vendor, ?string $notes = null): MaintenanceTicket
    {
        return DB::transaction(function () use ($ticket, $vendor, $notes) {
            $ticket->update([
                'assigned_vendor_id' => $vendor->id,
                'status' => 'assigned',
            ]);
            
            $ticket->events()->create([
                'actor_id' => auth()->id(),
                'type' => 'assignment',
                'notes' => $notes ?? "Assigned to {$vendor->contact->name}",
                'meta' => [
                    'vendor_id' => $vendor->id,
                    'vendor_name' => $vendor->contact->name,
                ],
            ]);
            
            Log::info('Vendor assigned to ticket', [
                'ticket_id' => $ticket->id,
                'vendor_id' => $vendor->id,
            ]);
            
            // TODO: Send notification to vendor
            
            return $ticket;
        });
    }
    
    public function addEvent(MaintenanceTicket $ticket, array $eventData): MaintenanceEvent
    {
        $data = [
            'actor_id' => auth()->id(),
            'type' => $eventData['type'],
            'notes' => $eventData['notes'] ?? null,
        ];
        
        if ($eventData['type'] === 'cost' && isset($eventData['cost'])) {
            $data['cost_cents'] = $eventData['cost'] * 100;
        }
        
        return $ticket->events()->create($data);
    }
    
    protected function handleImageUploads(MaintenanceTicket $ticket, array $images): void
    {
        $paths = [];
        
        foreach ($images as $image) {
            $path = $image->store("tickets/{$ticket->id}", 's3');
            $paths[] = $path;
        }
        
        if (!empty($paths)) {
            $ticket->events()->create([
                'actor_id' => auth()->id(),
                'type' => 'attachment',
                'attachments' => $paths,
                'notes' => count($paths) . ' image(s) uploaded',
            ]);
        }
    }
    
    public function paginate(array $filters = [], int $perPage = 15)
    {
        $query = MaintenanceTicket::with(['property', 'unit', 'contact', 'assignedVendor.contact']);
        
        // Apply role-based filtering
        $this->applyRoleFilters($query);
        
        // Apply search and other filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        if (!empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
    
    protected function applyRoleFilters($query): void
    {
        $user = auth()->user();
        
        if ($user->hasOrganizationRole('tenant')) {
            $contact = $user->currentOrganization->contacts()
                ->where('email', $user->email)
                ->first();
            
            if ($contact) {
                $query->where('contact_id', $contact->id);
            }
        } elseif ($user->hasOrganizationRole('vendor')) {
            $vendor = $user->currentOrganization->vendors()
                ->whereHas('contact', fn($q) => $q->where('email', $user->email))
                ->first();
            
            if ($vendor) {
                $query->where('assigned_vendor_id', $vendor->id);
            }
        }
    }
}