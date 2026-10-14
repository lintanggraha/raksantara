<?php

namespace App\Http\Middleware;

use Closure;

class EnsureComplianceDivision
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isComplianceDivision') && $user->isComplianceDivision()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Unauthorized.');
        }

        return redirect()->route('risks.index')->with('error', 'Menu Compliance hanya untuk divisi Compliance.');
    }
}
