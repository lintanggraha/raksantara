<?php

namespace App\Http\Middleware;

use Closure;

class EnsureApprovedUser
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

        if ($user && method_exists($user, 'isDivisionVerified') && $user->isDivisionVerified()) {
            return $next($request);
        }

        if ($request->routeIs('account.pending')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Akun Anda masih menunggu verifikasi superadmin.');
        }

        return redirect()
            ->route('account.pending')
            ->with('error', 'Akun Anda masih menunggu verifikasi superadmin.');
    }
}

