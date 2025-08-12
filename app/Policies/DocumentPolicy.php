<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Document $document): bool
    {
        // Check organization ownership first
        if (!$this->belongsToUserOrganization($user, $document)) {
            return false;
        }
        
        // Then check access to the parent entity
        $parentEntity = $document->documentable;
        
        if (!$parentEntity) {
            return false;
        }
        
        // Use the parent entity's policy
        return $user->can('view', $parentEntity);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Document $document): bool
    {
        return $this->belongsToUserOrganization($user, $document) 
            && $this->isStaff($user);
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->belongsToUserOrganization($user, $document) 
            && $this->isOwnerOrManager($user);
    }

    public function restore(User $user, Document $document): bool
    {
        return $this->belongsToUserOrganization($user, $document) 
            && $this->isOwnerOrManager($user);
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}