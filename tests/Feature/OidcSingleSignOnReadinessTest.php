<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
}
