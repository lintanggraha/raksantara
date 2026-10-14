<?php

namespace App\Http\Controllers;

use App\Services\EmailOtpService;
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
    public function show(Request $request, TwoFactorAuthenticationService $service, EmailOtpService $emailOtp): View
    {
        $user = $request->user();
        $pendingSecret = $request->session()->get('two_factor_pending_secret');
        $recoveryCodes = $request->session()->pull('two_factor_recovery_codes_display', []);

        return view('auth.two-factor-settings', [
            'user' => $user,
            'pendingSecret' => $pendingSecret,
            'provisioningUri' => $pendingSecret ? $service->provisioningUri($pendingSecret, $user->username) : null,
            'recoveryCodes' => $recoveryCodes,
            'emailOtpAvailable' => $this->emailOtpAvailableFor($user),
            'emailOtpEnforced' => (bool) config('mfa.email_otp.enforced'),
            'emailOtpActive' => $user->usesEmailOtp(),
            'emailOtpCodeSent' => $emailOtp->hasActiveCode($user),
            'maskedEmail' => $emailOtp->maskEmail((string) $user->email),
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
        // The user just proved a second factor, so keep the session verified
        // when email OTP still applies instead of challenging them right away.
        if (!$user->requiresMfaChallenge()) {
            $request->session()->forget('mfa_authenticated_user');
        }
        AuditLogger::logModelEvent('security.mfa.disabled', $user, null, []);

        return redirect()->route('two-factor.settings')->with('success', 'Two-factor authentication has been disabled.');
    }

    public function sendEmailOtp(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->emailOtpAvailableFor($user), 422, 'Email OTP is not available for this account.');

        return $this->sendResult(redirect()->route('two-factor.settings'), $emailOtp->send($user), $emailOtp->maskEmail((string) $user->email));
    }

    public function enableEmailOtp(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $user = $request->user();
        abort_unless($this->emailOtpAvailableFor($user), 422, 'Email OTP is not available for this account.');

        if (!$emailOtp->verify($user, (string) $request->input('code'))) {
            return redirect()->route('two-factor.settings')->withErrors(['email_otp_code' => 'Kode email tidak valid atau sudah kedaluwarsa.']);
        }

        $user->forceFill(['email_otp_enabled_at' => now()])->save();
        $request->session()->put('mfa_authenticated_user', $user->id);
        AuditLogger::logModelEvent('security.mfa.enabled', $user, null, ['method' => 'email_otp']);

        return redirect()->route('two-factor.settings')->with('success', 'Verifikasi kode email aktif. Kode akan dikirim ke email Anda setiap kali masuk.');
    }

    public function disableEmailOtp(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $user = $request->user();
        abort_if((bool) config('mfa.email_otp.enforced'), 422, 'Email OTP is required by the security policy.');
        abort_if(is_null($user->email_otp_enabled_at), 422, 'Email OTP is not enabled.');

        if (!$emailOtp->verify($user, (string) $request->input('code'))) {
            return redirect()->route('two-factor.settings')->withErrors(['email_otp_code' => 'Kode email tidak valid atau sudah kedaluwarsa.']);
        }

        $user->forceFill(['email_otp_enabled_at' => null])->save();
        AuditLogger::logModelEvent('security.mfa.disabled', $user, null, ['method' => 'email_otp']);

        return redirect()->route('two-factor.settings')->with('success', 'Verifikasi kode email dinonaktifkan.');
    }

    public function challenge(Request $request, EmailOtpService $emailOtp): View|RedirectResponse
    {
        $user = $this->pendingChallengeUser($request);
        if (is_null($user)) {
            return redirect()->route('login');
        }

        $method = $user->hasTwoFactorEnabled() ? 'totp' : 'email';
        if ($method === 'email' && !$emailOtp->hasActiveCode($user)) {
            $result = $emailOtp->send($user);
            if ($result['status'] === EmailOtpService::FAILED) {
                $request->session()->now('error', 'Kode verifikasi gagal dikirim. Coba kirim ulang beberapa saat lagi atau hubungi administrator.');
            }
        }

        return view('auth.two-factor-challenge', [
            'method' => $method,
            'maskedEmail' => $emailOtp->maskEmail((string) $user->email),
        ]);
    }

    public function resendChallenge(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $user = $this->pendingChallengeUser($request);
        if (is_null($user)) {
            return redirect()->route('login');
        }
        abort_unless($user->usesEmailOtp(), 422, 'Email OTP is not active for this account.');

        return $this->sendResult(redirect()->route('two-factor.challenge'), $emailOtp->send($user), $emailOtp->maskEmail((string) $user->email));
    }

    public function verifyChallenge(Request $request, TwoFactorAuthenticationService $service, EmailOtpService $emailOtp): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);
        $userId = $request->session()->pull('mfa_user_id');
        if (empty($userId)) {
            return redirect()->route('login')->withErrors(['username' => 'Your MFA challenge expired. Please sign in again.']);
        }

        $user = User::query()->find($userId);
        $code = (string) $request->input('code');
        $method = null;
        if ($user && $user->hasTwoFactorEnabled()) {
            $method = $this->verifyCode($user, $code, $service) ? 'totp' : null;
        } elseif ($user && $user->usesEmailOtp()) {
            $method = $emailOtp->verify($user, $code) ? 'email_otp' : null;
        }

        if (is_null($method)) {
            $request->session()->put('mfa_user_id', $userId);

            return back()->withErrors(['code' => 'The MFA code is invalid or expired.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('mfa_authenticated_user', $user->id);
        $user->forceFill(['two_factor_last_used_at' => now()])->save();
        AuditLogger::logModelEvent('security.mfa.challenge_passed', $user, null, ['method' => $method]);

        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }

    private function emailOtpAvailableFor(User $user): bool
    {
        return (bool) config('mfa.email_otp.enabled')
            && !$user->hasTwoFactorEnabled()
            && trim((string) $user->email) !== '';
    }

    private function pendingChallengeUser(Request $request): ?User
    {
        $userId = $request->session()->get('mfa_user_id');
        if (empty($userId)) {
            return null;
        }

        $user = User::query()->find($userId);

        return $user && $user->requiresMfaChallenge() ? $user : null;
    }

    /**
     * @param array{status: string, retry_after: int} $result
     */
    private function sendResult(RedirectResponse $redirect, array $result, string $maskedEmail): RedirectResponse
    {
        return match ($result['status']) {
            EmailOtpService::SENT => $redirect->with('success', 'Kode verifikasi telah dikirim ke ' . $maskedEmail . '.'),
            EmailOtpService::COOLDOWN => $redirect->with('error', 'Tunggu ' . $result['retry_after'] . ' detik sebelum meminta kode baru.'),
            default => $redirect->with('error', 'Kode verifikasi gagal dikirim. Coba lagi beberapa saat lagi atau hubungi administrator.'),
        };
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
