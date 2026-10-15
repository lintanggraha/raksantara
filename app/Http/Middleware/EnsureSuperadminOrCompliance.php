<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Administration that the Compliance division (the application owner) shares
 * with superadmins, such as risk parameters and risk status approvals.
 */
class EnsureSuperadminOrCompliance
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if ($user && ($user->isSuperadmin() || $user->isComplianceDivision())) {
            return $next($request);
        }

        abort(403, 'Halaman ini hanya untuk superadmin dan divisi Compliance.');
    }
}
