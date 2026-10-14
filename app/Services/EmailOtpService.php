<?php

namespace App\Services;

use App\Mail\EmailOtpCode;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EmailOtpService
{
    public const SENT = 'sent';
    public const COOLDOWN = 'cooldown';
    public const FAILED = 'failed';

    /**
     * Issue a fresh code and email it. Only a keyed hash of the code is kept,
     * in the cache, for the configured lifetime.
     *
     * @return array{status: string, retry_after: int}
     */
    public function send(User $user): array
    {
        $retryAfter = $this->secondsUntilResend($user);
        if ($retryAfter > 0) {
            return ['status' => self::COOLDOWN, 'retry_after' => $retryAfter];
        }

        $length = max(4, (int) config('mfa.email_otp.code_length', 6));
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
        $ttlMinutes = max(1, (int) config('mfa.email_otp.ttl_minutes', 10));

        $this->store($user, [
            'hash' => $this->hash($user, $code),
            'attempts' => 0,
            'sent_at' => now()->getTimestamp(),
            'expires_at' => now()->addMinutes($ttlMinutes)->getTimestamp(),
        ]);

        try {
            Mail::to($user->email)->send(new EmailOtpCode($code, $ttlMinutes));
        } catch (\Throwable $exception) {
            Cache::forget($this->key($user));
            report($exception);

            return ['status' => self::FAILED, 'retry_after' => 0];
        }

        return ['status' => self::SENT, 'retry_after' => 0];
    }

    public function hasActiveCode(User $user): bool
    {
        $entry = Cache::get($this->key($user));

        return is_array($entry) && $entry['expires_at'] > now()->getTimestamp();
    }

    /**
     * Single use: a correct code is consumed, and too many wrong guesses
     * discard the code so a new one must be requested.
     */
    public function verify(User $user, string $code): bool
    {
        $entry = Cache::get($this->key($user));
        if (!is_array($entry) || $entry['expires_at'] <= now()->getTimestamp()) {
            Cache::forget($this->key($user));

            return false;
        }

        $code = preg_replace('/\s+/', '', $code) ?? '';
        if (preg_match('/^\d{4,10}$/', $code) === 1 && hash_equals($entry['hash'], $this->hash($user, $code))) {
            Cache::forget($this->key($user));

            return true;
        }

        $entry['attempts']++;
        if ($entry['attempts'] >= max(1, (int) config('mfa.email_otp.max_attempts', 5))) {
            Cache::forget($this->key($user));
        } else {
            $this->store($user, $entry);
        }

        return false;
    }

    public function secondsUntilResend(User $user): int
    {
        $entry = Cache::get($this->key($user));
        if (!is_array($entry)) {
            return 0;
        }

        $cooldown = max(0, (int) config('mfa.email_otp.resend_cooldown_seconds', 60));

        return max(0, ($entry['sent_at'] + $cooldown) - now()->getTimestamp());
    }

    public function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($local, 0, min(2, max(1, mb_strlen($local) - 1)));

        return $visible . str_repeat('*', max(3, mb_strlen($local) - mb_strlen($visible))) . ($domain !== '' ? '@' . $domain : '');
    }

    private function store(User $user, array $entry): void
    {
        Cache::put($this->key($user), $entry, max(1, $entry['expires_at'] - now()->getTimestamp()));
    }

    private function key(User $user): string
    {
        return 'mfa:email-otp:' . $user->getKey();
    }

    private function hash(User $user, string $code): string
    {
        return hash_hmac('sha256', $user->getKey() . '|' . $code, (string) config('app.key'));
    }
}
