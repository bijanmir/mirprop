<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Users can see organizations they belong to
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->organizations->contains($organization);
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can create an organization
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->organizations()
            ->where('organization_id', $organization->id)
            ->where('role', 'owner')
            ->exists();
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->organizations()
            ->where('organization_id', $organization->id)
            ->where('role', 'owner')
            ->exists();
    }

    public function restore(User $user, Organization $organization): bool
    {
        return false;
    }

    public function forceDelete(User $user, Organization $organization): bool
    {
        return false;
    }
}