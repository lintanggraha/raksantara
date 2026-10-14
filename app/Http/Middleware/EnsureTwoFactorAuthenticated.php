<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactorAuthenticated
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (is_null($user) || !method_exists($user, 'requiresMfaChallenge') || !$user->requiresMfaChallenge()) {
            return $next($request);
        }

        if ((int) $request->session()->get('mfa_authenticated_user') === (int) $user->id) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Two-factor authentication challenge is required.');
        }

        // A session can lose its MFA proof (remember-me login, or email OTP
        // being enforced after sign-in). Hand it back to the challenge flow.
        $request->session()->put('mfa_user_id', $user->id);
        Auth::guard()->logout();

        return redirect()->route('two-factor.challenge');
    }
}
