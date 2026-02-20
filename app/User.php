<?php

namespace App;

use App\Support\RiskOwnerAccess;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'division', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function allowedRiskOwners(?string $includeCurrent = null): array
    {
        return RiskOwnerAccess::allowedRiskOwnersForDivision($this->division, $includeCurrent);
    }

    public function canManageRiskOwner(?string $riskOwner): bool
    {
        return RiskOwnerAccess::canManageRiskOwner($this->division, $riskOwner);
    }

    public function isComplianceDivision(): bool
    {
        return RiskOwnerAccess::isCompliance($this->division);
    }
}
