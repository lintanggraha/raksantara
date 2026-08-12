<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OidcSingleSignOnController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $config = $this->enabledConfig();
        $metadata = $this->metadata($config);
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
        $request->validate(['code' => ['required', 'string', 'max:4096']]);

        $metadata = $this->metadata($config);
        abort_unless(!empty($metadata['token_endpoint']) && !empty($metadata['userinfo_endpoint']), 503, 'SSO provider metadata is incomplete.');

        $tokenResponse = Http::asForm()->timeout(10)->post($metadata['token_endpoint'], [
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => $config['redirect_uri'],
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);
        abort_unless($tokenResponse->successful() && !empty($tokenResponse->json('access_token')), 401, 'SSO code exchange failed.');

        $profileResponse = Http::withToken((string) $tokenResponse->json('access_token'))->timeout(10)->get($metadata['userinfo_endpoint']);
        abort_unless($profileResponse->successful(), 401, 'SSO user profile retrieval failed.');
        $claims = $profileResponse->json();

        $email = $this->claim($claims, $config['claim_map']['email']);
        $subject = $this->claim($claims, $config['claim_map']['subject']);
        abort_unless(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($subject), 403, 'SSO provider did not supply the required identity claims.');
        $this->assertAllowedDomain($email, $config['allowed_email_domains']);

        $user = User::query()->where('email', $email)->first();
        if (is_null($user) && $config['auto_provision']) {
            $user = $this->provisionUser($claims, $config, $email);
        }
        abort_unless(!is_null($user), 403, 'No approved local account is linked to this SSO identity.');

        Auth::login($user);
        $request->session()->regenerate();
        AuditLogger::logModelEvent('security.sso.login', $user, null, ['subject' => hash('sha256', $subject)]);

        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();
            $request->session()->put('mfa_user_id', $user->id);

            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }

    private function enabledConfig(): array
    {
        $config = config('sso.oidc');
        abort_unless((bool) ($config['enabled'] ?? false), 404);
        abort_unless(!empty($config['discovery_url']) && !empty($config['client_id']) && !empty($config['client_secret']) && !empty($config['redirect_uri']), 503, 'SSO is enabled but incomplete.');

        foreach (['discovery_url', 'redirect_uri'] as $key) {
            abort_unless(Str::startsWith((string) $config[$key], 'https://') || app()->environment('local', 'testing'), 503, 'SSO requires HTTPS endpoints outside local or test environments.');
        }

        return $config;
    }

    private function metadata(array $config): array
    {
        return Cache::remember('sso:oidc:metadata:' . sha1((string) $config['discovery_url']), now()->addHour(), function () use ($config) {
            $response = Http::timeout(10)->acceptJson()->get($config['discovery_url']);
            abort_unless($response->successful(), 503, 'Unable to retrieve SSO provider metadata.');
            $metadata = $response->json();
            abort_unless(is_array($metadata) && !empty($metadata['authorization_endpoint']), 503, 'Invalid SSO provider metadata.');

            return $metadata;
        });
    }

    private function claim(array $claims, string $key): ?string
    {
        $value = data_get($claims, $key);

        return is_scalar($value) ? trim((string) $value) : null;
    }

    private function assertAllowedDomain(string $email, array $domains): void
    {
        if (empty($domains)) {
            return;
        }

        $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));
        $allowed = array_map('strtolower', $domains);
        abort_unless(in_array($domain, $allowed, true), 403, 'This SSO email domain is not allowed.');
    }

    private function provisionUser(array $claims, array $config, string $email): User
    {
        $username = $this->claim($claims, $config['claim_map']['username']) ?: Str::before($email, '@');
        $username = Str::limit(preg_replace('/[^A-Za-z0-9_.-]/', '_', $username) ?? 'sso_user', 50, '');
        $division = $this->claim($claims, $config['claim_map']['division']);

        return User::create([
            'name' => $this->claim($claims, $config['claim_map']['name']) ?: $username,
            'username' => $this->uniqueUsername($username),
            'email' => $email,
            'password' => bcrypt(Str::random(64)),
            'division' => $division,
            'division_verified_at' => empty($division) ? null : now(),
            'force_password_change' => false,
        ]);
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
