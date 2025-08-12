<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;

class LeasePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Lease $lease): bool
    {
        // Staff can view, or tenant can view their own lease
        if ($this->belongsToUserOrganization($user, $lease)) {
            return true;
        }
        
        // Check if user is the tenant
        return $user->hasOrganizationRole('tenant') 
            && $lease->primaryContact->email === $user->email;
    }

    public function create(User $user): bool
    {
        return $this->isOwnerOrManager($user);
    }

    public function update(User $user, Lease $lease): bool
    {
        return $this->belongsToUserOrganization($user, $lease) 
            && $this->isOwnerOrManager($user);
    }

    public function delete(User $user, Lease $lease): bool
    {
        return $this->belongsToUserOrganization($user, $lease) 
            && $this->isOwner($user)
            && $lease->status !== 'active';
    }

    public function restore(User $user, Lease $lease): bool
    {
        return $this->belongsToUserOrganization($user, $lease) 
            && $this->isOwner($user);
    }

    public function forceDelete(User $user, Lease $lease): bool
    {
        return false;
    }
}