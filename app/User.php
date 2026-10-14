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
        'name',
        'username',
        'division',
        'pending_division',
        'division_verified_at',
        'is_superadmin',
        'force_password_change',
        'password_changed_at',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    protected $casts = [
        'email_verified_at' => 'datetime',
        'division_verified_at' => 'datetime',
        'is_superadmin' => 'boolean',
        'force_password_change' => 'boolean',
        'password_changed_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_last_used_at' => 'datetime',
        'email_otp_enabled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $user): void {
            // Backward compatible: records created with a direct division assignment are treated as verified.
            if (!empty($user->division) && is_null($user->division_verified_at)) {
                $user->division_verified_at = now();
            }
        });
    }

    public function isSuperadmin(): bool
    {
        return (bool) $this->is_superadmin;
    }

    public function isDivisionVerified(): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        return !is_null($this->division_verified_at) && trim((string) $this->division) !== '';
    }

    public function allowedRiskOwners(?string $includeCurrent = null): array
    {
        if (!$this->isDivisionVerified()) {
            return [];
        }

        if ($this->isSuperadmin() || $this->isComplianceDivision()) {
            return RiskOwnerAccess::allowedRiskOwnersForDivision('CO - COMPLIANCE', $includeCurrent);
        }

        $allowedOwners = $this->ownRiskOwnerOptions();

        if (!is_null($includeCurrent) && $this->isOwnRiskOwner($includeCurrent)) {
            $allowedOwners[] = $includeCurrent;
        }

        return $this->uniqueOwners($allowedOwners);
    }

    public function canManageRiskOwner(?string $riskOwner): bool
    {
        if (!$this->isDivisionVerified()) {
            return false;
        }

        if ($this->isSuperadmin() || $this->isComplianceDivision()) {
            return true;
        }

        return $this->isOwnRiskOwner($riskOwner);
    }

    public function isComplianceDivision(): bool
    {
        return RiskOwnerAccess::isCompliance($this->division);
    }

    public function mustChangePassword(): bool
    {
        return (bool) $this->force_password_change;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at) && !empty($this->two_factor_secret);
    }

    /**
     * Email one-time codes apply only when the feature is on, the user has no
     * authenticator app, and either the policy enforces it or the user opted in.
     */
    public function usesEmailOtp(): bool
    {
        if (!config('mfa.email_otp.enabled') || $this->hasTwoFactorEnabled() || trim((string) $this->email) === '') {
            return false;
        }

        return (bool) config('mfa.email_otp.enforced') || !is_null($this->email_otp_enabled_at);
    }

    public function requiresMfaChallenge(): bool
    {
        return $this->hasTwoFactorEnabled() || $this->usesEmailOtp();
    }

    public function dashboardPreference()
    {
        return $this->hasOne(UserDashboardPreference::class, 'user_id');
    }

    public function organizationalUnit()
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function roleAssignments()
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['organizational_unit_id', 'assigned_by', 'expires_at'])
            ->withTimestamps();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        return $this->roleAssignments()
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereHas('role.permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    public function hasRole(string $role): bool
    {
        return $this->roleAssignments()
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereHas('role', function ($query) use ($role) {
                $query->where('slug', $role);
            })
            ->exists();
    }

    public function hasExplicitRoles(): bool
    {
        return $this->roleAssignments()
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function hasRoleAssignments(): bool
    {
        return $this->roleAssignments()->exists();
    }

    private function ownRiskOwnerOptions(): array
    {
        $canonicalDivision = RiskOwnerAccess::canonicalOption($this->division);
        if (!is_null($canonicalDivision)) {
            return [$canonicalDivision];
        }

        $fallbackDivision = trim((string) $this->division);

        return $fallbackDivision === '' ? [] : [$fallbackDivision];
    }

    private function isOwnRiskOwner(?string $riskOwner): bool
    {
        $riskOwner = trim((string) $riskOwner);
        if ($riskOwner === '') {
            return false;
        }

        $divisionCanonical = RiskOwnerAccess::canonicalOption($this->division);
        $ownerCanonical = RiskOwnerAccess::canonicalOption($riskOwner);

        if (!is_null($divisionCanonical) && !is_null($ownerCanonical)) {
            return $this->normalizeOwner($divisionCanonical) === $this->normalizeOwner($ownerCanonical);
        }

        $divisionLabel = RiskOwnerAccess::normalizedLabel($this->division);
        $ownerLabel = RiskOwnerAccess::normalizedLabel($riskOwner);

        if ($divisionLabel !== '' && $ownerLabel !== '') {
            return $divisionLabel === $ownerLabel;
        }

        return $this->normalizeOwner($this->division) === $this->normalizeOwner($riskOwner);
    }

    private function uniqueOwners(array $owners): array
    {
        $result = [];
        $seen = [];

        foreach ($owners as $owner) {
            $owner = trim((string) $owner);
            if ($owner === '') {
                continue;
            }

            $normalized = $this->normalizeOwner($owner);
            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $result[] = $owner;
        }

        return $result;
    }

    private function normalizeOwner(?string $value): string
    {
        if (is_null($value)) {
            return '';
        }

        $value = strtoupper(trim((string) $value));
        $value = str_replace(['&', '/', '\\', '|'], ' ', $value);
        $value = (string) preg_replace('/[\.,:;()\[\]{}]+/', ' ', $value);
        $value = (string) preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }
}
