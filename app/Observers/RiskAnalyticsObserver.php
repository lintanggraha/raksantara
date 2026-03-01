<?php

namespace App\Observers;

use App\Support\RiskAnalyticsCacheVersion;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class RiskAnalyticsObserver implements ShouldHandleEventsAfterCommit
{
    public function created($model): void
    {
        RiskAnalyticsCacheVersion::bump();
    }

    public function updated($model): void
    {
        RiskAnalyticsCacheVersion::bump();
    }

    public function deleted($model): void
    {
        RiskAnalyticsCacheVersion::bump();
    }

    public function restored($model): void
    {
        RiskAnalyticsCacheVersion::bump();
    }

    public function forceDeleted($model): void
    {
        RiskAnalyticsCacheVersion::bump();
    }
}
