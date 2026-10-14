<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class TwoFactorAuthenticationService
{
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    public function provisioningUri(string $secret, string $accountName): string
    {
        $issuer = (string) config('app.name', 'Risk Management');
        $label = rawurlencode($issuer . ':' . $accountName);

        return 'otpauth://totp/' . $label . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($index = 0; $index < $count; $index++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 5) . '-' . substr(bin2hex(random_bytes(5)), 0, 5));
        }

        return $codes;
    }

    /**
     * @param array<int, string> $codes
     * @return array<int, string>
     */
    public function hashRecoveryCodes(array $codes): array
    {
        return array_map(fn (string $code) => Hash::make($this->normalizeCode($code)), $codes);
    }

    /**
     * @param array<int, string> $hashedCodes
     * @return array{valid: bool, remaining: array<int, string>}
     */
    public function consumeRecoveryCode(array $hashedCodes, string $code): array
    {
        $normalized = $this->normalizeCode($code);
        foreach ($hashedCodes as $index => $hashedCode) {
            if (Hash::check($normalized, $hashedCode)) {
                unset($hashedCodes[$index]);

                return ['valid' => true, 'remaining' => array_values($hashedCodes)];
            }
        }

        return ['valid' => false, 'remaining' => $hashedCodes];
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (!is_string($code) || preg_match('/^\d{6}$/', $code) !== 1) {
            return false;
        }

        $currentCounter = intdiv(time(), 30);
        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->codeAt($secret, $currentCounter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    private function codeAt(string $secret, int $counter): string
    {
        $binarySecret = $this->base32Decode($secret);
        $binaryCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $binarySecret, true);
        $offset = ord($hash[19]) & 0x0f;
        $value = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    private function base32Encode(string $binary): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($binary) as $character) {
            $bits .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    private function base32Decode(string $value): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $value = strtoupper(preg_replace('/[^A-Z2-7]/', '', $value) ?? '');
        $bits = '';
        foreach (str_split($value) as $character) {
            $position = strpos($alphabet, $character);
            if ($position === false) {
                throw new \InvalidArgumentException('Invalid base32 secret.');
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $binary .= chr(bindec($chunk));
            }
        }

        return $binary;
    }
}
