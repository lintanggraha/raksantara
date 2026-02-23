<?php

namespace App\Policies;

use App\User;
use App\Risk;

class RiskPolicy
{
    /**
     * Determine if the user can view any risks.
     */
    public function viewAny(User $user): bool
    {
        return $user->isDivisionVerified() || $user->isSuperadmin();
    }

    /**
     * Determine if the user can view the risk.
     */
    public function view(User $user, Risk $risk): bool
    {
        // Superadmin can view all risks
        if ($user->isSuperadmin()) {
            return true;
        }

        // User can view risks from their division
        if ($user->isDivisionVerified()) {
            $userAllowedOwners = $user->allowedRiskOwners();
            return in_array($risk->risk_owner, $userAllowedOwners, true);
        }

        return false;
    }

    /**
     * Determine if the user can create risks.
     */
    public function create(User $user): bool
    {
        return $user->isDivisionVerified() || $user->isSuperadmin();
    }

    /**
     * Determine if the user can update the risk.
     */
    public function update(User $user, Risk $risk): bool
    {
        // Superadmin can update all risks
        if ($user->isSuperadmin()) {
            return true;
        }

        // User can update risks they own or that are in their allowed divisions
        if ($user->isDivisionVerified()) {
            $userAllowedOwners = $user->allowedRiskOwners();
            return in_array($risk->risk_owner, $userAllowedOwners, true);
        }

        return false;
    }

    /**
     * Determine if the user can delete the risk.
     */
    public function delete(User $user, Risk $risk): bool
    {
        // Only superadmin can delete risks
        return $user->isSuperadmin();
    }

    /**
     * Determine if the user can restore the risk.
     */
    public function restore(User $user, Risk $risk): bool
    {
        return $this->delete($user, $risk);
    }

    /**
     * Determine if the user can permanently delete the risk.
     */
    public function forceDelete(User $user, Risk $risk): bool
    {
        return $user->isSuperadmin();
    }
}
