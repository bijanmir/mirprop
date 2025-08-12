<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->belongsToUserOrganization($user, $unit);
    }

    public function create(User $user): bool
    {
        return $this->isOwnerOrManager($user);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->belongsToUserOrganization($user, $unit) 
            && $this->isOwnerOrManager($user);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->belongsToUserOrganization($user, $unit) 
            && $this->isOwnerOrManager($user)
            && !$unit->leases()->exists(); // Can't delete unit with leases
    }

    public function restore(User $user, Unit $unit): bool
    {
        return $this->belongsToUserOrganization($user, $unit) 
            && $this->isOwnerOrManager($user);
    }

    public function forceDelete(User $user, Unit $unit): bool
    {
        return false;
    }
}