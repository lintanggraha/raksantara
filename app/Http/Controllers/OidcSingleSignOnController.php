<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use App\Support\RiskOwnerAccess;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OpenID Connect sign-in. Disabled unless SSO_OIDC_ENABLED=true; whenever the
 * provider is misconfigured, unreachable or rejects the identity, the user is
 * sent back to the local username/password login (the fallback path).
 */
class OidcSingleSignOnController extends Controller
{
    private const FALLBACK_MESSAGE = 'Login SSO sedang tidak tersedia. Silakan masuk dengan username dan password.';

    public function redirect(Request $request): RedirectResponse
    {
        $config = $this->enabledConfig();
        if (!$this->isConfigComplete($config)) {
            return $this->fallback('config_incomplete');
        }

        $metadata = $this->metadata($config);
        if (is_null($metadata)) {
            return $this->fallback('metadata_unavailable');
        }

        $state = Str::random(64);
        $nonce = Str::random(64);
        $request->session()->put('oidc_state', $state);
        $request->session()->put('oidc_nonce', $nonce);

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', $config['scopes']),
            'state' => $state,
            'nonce' => $nonce,
        ]);

        return redirect()->away($metadata['authorization_endpoint'] . '?' . $query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $config = $this->enabledConfig();
        $state = (string) $request->session()->pull('oidc_state');
        abort_unless(!empty($state) && hash_equals($state, (string) $request->input('state')), 403, 'Invalid SSO state.');

        if ($request->filled('error')) {
            return $this->fallback('provider_error', 'Login SSO dibatalkan atau ditolak oleh penyedia identitas.');
        }
        $request->validate(['code' => ['required', 'string', 'max:4096']]);

        if (!$this->isConfigComplete($config)) {
            return $this->fallback('config_incomplete');
        }

        $metadata = $this->metadata($config);
        if (is_null($metadata) || empty($metadata['token_endpoint']) || empty($metadata['userinfo_endpoint'])) {
            return $this->fallback('metadata_unavailable');
        }

        $claims = $this->fetchClaims($request, $config, $metadata);
        if (is_null($claims)) {
            return $this->fallback('provider_unavailable');
        }

        $email = $this->claim($claims, $config['claim_map']['email']);
        $subject = $this->claim($claims, $config['claim_map']['subject']);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($subject)) {
            return $this->fallback('missing_claims', 'Penyedia SSO tidak mengirim identitas yang dibutuhkan (email dan subject).');
        }
        if (!$this->isAllowedDomain($email, $config['allowed_email_domains'])) {
            return $this->fallback('domain_not_allowed', 'Domain email ini tidak diizinkan untuk login SSO.');
        }
        if (($config['require_verified_email'] ?? true) && !$this->isTruthy(data_get($claims, $config['email_verified_claim'] ?? 'email_verified'))) {
            return $this->fallback('email_unverified', 'Email akun SSO belum diverifikasi oleh penyedia identitas.');
        }

        $user = $this->resolveUser($claims, $config, $email, $subject);
        if (is_null($user)) {
            return $this->fallback('no_linked_account', 'Akun SSO ini belum terhubung ke akun aplikasi. Hubungi administrator atau masuk dengan username dan password.');
        }

        if (empty($user->oidc_subject)) {
            $user->forceFill(['oidc_subject' => $subject])->save();
            AuditLogger::logModelEvent('security.sso.linked', $user, null, ['subject' => hash('sha256', $subject)]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        AuditLogger::logModelEvent('security.sso.login', $user, null, ['subject' => hash('sha256', $subject)]);

        if ($user->requiresMfaChallenge()) {
            Auth::logout();
            $request->session()->put('mfa_user_id', $user->id);

            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }

    /**
     * The subject claim is the stable identity. Email is only used to link an
     * existing account on its first SSO login, and never to re-link an account
     * already bound to a different subject.
     */
    private function resolveUser(array $claims, array $config, string $email, string $subject): ?User
    {
        $user = User::query()->where('oidc_subject', $subject)->first();
        if ($user) {
            return $user;
        }

        $user = User::query()->where('email', $email)->first();
        if ($user) {
            if (!empty($user->oidc_subject) && !hash_equals((string) $user->oidc_subject, $subject)) {
                Log::warning('OIDC login rejected: account is bound to a different subject.', ['user_id' => $user->id]);

                return null;
            }

            return $user;
        }

        return $config['auto_provision'] ? $this->provisionUser($claims, $config, $email) : null;
    }

    private function fetchClaims(Request $request, array $config, array $metadata): ?array
    {
        try {
            $tokenResponse = Http::asForm()->timeout(10)->post($metadata['token_endpoint'], [
                'grant_type' => 'authorization_code',
                'code' => $request->input('code'),
                'redirect_uri' => $config['redirect_uri'],
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
            ]);
            if (!$tokenResponse->successful() || empty($tokenResponse->json('access_token'))) {
                return null;
            }

            $profileResponse = Http::withToken((string) $tokenResponse->json('access_token'))->timeout(10)->get($metadata['userinfo_endpoint']);
            $claims = $profileResponse->successful() ? $profileResponse->json() : null;

            return is_array($claims) ? $claims : null;
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function enabledConfig(): array
    {
        $config = config('sso.oidc');
        abort_unless((bool) ($config['enabled'] ?? false), 404);

        return $config;
    }

    private function isConfigComplete(array $config): bool
    {
        if (empty($config['discovery_url']) || empty($config['client_id']) || empty($config['client_secret']) || empty($config['redirect_uri'])) {
            return false;
        }

        foreach (['discovery_url', 'redirect_uri'] as $key) {
            if (!Str::startsWith((string) $config[$key], 'https://') && !app()->environment('local', 'testing')) {
                return false;
            }
        }

        return true;
    }

    private function metadata(array $config): ?array
    {
        $cacheKey = 'sso:oidc:metadata:' . sha1((string) $config['discovery_url']);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::timeout(10)->acceptJson()->get($config['discovery_url']);
            $metadata = $response->successful() ? $response->json() : null;
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }

        if (!is_array($metadata) || empty($metadata['authorization_endpoint'])) {
            return null;
        }

        // Only a valid document is cached, so an outage is retried on the next attempt.
        Cache::put($cacheKey, $metadata, now()->addHour());

        return $metadata;
    }

    private function fallback(string $reason, string $message = self::FALLBACK_MESSAGE): RedirectResponse
    {
        Log::warning('OIDC sign-in fell back to local login.', ['reason' => $reason]);

        return redirect()->route('login')->withErrors(['sso' => $message]);
    }

    private function claim(array $claims, string $key): ?string
    {
        $value = data_get($claims, $key);

        return is_scalar($value) ? trim((string) $value) : null;
    }

    private function isTruthy(mixed $value): bool
    {
        return $value === true || (is_string($value) && strtolower($value) === 'true') || $value === 1 || $value === '1';
    }

    private function isAllowedDomain(string $email, array $domains): bool
    {
        if (empty($domains)) {
            return true;
        }

        $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));

        return in_array($domain, array_map('strtolower', $domains), true);
    }

    /**
     * New SSO users start unapproved: the division claim is only a request and
     * a superadmin still has to verify it, exactly like self-registration.
     */
    private function provisionUser(array $claims, array $config, string $email): User
    {
        $username = $this->claim($claims, $config['claim_map']['username']) ?: Str::before($email, '@');
        $username = Str::limit(preg_replace('/[^A-Za-z0-9_.-]/', '_', $username) ?? 'sso_user', 50, '');
        $division = $this->claim($claims, $config['claim_map']['division']);
        if (!in_array($division, RiskOwnerAccess::options(), true)) {
            $division = null;
        }

        $user = User::create([
            'name' => $this->claim($claims, $config['claim_map']['name']) ?: $username,
            'username' => $this->uniqueUsername($username),
            'email' => $email,
            'password' => bcrypt(Str::random(64)),
            'division' => null,
            'pending_division' => $division ?: null,
            'division_verified_at' => null,
            'force_password_change' => false,
        ]);
        AuditLogger::logModelEvent('security.sso.provisioned', $user, null, ['requested_division' => $division]);

        return $user;
    }

    private function uniqueUsername(string $base): string
    {
        $candidate = $base;
        $suffix = 1;
        while (User::query()->where('username', $candidate)->exists()) {
            $suffix++;
            $candidate = Str::limit($base, 45, '') . '_' . $suffix;
        }

        return $candidate;
    }
}
