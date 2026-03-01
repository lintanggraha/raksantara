<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class RiskAnalyticsCacheVersion
{
    private const CACHE_KEY = 'risk:analytics:version';
    private const DEFAULT_VERSION = 1;

    public static function current(): int
    {
        $version = Cache::get(self::CACHE_KEY);

        if (!is_numeric($version) || (int) $version < self::DEFAULT_VERSION) {
            Cache::forever(self::CACHE_KEY, self::DEFAULT_VERSION);

            return self::DEFAULT_VERSION;
        }

        return (int) $version;
    }

    public static function bump(): int
    {
        $nextVersion = self::current() + 1;

        Cache::forever(self::CACHE_KEY, $nextVersion);

        return $nextVersion;
    }
}
