<?php

namespace Tests\Unit;

use App\Risk;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\Support\RiskMetricService;
use Carbon\Carbon;
use Tests\TestCase;

class RiskMetricServiceTest extends TestCase
{


    public function test_resolve_priority_bucket_thresholds()
    {
        $this->assertNull(RiskMetricService::resolvePriorityBucket(null));
        $this->assertNull(RiskMetricService::resolvePriorityBucket('abc'));

        // Threshold boundary tests
        $this->assertSame(RiskMetricService::PRIORITY_LOW, RiskMetricService::resolvePriorityBucket(1.0));
        $this->assertSame(RiskMetricService::PRIORITY_LOW, RiskMetricService::resolvePriorityBucket(5.0));

        $this->assertSame(RiskMetricService::PRIORITY_MODERATE, RiskMetricService::resolvePriorityBucket(5.1));
        $this->assertSame(RiskMetricService::PRIORITY_MODERATE, RiskMetricService::resolvePriorityBucket(10.0));

        $this->assertSame(RiskMetricService::PRIORITY_HIGH, RiskMetricService::resolvePriorityBucket(10.1));
        $this->assertSame(RiskMetricService::PRIORITY_HIGH, RiskMetricService::resolvePriorityBucket(15.0));

        $this->assertSame(RiskMetricService::PRIORITY_EXTREME, RiskMetricService::resolvePriorityBucket(15.1));
        $this->assertSame(RiskMetricService::PRIORITY_EXTREME, RiskMetricService::resolvePriorityBucket(25.0));
    }

    public function test_resolve_priority_label_and_color()
    {
        $this->assertSame('Low', RiskMetricService::resolvePriorityLabel('low'));
        $this->assertSame('Moderate', RiskMetricService::resolvePriorityLabel('moderate'));
        $this->assertSame('High', RiskMetricService::resolvePriorityLabel('high'));
        $this->assertSame('Extreme', RiskMetricService::resolvePriorityLabel('extreme'));
        $this->assertSame('-', RiskMetricService::resolvePriorityLabel('unknown'));

        $this->assertSame('#4caf50', RiskMetricService::resolvePriorityColor('low'));
        $this->assertSame('#facc15', RiskMetricService::resolvePriorityColor('moderate'));
        $this->assertSame('#fb8c00', RiskMetricService::resolvePriorityColor('high'));
        $this->assertSame('#ef4444', RiskMetricService::resolvePriorityColor('extreme'));
    }

    public function test_is_high_risk_normal_and_strict_modes()
    {
        // Normal mode (score > 10 covers High and Extreme)
        $this->assertFalse(RiskMetricService::isHighRisk(10.0, false));
        $this->assertTrue(RiskMetricService::isHighRisk(10.5, false));
        $this->assertTrue(RiskMetricService::isHighRisk(16.0, false));

        // Strict mode (legacy committee threshold score >= 16)
        $this->assertFalse(RiskMetricService::isHighRisk(15.9, true));
        $this->assertTrue(RiskMetricService::isHighRisk(16.0, true));
        $this->assertTrue(RiskMetricService::isHighRisk(25.0, true));
    }

    public function test_resolve_display_score_hierarchy()
    {
        // Priority 1: Residual analysis score
        $riskWithResidual = new Risk(['risk_score' => 6.0]);
        $riskWithResidual->setRelation('residualAnalysis', new RiskResidualAnalysis(['residual_risk_score' => 12.0]));
        $riskWithResidual->setRelation('inherentAnalysis', new RiskInherentAnalysis(['inherent_risk_score' => 20.0]));
        $this->assertSame(12.0, RiskMetricService::resolveDisplayScore($riskWithResidual));

        // Priority 2: Inherent analysis score (when residual is null)
        $riskWithInherent = new Risk(['risk_score' => 6.0]);
        $riskWithInherent->setRelation('inherentAnalysis', new RiskInherentAnalysis(['inherent_risk_score' => 18.0]));
        $this->assertSame(18.0, RiskMetricService::resolveDisplayScore($riskWithInherent));

        // Priority 3: Fallback base risk_score
        $riskBase = new Risk(['risk_score' => 8.5]);
        $this->assertSame(8.5, RiskMetricService::resolveDisplayScore($riskBase));
    }

    public function test_build_benchmark_scoring_positions()
    {
        // Internal score <= 9 is ahead of target
        $ahead = RiskMetricService::buildBenchmarkScoring(8.0);
        $this->assertSame('ahead_of_target', $ahead['position']);
        $this->assertSame('Ahead of Target', $ahead['position_label']);

        // Internal score 10 is between target (9) and baseline (12)
        $better = RiskMetricService::buildBenchmarkScoring(10.5);
        $this->assertSame('better_than_industry', $better['position']);
        $this->assertSame('Better Than Industry', $better['position_label']);

        // Internal score 14 is above industry risk (12)
        $above = RiskMetricService::buildBenchmarkScoring(14.0);
        $this->assertSame('above_industry_risk', $above['position']);
        $this->assertSame('Above Industry Risk', $above['position_label']);
    }

    public function test_build_priority_summary_distribution()
    {
        $risk1 = new Risk(['risk_score' => 4.0]);
        $risk2 = new Risk(['risk_score' => 8.0]);
        $risk3 = new Risk(['risk_score' => 12.0]);
        $risk4 = new Risk(['risk_score' => 20.0]);

        $summary = RiskMetricService::buildPrioritySummary([$risk1, $risk2, $risk3, $risk4]);

        $this->assertCount(4, $summary);
        $keyed = collect($summary)->keyBy('label');

        $this->assertSame(1, $keyed['Low']['count']);
        $this->assertSame(1, $keyed['Moderate']['count']);
        $this->assertSame(1, $keyed['High']['count']);
        $this->assertSame(1, $keyed['Extreme']['count']);
    }

    public function test_build_full_summary_structure()
    {
        $summary = RiskMetricService::buildFullSummary(collect([]));

        $this->assertArrayHasKey('generated_at', $summary);
        $this->assertArrayHasKey('total_risks', $summary);
        $this->assertArrayHasKey('average_scores', $summary);
        $this->assertArrayHasKey('portfolio_overview', $summary);
        $this->assertArrayHasKey('sla', $summary);
        $this->assertArrayHasKey('kpi_dashboard', $summary);
        $this->assertArrayHasKey('variance', $summary);
        $this->assertArrayHasKey('benchmark_scoring', $summary);
        $this->assertArrayHasKey('department_performance', $summary);
        $this->assertArrayHasKey('root_cause_analysis', $summary);

        $this->assertSame(0, $summary['total_risks']);
    }

    public function test_normalize_trend_granularity()
    {
        $this->assertSame('monthly', RiskMetricService::normalizeTrendGranularity('monthly'));
        $this->assertSame('quarterly', RiskMetricService::normalizeTrendGranularity('quarterly'));
        $this->assertSame('yearly', RiskMetricService::normalizeTrendGranularity('yearly'));
        $this->assertSame('quarterly', RiskMetricService::normalizeTrendGranularity('invalid_value'));
    }
}
