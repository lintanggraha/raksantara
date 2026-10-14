<?php

namespace App\Http\Middleware;

use Closure;

class EnsureSuperadmin
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

        if ($user && method_exists($user, 'isSuperadmin') && $user->isSuperadmin()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Superadmin access required.');
        }

        abort(403, 'Superadmin access required.');
    }
}

