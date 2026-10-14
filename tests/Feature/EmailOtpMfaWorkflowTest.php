<?php

namespace Tests\Feature;

use App\Mail\EmailOtpCode;
use App\Services\TwoFactorAuthenticationService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailOtpMfaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_disabled_feature_never_challenges_even_for_opted_in_users(): void
    {
        $this->configureEmailOtp(enabled: false, enforced: true);
        $user = $this->createUser(['email_otp_enabled_at' => now()]);

        $this->post(route('login'), ['username' => $user->username, 'password' => 'password123'])
            ->assertRedirect(route('management.dashboard'));

        $this->assertAuthenticatedAs($user);
        Mail::assertNothingSent();
    }

    public function test_enforced_email_otp_challenges_login_and_accepts_the_emailed_code(): void
    {
        $this->configureEmailOtp(enabled: true, enforced: true);
        $user = $this->createUser();

        $this->post(route('login'), ['username' => $user->username, 'password' => 'password123'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $this->get(route('two-factor.challenge'))
            ->assertOk()
            ->assertSee('Kode dari Email');
        $code = $this->lastEmailedCode();

        $this->post(route('two-factor.challenge.verify'), ['code' => $code])
            ->assertRedirect(\App\Providers\RouteServiceProvider::HOME);

        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->id, session('mfa_authenticated_user'));
    }

    public function test_email_code_is_single_use_and_discarded_after_too_many_wrong_attempts(): void
    {
        $this->configureEmailOtp(enabled: true, enforced: true, maxAttempts: 3);
        $user = $this->createUser();

        $this->withSession(['mfa_user_id' => $user->id])->get(route('two-factor.challenge'))->assertOk();
        $code = $this->lastEmailedCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        foreach (range(1, 3) as $attempt) {
            $this->post(route('two-factor.challenge.verify'), ['code' => $wrong])
                ->assertSessionHasErrors('code');
        }

        $this->post(route('two-factor.challenge.verify'), ['code' => $code])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_resend_respects_cooldown(): void
    {
        $this->configureEmailOtp(enabled: true, enforced: true);
        $user = $this->createUser();

        $this->withSession(['mfa_user_id' => $user->id])->get(route('two-factor.challenge'))->assertOk();
        $this->post(route('two-factor.challenge.resend'))
            ->assertRedirect(route('two-factor.challenge'))
            ->assertSessionHas('error');

        Mail::assertSent(EmailOtpCode::class, 1);

        $this->travel(61)->seconds();
        $this->post(route('two-factor.challenge.resend'))
            ->assertSessionHas('success');

        Mail::assertSent(EmailOtpCode::class, 2);
    }

    public function test_user_can_opt_in_to_email_otp_when_not_enforced(): void
    {
        $this->configureEmailOtp(enabled: true, enforced: false);
        $user = $this->createUser();

        $this->post(route('login'), ['username' => $user->username, 'password' => 'password123'])
            ->assertRedirect(route('management.dashboard'));

        $this->get(route('two-factor.settings'))->assertOk()->assertSee('Kirim Kode Verifikasi');
        $this->post(route('two-factor.email-otp.send'))->assertSessionHas('success');
        $this->post(route('two-factor.email-otp.enable'), ['code' => $this->lastEmailedCode()])
            ->assertRedirect(route('two-factor.settings'));

        $this->assertNotNull($user->fresh()->email_otp_enabled_at);
        $this->get(route('profile.index'))->assertOk();

        $this->post(route('logout'));
        $this->post(route('login'), ['username' => $user->username, 'password' => 'password123'])
            ->assertRedirect(route('two-factor.challenge'));
    }

    public function test_enforced_policy_blocks_disabling_email_otp(): void
    {
        $this->configureEmailOtp(enabled: true, enforced: true);
        $user = $this->createUser(['email_otp_enabled_at' => now()]);

        $this->actingAs($user)
            ->withSession(['mfa_authenticated_user' => $user->id])
            ->delete(route('two-factor.email-otp.disable'), ['code' => '123456'])
            ->assertStatus(422);
    }

    public function test_authenticator_app_takes_precedence_over_email_otp(): void
    {
        $this->configureEmailOtp(enabled: true, enforced: true);
        $service = app(TwoFactorAuthenticationService::class);
        $secret = $service->generateSecret();
        $user = $this->createUser();
        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode([])),
        ])->save();

        $this->withSession(['mfa_user_id' => $user->id])
            ->get(route('two-factor.challenge'))
            ->assertOk()
            ->assertSee('Authenticator or Recovery Code');

        Mail::assertNothingSent();
    }

    public function test_session_without_mfa_proof_is_sent_to_challenge_when_policy_is_enforced(): void
    {
        $this->configureEmailOtp(enabled: true, enforced: true);
        $user = $this->createUser();

        $this->actingAs($user)
            ->get(route('profile.index'))
            ->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->assertSame($user->id, session('mfa_user_id'));
    }

    private function configureEmailOtp(bool $enabled, bool $enforced, int $maxAttempts = 5): void
    {
        config()->set('mfa.email_otp', array_merge(config('mfa.email_otp'), [
            'enabled' => $enabled,
            'enforced' => $enforced,
            'max_attempts' => $maxAttempts,
            'resend_cooldown_seconds' => 60,
        ]));
    }

    private function lastEmailedCode(): string
    {
        $code = null;
        Mail::assertSent(EmailOtpCode::class, function (EmailOtpCode $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return (string) $code;
    }

    private function createUser(array $overrides = []): User
    {
        $suffix = Str::lower(Str::random(8));
        $user = User::create([
            'name' => 'OTP User ' . $suffix,
            'username' => 'otp_' . $suffix,
            'division' => 'CO - COMPLIANCE',
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
        if ($overrides !== []) {
            $user->forceFill($overrides)->save();
        }

        return $user;
    }
}
