<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Users can view properties in their organization
    }

    public function view(User $user, Property $property): bool
    {
        return $this->belongsToUserOrganization($user, $property);
    }

    public function create(User $user): bool
    {
        return $this->isOwnerOrManager($user);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->belongsToUserOrganization($user, $property) 
            && $this->isOwnerOrManager($user);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->belongsToUserOrganization($user, $property) 
            && $this->isOwnerOrManager($user);
    }

    public function manageUnits(User $user, Property $property): bool
    {
        return $this->belongsToUserOrganization($user, $property) 
            && $this->isStaff($user);
    }
}