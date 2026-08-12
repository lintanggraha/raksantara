<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthenticationService;
use App\Support\AuditLogger;
use App\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class TwoFactorAuthenticationController extends Controller
{
    public function show(Request $request, TwoFactorAuthenticationService $service): View
    {
        $user = $request->user();
        $pendingSecret = $request->session()->get('two_factor_pending_secret');
        $recoveryCodes = $request->session()->pull('two_factor_recovery_codes_display', []);

        return view('auth.two-factor-settings', [
            'user' => $user,
            'pendingSecret' => $pendingSecret,
            'provisioningUri' => $pendingSecret ? $service->provisioningUri($pendingSecret, $user->username) : null,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    public function beginSetup(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        abort_if($request->user()->hasTwoFactorEnabled(), 422, 'Two-factor authentication is already enabled.');

        $request->session()->put('two_factor_pending_secret', $service->generateSecret());

        return redirect()->route('two-factor.settings')->with('success', 'Add the secret to an authenticator app, then enter a current six-digit code to confirm.');
    }

    public function confirmSetup(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $secret = $request->session()->get('two_factor_pending_secret');
        abort_if(empty($secret), 422, 'Start MFA setup before confirming a code.');

        if (!$service->verify($secret, (string) $request->input('code'))) {
            return back()->withErrors(['code' => 'The authenticator code is invalid or expired.']);
        }

        $recoveryCodes = $service->generateRecoveryCodes();
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($service->hashRecoveryCodes($recoveryCodes))),
        ])->save();

        $request->session()->forget('two_factor_pending_secret');
        $request->session()->put('mfa_authenticated_user', $user->id);
        $request->session()->put('two_factor_recovery_codes_display', $recoveryCodes);
        AuditLogger::logModelEvent('security.mfa.enabled', $user, null, ['method' => 'totp']);

        return redirect()->route('two-factor.settings')->with('success', 'Two-factor authentication is now enabled. Save the recovery codes securely.');
    }

    public function regenerateRecoveryCodes(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 422, 'Enable MFA before managing recovery codes.');
        abort_unless($this->verifyCode($user, (string) $request->input('code'), $service), 422, 'The MFA code is invalid.');

        $recoveryCodes = $service->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($service->hashRecoveryCodes($recoveryCodes))),
        ])->save();
        $request->session()->put('two_factor_recovery_codes_display', $recoveryCodes);
        AuditLogger::logModelEvent('security.mfa.recovery_codes_regenerated', $user, null, []);

        return redirect()->route('two-factor.settings')->with('success', 'New recovery codes generated. Previous recovery codes no longer work.');
    }

    public function disable(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 422, 'MFA is not enabled.');
        abort_unless($this->verifyCode($user, (string) $request->input('code'), $service), 422, 'The MFA code is invalid.');

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_last_used_at' => null,
        ])->save();
        $request->session()->forget('mfa_authenticated_user');
        AuditLogger::logModelEvent('security.mfa.disabled', $user, null, []);

        return redirect()->route('two-factor.settings')->with('success', 'Two-factor authentication has been disabled.');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $userId = $request->session()->pull('mfa_user_id');
        if (empty($userId)) {
            return redirect()->route('login')->withErrors(['username' => 'Your MFA challenge expired. Please sign in again.']);
        }

        $user = User::query()->find($userId);
        if (is_null($user) || !$user->hasTwoFactorEnabled() || !$this->verifyCode($user, (string) $request->input('code'), $service)) {
            $request->session()->put('mfa_user_id', $userId);

            return back()->withErrors(['code' => 'The MFA code is invalid or expired.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('mfa_authenticated_user', $user->id);
        $user->forceFill(['two_factor_last_used_at' => now()])->save();
        AuditLogger::logModelEvent('security.mfa.challenge_passed', $user, null, []);

        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }

    private function verifyCode(User $user, string $code, TwoFactorAuthenticationService $service): bool
    {
        try {
            $secret = Crypt::decryptString((string) $user->two_factor_secret);
            if ($service->verify($secret, $code)) {
                return true;
            }

            $hashedCodes = json_decode(Crypt::decryptString((string) $user->two_factor_recovery_codes), true);
            if (!is_array($hashedCodes)) {
                return false;
            }

            $recovery = $service->consumeRecoveryCode($hashedCodes, $code);
            if (!$recovery['valid']) {
                return false;
            }

            $user->forceFill([
                'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recovery['remaining'])),
                'two_factor_last_used_at' => now(),
            ])->save();
            AuditLogger::logModelEvent('security.mfa.recovery_code_used', $user, null, []);

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
