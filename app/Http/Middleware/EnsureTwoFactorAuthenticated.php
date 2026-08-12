<?php

namespace App\Http\Middleware;

use Closure;

class EnsureTwoFactorAuthenticated
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (is_null($user) || !method_exists($user, 'hasTwoFactorEnabled') || !$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        if ((int) $request->session()->get('mfa_authenticated_user') === (int) $user->id) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Two-factor authentication challenge is required.');
        }

        return redirect()->route('two-factor.challenge');
    }
}
