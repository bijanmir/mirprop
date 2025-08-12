<?php

namespace App\Policies;

use App\Models\MaintenanceTicket;
use App\Models\User;

class MaintenanceTicketPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, MaintenanceTicket $ticket): bool
    {
        // Staff can view all tickets
        if ($this->belongsToUserOrganization($user, $ticket) && $this->isStaff($user)) {
            return true;
        }
        
        // Tenant can view their own tickets
        if ($user->hasOrganizationRole('tenant') && $ticket->contact_id) {
            return $ticket->contact->email === $user->email;
        }
        
        // Vendor can view assigned tickets
        if ($user->hasOrganizationRole('vendor') && $ticket->assigned_vendor_id) {
            $vendor = $user->currentOrganization->vendors()
                ->where('contact_id', function($query) use ($user) {
                    $query->select('id')
                        ->from('contacts')
                        ->where('email', $user->email)
                        ->limit(1);
                })->first();
            
            return $vendor && $vendor->id === $ticket->assigned_vendor_id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user) || $user->hasOrganizationRole('tenant');
    }

    public function update(User $user, MaintenanceTicket $ticket): bool
    {
        // Staff can update
        if ($this->belongsToUserOrganization($user, $ticket) && $this->isStaff($user)) {
            return true;
        }
        
        // Vendor can update assigned tickets
        if ($user->hasOrganizationRole('vendor') && $ticket->assigned_vendor_id) {
            $vendor = $user->currentOrganization->vendors()
                ->where('contact_id', function($query) use ($user) {
                    $query->select('id')
                        ->from('contacts')
                        ->where('email', $user->email)
                        ->limit(1);
                })->first();
            
            return $vendor && $vendor->id === $ticket->assigned_vendor_id;
        }
        
        return false;
    }

    public function delete(User $user, MaintenanceTicket $ticket): bool
    {
        return $this->belongsToUserOrganization($user, $ticket) 
            && $this->isOwnerOrManager($user)
            && $ticket->status === 'open';
    }

    public function restore(User $user, MaintenanceTicket $ticket): bool
    {
        return $this->belongsToUserOrganization($user, $ticket) 
            && $this->isOwnerOrManager($user);
    }

    public function forceDelete(User $user, MaintenanceTicket $ticket): bool
    {
        return false;
    }
}