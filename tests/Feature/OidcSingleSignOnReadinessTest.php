<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class OidcSingleSignOnReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sso_routes_fail_closed_when_not_enabled(): void
    {
        config()->set('sso.oidc.enabled', false);

        $this->get(route('sso.oidc.redirect'))->assertNotFound();
    }

    public function test_enabled_sso_redirect_generates_state_and_nonce(): void
    {
        config()->set('sso.oidc', array_merge(config('sso.oidc'), [
            'enabled' => true,
            'discovery_url' => 'https://identity.example.test/.well-known/openid-configuration',
            'client_id' => 'risk-management',
            'client_secret' => 'secret',
            'redirect_uri' => 'https://risk.example.test/sso/oidc/callback',
        ]));
        Http::fake([
            'identity.example.test/*' => Http::response([
                'authorization_endpoint' => 'https://identity.example.test/authorize',
                'token_endpoint' => 'https://identity.example.test/token',
                'userinfo_endpoint' => 'https://identity.example.test/userinfo',
            ], 200),
        ]);

        $this->get(route('sso.oidc.redirect'))
            ->assertRedirectContains('https://identity.example.test/authorize?');

        $this->assertNotEmpty(session('oidc_state'));
        $this->assertNotEmpty(session('oidc_nonce'));
    }

    public function test_callback_rejects_invalid_state_before_token_exchange(): void
    {
        config()->set('sso.oidc', array_merge(config('sso.oidc'), [
            'enabled' => true,
            'discovery_url' => 'https://identity.example.test/.well-known/openid-configuration',
            'client_id' => 'risk-management',
            'client_secret' => 'secret',
            'redirect_uri' => 'https://risk.example.test/sso/oidc/callback',
        ]));

        $this->withSession(['oidc_state' => 'expected-state'])
            ->get(route('sso.oidc.callback', ['state' => 'wrong-state', 'code' => 'authorization-code']))
            ->assertForbidden();
    }

    public function test_login_page_shows_sso_button_only_when_enabled(): void
    {
        config()->set('sso.oidc.enabled', false);
        $this->get(route('login'))->assertOk()->assertDontSee('data-sso-login', false);

        $this->enableSso();
        $this->get(route('login'))->assertOk()->assertSee('data-sso-login', false);
    }

    public function test_unreachable_provider_falls_back_to_local_login(): void
    {
        $this->enableSso();
        Http::fake(['identity.example.test/*' => Http::response('down', 503)]);

        $this->get(route('sso.oidc.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');
    }

    public function test_incomplete_configuration_falls_back_to_local_login(): void
    {
        $this->enableSso(['client_secret' => null]);

        $this->get(route('sso.oidc.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');
    }

    public function test_failed_token_exchange_falls_back_to_local_login(): void
    {
        $this->enableSso();
        $this->fakeProvider(tokenStatus: 500);

        $this->ssoCallback()
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');
        $this->assertGuest();
    }

    public function test_unverified_email_is_rejected(): void
    {
        $this->enableSso();
        $user = $this->createUser('person@example.test');
        $this->fakeProvider(['sub' => 'subject-1', 'email' => $user->email, 'email_verified' => false]);

        $this->ssoCallback()
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');
        $this->assertGuest();
    }

    public function test_first_login_links_by_email_and_binds_subject(): void
    {
        $this->enableSso();
        $user = $this->createUser('person@example.test');
        $this->fakeProvider(['sub' => 'subject-1', 'email' => $user->email, 'email_verified' => true]);

        $this->ssoCallback()->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('subject-1', $user->fresh()->oidc_subject);
    }

    public function test_account_bound_to_another_subject_cannot_be_taken_over_by_email(): void
    {
        $this->enableSso();
        $user = $this->createUser('person@example.test');
        $user->forceFill(['oidc_subject' => 'original-subject'])->save();
        $this->fakeProvider(['sub' => 'attacker-subject', 'email' => $user->email, 'email_verified' => true]);

        $this->ssoCallback()
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');
        $this->assertGuest();
        $this->assertSame('original-subject', $user->fresh()->oidc_subject);
    }

    public function test_auto_provisioned_user_waits_for_division_approval(): void
    {
        $this->enableSso(['auto_provision' => true]);
        $this->fakeProvider([
            'sub' => 'new-subject',
            'email' => 'newcomer@example.test',
            'email_verified' => true,
            'name' => 'New Comer',
            'department' => 'CO - COMPLIANCE',
        ]);

        $this->ssoCallback()->assertRedirect();

        $user = User::query()->where('email', 'newcomer@example.test')->firstOrFail();
        $this->assertNull($user->division);
        $this->assertNull($user->division_verified_at);
        $this->assertSame('CO - COMPLIANCE', $user->pending_division);
        $this->assertSame('new-subject', $user->oidc_subject);
        $this->assertFalse($user->isDivisionVerified());
    }

    private function enableSso(array $overrides = []): void
    {
        config()->set('sso.oidc', array_merge(config('sso.oidc'), [
            'enabled' => true,
            'discovery_url' => 'https://identity.example.test/.well-known/openid-configuration',
            'client_id' => 'risk-management',
            'client_secret' => 'secret',
            'redirect_uri' => 'https://risk.example.test/sso/oidc/callback',
            'require_verified_email' => true,
            'auto_provision' => false,
        ], $overrides));
    }

    private function fakeProvider(array $claims = [], int $tokenStatus = 200): void
    {
        Http::fake([
            'identity.example.test/.well-known/*' => Http::response([
                'authorization_endpoint' => 'https://identity.example.test/authorize',
                'token_endpoint' => 'https://identity.example.test/token',
                'userinfo_endpoint' => 'https://identity.example.test/userinfo',
            ], 200),
            'identity.example.test/token' => Http::response(['access_token' => 'access-token'], $tokenStatus),
            'identity.example.test/userinfo' => Http::response($claims, 200),
        ]);
    }

    private function ssoCallback(): \Illuminate\Testing\TestResponse
    {
        return $this->withSession(['oidc_state' => 'expected-state'])
            ->get(route('sso.oidc.callback', ['state' => 'expected-state', 'code' => 'authorization-code']));
    }

    private function createUser(string $email): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'SSO User ' . $suffix,
            'username' => 'sso_' . $suffix,
            'division' => 'CO - COMPLIANCE',
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);
    }
}
