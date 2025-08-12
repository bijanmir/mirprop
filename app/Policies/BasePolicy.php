<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    protected function belongsToUserOrganization(User $user, $model): bool
    {
        return $user->current_organization_id === $model->organization_id;
    }
    
    protected function canAccessOrganization(User $user, $organizationId): bool
    {
        return $user->organizations()
            ->where('organization_id', $organizationId)
            ->exists();
    }
    
    protected function isOwner(User $user): bool
    {
        return $user->hasOrganizationRole('owner');
    }
    
    protected function isOwnerOrManager(User $user): bool
    {
        return $user->hasOrganizationRole(['owner', 'manager']);
    }
    
    protected function isStaff(User $user): bool
    {
        return $user->hasOrganizationRole(['owner', 'manager', 'staff']);
    }
}