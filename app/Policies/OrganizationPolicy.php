<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Users can view their organizations
    }

    public function view(User $user, Organization $organization): bool
    {
        return $this->belongsToUserOrganization($user, $organization);
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can create organizations
    }

    public function update(User $user, Organization $organization): bool
    {
        return $this->belongsToUserOrganization($user, $organization) 
            && $this->isOwnerOrManager($user);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $this->belongsToUserOrganization($user, $organization) 
            && $this->isOwner($user);
    }

    public function manageUsers(User $user, Organization $organization): bool
    {
        return $this->belongsToUserOrganization($user, $organization) 
            && $this->isOwnerOrManager($user);
    }

    public function manageBilling(User $user, Organization $organization): bool
    {
        return $this->belongsToUserOrganization($user, $organization) 
            && $this->isOwner($user);
    }
}