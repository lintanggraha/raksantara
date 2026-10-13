<?php

namespace App\Policies;

use App\Support\RiskAccess;
use App\User;
use App\Risk;

class RiskPolicy
{
    /**
     * Determine if the user can view any risks.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRoleAssignments() && !$user->hasPermission('risks.view')) {
            return false;
        }

        return $user->isDivisionVerified() || $user->isSuperadmin();
    }

    /**
     * Determine if the user can view the risk.
     */
    public function view(User $user, Risk $risk): bool
    {
        return RiskAccess::canView($user, $risk);
    }

    /**
     * Determine if the user can create risks.
     */
    public function create(User $user): bool
    {
        if (!$user->isDivisionVerified() || $user->isSuperadmin() || empty($user->allowedRiskOwners())) {
            return false;
        }

        return !$user->hasRoleAssignments() || $user->hasPermission('risks.manage');
    }

    /**
     * Determine if the user can update the risk.
     */
    public function update(User $user, Risk $risk): bool
    {
        return RiskAccess::canManage($user, $risk);
    }

    /**
     * Determine if the user can delete the risk.
     */
    public function delete(User $user, Risk $risk): bool
    {
        return RiskAccess::canManage($user, $risk);
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
        return RiskAccess::canManage($user, $risk);
    }
}
