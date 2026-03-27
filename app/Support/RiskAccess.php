<?php

namespace App\Support;

use App\Risk;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class RiskAccess
{
    public static function canView(?User $user, Risk $risk): bool
    {
        if (is_null($user)) {
            return false;
        }

        if ($user->isSuperadmin() || $user->isComplianceDivision()) {
            return true;
        }

        return self::isCreator($user, $risk) || $user->canManageRiskOwner($risk->risk_owner);
    }

    public static function canManage(?User $user, Risk $risk): bool
    {
        if (is_null($user) || $user->isSuperadmin()) {
            return false;
        }

        return self::isCreator($user, $risk) || $user->canManageRiskOwner($risk->risk_owner);
    }

    public static function applyVisibilityScope(
        Builder $query,
        ?User $user,
        string $ownerColumn = 'risk_owner',
        string $createdByColumn = 'created_by'
    ): void {
        if (is_null($user)) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($user->isSuperadmin() || $user->isComplianceDivision()) {
            return;
        }

        $userId = (int) $user->id;
        $allowedOwners = array_values(array_filter($user->allowedRiskOwners(), function ($owner) {
            return trim((string) $owner) !== '';
        }));

        if ($userId <= 0 && empty($allowedOwners)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $scopedQuery) use ($userId, $allowedOwners, $ownerColumn, $createdByColumn) {
            $hasCondition = false;

            if ($userId > 0) {
                $scopedQuery->where($createdByColumn, $userId);
                $hasCondition = true;
            }

            if (!empty($allowedOwners)) {
                if ($hasCondition) {
                    $scopedQuery->orWhereIn($ownerColumn, $allowedOwners);
                } else {
                    $scopedQuery->whereIn($ownerColumn, $allowedOwners);
                }
            }
        });
    }

    public static function scopeCacheKey(?User $user): string
    {
        if (is_null($user)) {
            return 'guest';
        }

        if ($user->isSuperadmin()) {
            return 'superadmin:' . (int) $user->id;
        }

        if ($user->isComplianceDivision()) {
            return 'compliance:' . (int) $user->id;
        }

        $allowedOwners = $user->allowedRiskOwners();
        sort($allowedOwners, SORT_NATURAL | SORT_FLAG_CASE);

        return 'user:' . (int) $user->id . ':' . sha1(json_encode(array_values($allowedOwners)));
    }

    private static function isCreator(User $user, Risk $risk): bool
    {
        return (int) ($risk->created_by ?? 0) > 0
            && (int) $risk->created_by === (int) $user->id;
    }
}
