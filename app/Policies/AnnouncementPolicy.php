<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Announcement $announcement): bool
    {
        // Staff can view all announcements
        if ($this->belongsToUserOrganization($user, $announcement) && $this->isStaff($user)) {
            return true;
        }
        
        // Others can only view sent announcements for their audience
        if ($this->belongsToUserOrganization($user, $announcement) && $announcement->isSent()) {
            if ($announcement->audience === 'all') {
                return true;
            }
            
            if ($announcement->audience === 'tenants' && $user->hasOrganizationRole('tenant')) {
                return true;
            }
            
            if ($announcement->audience === 'owners' && $user->hasOrganizationRole('owner')) {
                return true;
            }
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $this->isOwnerOrManager($user);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $this->belongsToUserOrganization($user, $announcement) 
            && $this->isOwnerOrManager($user)
            && !$announcement->isSent(); // Can't update sent announcements
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->belongsToUserOrganization($user, $announcement) 
            && $this->isOwner($user)
            && !$announcement->isSent();
    }

    public function restore(User $user, Announcement $announcement): bool
    {
        return $this->belongsToUserOrganization($user, $announcement) 
            && $this->isOwner($user);
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return false;
    }
}