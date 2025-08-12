<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
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
            && $this->isOwner($user);
    }

    public function restore(User $user, Property $property): bool
    {
        return $this->belongsToUserOrganization($user, $property) 
            && $this->isOwner($user);
    }

    public function forceDelete(User $user, Property $property): bool
    {
        return false;
    }
}