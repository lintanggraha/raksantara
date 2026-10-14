<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Business impact analysis rules (ISO 22301 cl. 8.2.2, ISO 22317).
 *
 * Impact is rated per category at increasing outage durations. The maximum
 * tolerable period of disruption (MTPD) is the first duration at which any
 * category reaches an unacceptable level; the RTO must not exceed it, and the
 * service tier follows from it.
 */
class BusinessContinuity
{
    /** Outage durations assessed, in minutes => label. */
    public const HORIZONS = [
        60 => '1 jam',
        240 => '4 jam',
        1440 => '24 jam',
        4320 => '72 jam',
        10080 => '1 minggu',
    ];

    public const CATEGORIES = [
        'financial' => 'Finansial',
        'operational' => 'Operasional',
        'regulatory' => 'Regulasi & hukum',
        'reputational' => 'Reputasi & nasabah',
    ];

    public const IMPACT_LEVELS = [
        1 => 'Rendah',
        2 => 'Sedang',
        3 => 'Tinggi',
        4 => 'Kritis',
    ];

    /** Impact from this level upwards is not acceptable. */
    public const UNACCEPTABLE_LEVEL = 3;

    public const RTO_OPTIONS = [
        30 => '30 menit',
        60 => '1 jam',
        120 => '2 jam',
        240 => '4 jam',
        480 => '8 jam',
        1440 => '24 jam',
        4320 => '72 jam',
        10080 => '1 minggu',
    ];

    public const RPO_OPTIONS = [
        0 => 'Tanpa kehilangan data (0)',
        15 => '15 menit',
        60 => '1 jam',
        240 => '4 jam',
        1440 => '24 jam',
    ];

    /** Tier => [upper MTPD bound in minutes, description]. */
    public const TIERS = [
        'Tier 1' => [240, 'Sangat kritikal: harus pulih dalam 4 jam'],
        'Tier 2' => [1440, 'Kritikal: harus pulih dalam 24 jam'],
        'Tier 3' => [4320, 'Penting: harus pulih dalam 72 jam'],
        'Tier 4' => [null, 'Pendukung: dapat ditunda lebih dari 72 jam'],
    ];

    public const TESTING_FREQUENCIES = [
        'Quarterly' => ['Triwulanan', 3],
        'Semi-Annual' => ['Semesteran', 6],
        'Annual' => ['Tahunan', 12],
    ];

    public const TEST_TYPES = [
        'tabletop' => 'Tabletop exercise',
        'walkthrough' => 'Walkthrough prosedur',
        'simulation' => 'Simulasi',
        'full_dr' => 'Uji DR penuh (failover)',
        'restore' => 'Uji restore data',
    ];

    public const TEST_RESULTS = [
        'pass' => 'Berhasil',
        'partial' => 'Sebagian',
        'fail' => 'Gagal',
    ];

    /**
     * @param array<int|string, array<string, int>> $matrix minutes => [category => level]
     */
    public static function mtpdMinutes(array $matrix): ?int
    {
        foreach (array_keys(self::HORIZONS) as $minutes) {
            foreach (array_keys(self::CATEGORIES) as $category) {
                if ((int) ($matrix[$minutes][$category] ?? 0) >= self::UNACCEPTABLE_LEVEL) {
                    return $minutes;
                }
            }
        }

        return null;
    }

    public static function tierFor(?int $mtpdMinutes): string
    {
        if ($mtpdMinutes !== null) {
            foreach (self::TIERS as $tier => [$bound]) {
                if ($bound !== null && $mtpdMinutes <= $bound) {
                    return $tier;
                }
            }
        }

        return 'Tier 4';
    }

    /**
     * Problems with a submitted matrix, keyed like validation errors.
     *
     * @return array<string, string>
     */
    public static function matrixErrors(array $matrix): array
    {
        $errors = [];
        foreach (array_keys(self::CATEGORIES) as $category) {
            $previous = 0;
            foreach (self::HORIZONS as $minutes => $label) {
                $level = (int) ($matrix[$minutes][$category] ?? 0);
                if (!array_key_exists($level, self::IMPACT_LEVELS)) {
                    $errors["impact.{$minutes}.{$category}"] = 'Pilih tingkat dampak ' . self::CATEGORIES[$category] . ' pada ' . $label . '.';
                    continue;
                }
                if ($level < $previous) {
                    $errors["impact.{$minutes}.{$category}"] = 'Dampak ' . self::CATEGORIES[$category] . ' tidak boleh turun seiring lamanya gangguan (' . $label . ').';
                }
                $previous = max($previous, $level);
            }
        }

        return $errors;
    }

    public static function durationLabel(?int $minutes): string
    {
        if ($minutes === null) {
            return 'Lebih dari 1 minggu';
        }

        return self::HORIZONS[$minutes] ?? self::RTO_OPTIONS[$minutes] ?? self::RPO_OPTIONS[$minutes] ?? ($minutes . ' menit');
    }

    /**
     * Date the next BCP test is due, from the last test (or never tested).
     */
    public static function nextTestDue(?string $frequency, ?CarbonInterface $lastTestedAt): ?CarbonInterface
    {
        if ($frequency === null || !isset(self::TESTING_FREQUENCIES[$frequency]) || $lastTestedAt === null) {
            return null;
        }

        return $lastTestedAt->copy()->addMonthsNoOverflow(self::TESTING_FREQUENCIES[$frequency][1]);
    }
}
