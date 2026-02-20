<?php

namespace App\Support;

class InputValidation
{
    /**
     * Allow letters, numbers, spaces, and common business punctuation.
     */
    public const SAFE_TEXT_REGEX = '/^[\pL\pN\s\.,!?\&\/\(\):=\'@_-]*$/u';

    /**
     * Integer-only number (no sign, no scientific notation).
     */
    public const STRICT_INTEGER_REGEX = '/^\d+$/';

    /**
     * Decimal number with dot separator (e.g. 10 or 10.25).
     */
    public const STRICT_DECIMAL_REGEX = '/^\d+(\.\d+)?$/';

    public static function safeTextMessage(): string
    {
        return 'Kolom :attribute hanya boleh berisi huruf, angka, spasi, dan tanda baca umum (.,!?&/():=\'@_-).';
    }

    public static function strictIntegerMessage(): string
    {
        return 'Kolom :attribute harus angka. Boleh pakai pemisah ribuan titik atau koma.';
    }

    public static function strictDecimalMessage(): string
    {
        return 'Kolom :attribute harus angka valid (boleh desimal dengan titik/koma).';
    }

    public static function normalizeGroupedInteger($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        // Already a plain integer.
        if (preg_match('/^\d+$/', $text) === 1) {
            return $text;
        }

        // Supports 1,000 or 1.000 style grouping.
        if (preg_match('/^\d{1,3}(?:[.,]\d{3})+$/', $text) === 1) {
            return str_replace([',', '.'], '', $text);
        }

        return $text;
    }

    public static function normalizeLocalizedDecimal($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        // Supports both decimal separators: 10.5 or 10,5
        if (preg_match('/^\d+(?:[.,]\d+)?$/', $text) === 1) {
            return str_replace(',', '.', $text);
        }

        return $text;
    }
}
