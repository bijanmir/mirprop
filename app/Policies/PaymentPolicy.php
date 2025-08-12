<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Payment $payment): bool
    {
        // Staff can view, or tenant can view their own payments
        if ($this->belongsToUserOrganization($user, $payment)) {
            return true;
        }
        
        // Check if user is the payer
        return $user->hasOrganizationRole('tenant') 
            && $payment->contact->email === $user->email;
    }

    public function create(User $user): bool
    {
        // Only the system creates payments via webhooks
        return false;
    }

    public function update(User $user, Payment $payment): bool
    {
        // Payments are immutable
        return false;
    }

    public function delete(User $user, Payment $payment): bool
    {
        // Payments cannot be deleted
        return false;
    }

    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }
}