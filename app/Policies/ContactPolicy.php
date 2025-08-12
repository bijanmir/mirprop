<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Users can view contacts in their organization
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
            && $this->isOwnerOrManager($user);
    }

    public function createLease(User $user, Contact $contact): bool
    {
        return $this->belongsToUserOrganization($user, $contact) 
            && $this->isStaff($user)
            && $contact->type === 'tenant';
    }

    public function viewPayments(User $user, Contact $contact): bool
    {
        return $this->belongsToUserOrganization($user, $contact) 
            && $this->isStaff($user);
    }
}