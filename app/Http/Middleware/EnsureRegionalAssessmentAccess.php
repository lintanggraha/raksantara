<?php

namespace App\Http\Middleware;

use Closure;

class EnsureRegionalAssessmentAccess
{
    /**
     * Regional investigation evidence (Sulteng, Sumut) is restricted to the
     * Compliance division and superadmins.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if ($user && (
            (method_exists($user, 'isSuperadmin') && $user->isSuperadmin())
            || (method_exists($user, 'isComplianceDivision') && $user->isComplianceDivision())
        )) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Unauthorized.');
        }

        abort(403, 'Monitoring Assessment hanya untuk divisi Compliance.');
    }
}
