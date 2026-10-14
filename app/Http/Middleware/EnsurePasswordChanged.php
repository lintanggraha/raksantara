<?php

namespace App\Http\Middleware;

use Closure;

class EnsurePasswordChanged
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

        if (is_null($user) || !method_exists($user, 'mustChangePassword') || !$user->mustChangePassword()) {
            return $next($request);
        }

        if ($request->routeIs('change.password') || $request->routeIs('profile.changePassword')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Anda harus mengganti password sementara sebelum melanjutkan.');
        }

        return redirect()
            ->route('change.password')
            ->with('error', 'Anda harus mengganti password sementara sebelum melanjutkan.');
    }
}

