<?php

namespace Tests\Feature;

use App\Services\TwoFactorAuthenticationService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class TwoFactorAuthenticationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_mfa_and_mfa_session_is_enforced(): void
    {
        $user = $this->createUser();
        $service = app(TwoFactorAuthenticationService::class);

        $this->actingAs($user)
            ->post(route('two-factor.setup.begin'))
            ->assertRedirect(route('two-factor.settings'));

        $secret = session('two_factor_pending_secret');
        $this->assertNotEmpty($secret);

        $this->actingAs($user)
            ->post(route('two-factor.setup.confirm'), ['code' => $this->currentCode($service, $secret)])
            ->assertRedirect(route('two-factor.settings'));

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertNotEmpty(Crypt::decryptString($user->two_factor_recovery_codes));

        $this->actingAs($user)
            ->withSession(['mfa_authenticated_user' => 0])
            ->get(route('profile.index'))
            ->assertRedirect(route('two-factor.challenge'));
    }

    public function test_mfa_challenge_accepts_totp_and_establishes_authenticated_session(): void
    {
        $user = $this->createUser();
        $service = app(TwoFactorAuthenticationService::class);
        $secret = $service->generateSecret();
        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($service->hashRecoveryCodes(['ABCDE-12345']))),
        ])->save();

        $this->withSession(['mfa_user_id' => $user->id])
            ->post(route('two-factor.challenge.verify'), ['code' => $this->currentCode($service, $secret)])
            ->assertRedirect('/risks');

        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->id, session('mfa_authenticated_user'));
    }

    public function test_recovery_code_can_only_be_used_once_for_mfa_challenge(): void
    {
        $user = $this->createUser();
        $service = app(TwoFactorAuthenticationService::class);
        $secret = $service->generateSecret();
        $recoveryCode = 'ABCDE-12345';
        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($service->hashRecoveryCodes([$recoveryCode]))),
        ])->save();

        $this->withSession(['mfa_user_id' => $user->id])
            ->post(route('two-factor.challenge.verify'), ['code' => $recoveryCode])
            ->assertRedirect('/risks');

        $this->assertSame([], json_decode(Crypt::decryptString($user->fresh()->two_factor_recovery_codes), true));
    }

    private function currentCode(TwoFactorAuthenticationService $service, string $secret): string
    {
        $method = new ReflectionMethod($service, 'codeAt');
        $method->setAccessible(true);

        return $method->invoke($service, $secret, intdiv(time(), 30));
    }

    private function createUser(): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'MFA User ' . $suffix,
            'username' => 'mfa_' . $suffix,
            'division' => 'BT - DIGITAL BANKING',
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
