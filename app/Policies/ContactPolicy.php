<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Contact $contact): bool
    {
        return $this->belongsToUserOrganization($user, $contact);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Contact $contact): bool
    {
        return $this->belongsToUserOrganization($user, $contact) 
            && $this->isStaff($user);
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $this->belongsToUserOrganization($user, $contact) 
            && $this->isOwnerOrManager($user)
            && !$contact->leases()->exists(); // Can't delete contact with leases
    }

    public function restore(User $user, Contact $contact): bool
    {
        return $this->belongsToUserOrganization($user, $contact) 
            && $this->isOwnerOrManager($user);
    }

    public function forceDelete(User $user, Contact $contact): bool
    {
        return false;
    }
}