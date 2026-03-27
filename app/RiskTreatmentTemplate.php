<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskTreatmentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'risk_owner',
        'option',
        'description',
        'default_pic',
        'default_effectiveness',
        'target_days',
        'next_action_offset_days',
        'checklist',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (is_null($user)) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperadmin() || $user->isComplianceDivision()) {
            return $query;
        }

        $allowedOwners = array_values(array_filter($user->allowedRiskOwners(), function ($owner) {
            return trim((string) $owner) !== '';
        }));
        $userId = (int) $user->id;

        return $query->where(function (Builder $scopedQuery) use ($allowedOwners, $userId) {
            $hasCondition = false;

            if ($userId > 0) {
                $scopedQuery->where('created_by', $userId);
                $hasCondition = true;
            }

            if (!empty($allowedOwners)) {
                if ($hasCondition) {
                    $scopedQuery->orWhereIn('risk_owner', $allowedOwners);
                } else {
                    $scopedQuery->whereIn('risk_owner', $allowedOwners);
                }
            }
        });
    }

    public function canManageBy(?User $user): bool
    {
        if (is_null($user) || !$user->isDivisionVerified() || $user->isSuperadmin()) {
            return false;
        }

        if ($user->isComplianceDivision()) {
            return true;
        }

        return $user->canManageRiskOwner($this->risk_owner)
            || (int) ($this->created_by ?? 0) === (int) $user->id;
    }
}
