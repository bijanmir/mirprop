<?php

namespace App\Policies;

use App\Models\Vendor;
use App\Models\User;

class VendorPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $this->belongsToUserOrganization($user, $vendor);
    }

    public function create(User $user): bool
    {
        return $this->isOwnerOrManager($user);
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $this->belongsToUserOrganization($user, $vendor) 
            && $this->isOwnerOrManager($user);
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $this->belongsToUserOrganization($user, $vendor) 
            && $this->isOwnerOrManager($user)
            && !$vendor->assignedTickets()->exists();
    }

    public function restore(User $user, Vendor $vendor): bool
    {
        return $this->belongsToUserOrganization($user, $vendor) 
            && $this->isOwnerOrManager($user);
    }

    public function forceDelete(User $user, Vendor $vendor): bool
    {
        return false;
    }
}