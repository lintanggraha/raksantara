<?php

namespace App\Support;

use App\BusinessService;
use App\ServiceBia;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Who may see and maintain business services. Mirrors RiskAccess: scope comes
 * from the owner unit, and explicit role assignments add RBAC permissions.
 */
class ServiceAccess
{
    public static function applyVisibilityScope(Builder|Relation $query, ?User $user): void
    {
        if (is_null($user) || ($user->hasRoleAssignments() && !$user->hasPermission('risks.view'))) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($user->isSuperadmin() || $user->isComplianceDivision()) {
            return;
        }

        $owners = array_values(array_filter($user->allowedRiskOwners()));
        if (empty($owners)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('business_services.owner_unit', $owners);
    }

    public static function canView(?User $user, BusinessService $service): bool
    {
        $query = BusinessService::query()->whereKey($service->getKey());
        self::applyVisibilityScope($query, $user);

        return $query->exists();
    }

    public static function canManageUnit(?User $user, ?string $unit): bool
    {
        if (is_null($user) || $user->isSuperadmin()) {
            return false;
        }
        if (trim((string) $unit) === '') {
            // A service without an owner unit can only be fixed by Compliance.
            return $user->isComplianceDivision();
        }

        return $user->canManageRiskOwner($unit)
            && (!$user->hasRoleAssignments() || $user->hasPermission('risks.manage'));
    }

    public static function canManage(?User $user, BusinessService $service): bool
    {
        return self::canManageUnit($user, $service->owner_unit);
    }

    /**
     * Maker-checker: the approver must be able to manage the service and must
     * not be the person who last assessed the BIA.
     */
    public static function canApprove(?User $user, BusinessService $service, ?ServiceBia $bia): bool
    {
        return !is_null($bia)
            && self::canManage($user, $service)
            && (int) $bia->assessed_by !== (int) $user->id;
    }

    public static function manageableUnits(?User $user): array
    {
        if (is_null($user) || $user->isSuperadmin()) {
            return [];
        }

        return array_values(array_filter(
            RiskOwnerAccess::options(),
            fn ($unit) => self::canManageUnit($user, $unit)
        ));
    }
}
