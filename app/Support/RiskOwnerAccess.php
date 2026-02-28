<?php

namespace App\Support;

class RiskOwnerAccess
{
    private const COMPLIANCE_LABEL = 'COMPLIANCE';

    private const SUPPLEMENTAL_OWNER_RULES = [
        'BANKING APPLICATIONS' => [
            'CORE BANKING SYSTEM CONVENTIONAL',
            'CORE BANKING SYSTEM SYARIAH',
            'REGULATORY & FINANCIAL REPORTING',
            'QA & INNOVATION',
        ],
        'DIGITAL SOLUTION' => [
            'BUSINESS & DIGITAL OPERATION',
            'DIGITAL BANKING',
            'SURROUNDING APPLICATION',
            'SWITCHING & MIDDLEWARE',
        ],
        'DATA CENTER OPERATION' => [
            'INFRASTRUCTURE',
            'IT OPERATION MANAGEMENT',
            'RESEARCH & INNOVATION',
        ],
    ];

    private const DISPLAY_LABEL_ALIASES = [
        'BUSINESS STRATEGY' => 'Business Strategy',
    ];

    public static function options(): array
    {
        $options = config('risk_owners.options', []);

        return self::uniqueByNormalizedFull($options);
    }

    public static function isCompliance(?string $division): bool
    {
        if (self::normalizedLabel($division) === self::COMPLIANCE_LABEL) {
            return true;
        }

        $canonical = self::canonicalOption($division);

        return !is_null($canonical) && self::normalizedLabel($canonical) === self::COMPLIANCE_LABEL;
    }

    public static function allowedRiskOwnersForDivision(?string $division, ?string $includeCurrent = null): array
    {
        $options = self::options();

        if (self::isCompliance($division)) {
            return self::withIncludedOwner($options, $includeCurrent);
        }

        $canonicalDivision = self::canonicalOption($division);
        if (is_null($canonicalDivision)) {
            return self::withIncludedOwner([], $includeCurrent);
        }

        $allowed = [$canonicalDivision];
        $normalizedDivisionLabel = self::normalizedLabel($canonicalDivision);

        foreach (self::SUPPLEMENTAL_OWNER_RULES as $supplementalOwnerLabel => $baseDivisionLabels) {
            if (!in_array($normalizedDivisionLabel, $baseDivisionLabels, true)) {
                continue;
            }

            $supplementalOwner = self::canonicalOption($supplementalOwnerLabel);
            if (!is_null($supplementalOwner)) {
                $allowed[] = $supplementalOwner;
            }
        }

        return self::withIncludedOwner($allowed, $includeCurrent);
    }

    public static function canManageRiskOwner(?string $division, ?string $riskOwner): bool
    {
        if (self::isCompliance($division)) {
            return true;
        }

        $candidateRiskOwner = self::normalize($riskOwner);
        $candidateLabel = self::normalizedLabel($riskOwner);

        if ($candidateRiskOwner === '') {
            return false;
        }

        foreach (self::allowedRiskOwnersForDivision($division) as $allowedOwner) {
            if (self::normalize($allowedOwner) === $candidateRiskOwner) {
                return true;
            }

            if (self::normalizedLabel($allowedOwner) === $candidateLabel && $candidateLabel !== '') {
                return true;
            }
        }

        return false;
    }

    public static function canonicalOption(?string $value): ?string
    {
        $normalizedFull = self::normalize($value);
        $normalizedLabel = self::normalizedLabel($value);

        if ($normalizedFull === '' && $normalizedLabel === '') {
            return null;
        }

        foreach (self::options() as $option) {
            if (self::normalize($option) === $normalizedFull) {
                return $option;
            }

            if ($normalizedLabel !== '' && self::normalizedLabel($option) === $normalizedLabel) {
                return $option;
            }
        }

        return null;
    }

    public static function normalizedLabel(?string $value): string
    {
        if (is_null($value)) {
            return '';
        }

        $withoutPrefix = self::stripLabelPrefix((string) $value);

        return self::normalize($withoutPrefix);
    }

    public static function displayLabel(?string $value, string $fallback = '-'): string
    {
        $normalizedLabel = self::normalizedLabel($value);
        if ($normalizedLabel === '') {
            return $fallback;
        }

        if (isset(self::DISPLAY_LABEL_ALIASES[$normalizedLabel])) {
            return self::DISPLAY_LABEL_ALIASES[$normalizedLabel];
        }

        $canonical = self::canonicalOption($value);
        $source = !is_null($canonical) ? $canonical : (string) $value;
        $label = self::stripLabelPrefix($source);

        return $label !== '' ? $label : $fallback;
    }

    private static function stripLabelPrefix(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = (string) preg_replace('/^[A-Z]{2}\s*-\s*/i', '', $value);
        $value = (string) preg_replace('/^SPV\b[\s:,-]*/i', '', $value);
        $value = (string) preg_replace('/^[^A-Z0-9]+/i', '', $value);
        $value = (string) preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private static function normalize(?string $value): string
    {
        if (is_null($value)) {
            return '';
        }

        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }

    private static function withIncludedOwner(array $owners, ?string $includeCurrent = null): array
    {
        if (!is_null($includeCurrent) && trim($includeCurrent) !== '') {
            $owners[] = $includeCurrent;
        }

        return self::uniqueByNormalizedFull($owners);
    }

    private static function uniqueByNormalizedFull(array $owners): array
    {
        $result = [];
        $seen = [];

        foreach ($owners as $owner) {
            $normalized = self::normalize((string) $owner);
            if ($normalized === '' || isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $result[] = (string) $owner;
        }

        return array_values($result);
    }
}
