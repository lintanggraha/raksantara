<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Risk Benchmark Scoring
    |--------------------------------------------------------------------------
    |
    | Lower score means lower risk exposure. These defaults can be tuned
    | based on your industry benchmark and internal risk appetite.
    |
    */
    'industry_baseline_score' => (float) env('RISK_BENCHMARK_INDUSTRY_SCORE', 12.0),
    'target_score' => (float) env('RISK_BENCHMARK_TARGET_SCORE', 9.0),
    'industry_source_name' => (string) env(
        'RISK_BENCHMARK_SOURCE_NAME',
        'NHSCFA Risk Management Guidance (public-sector 5x5 governance benchmark)'
    ),
    'industry_source_reference' => (string) env(
        'RISK_BENCHMARK_SOURCE_REFERENCE',
        'https://cfa.nhs.uk/about-nhscfa/information-hub/corporate-information-governance/risk-management-guidance (score 9+ discussion trigger; 12+ escalation threshold)'
    ),
    'industry_source_year' => (int) env('RISK_BENCHMARK_SOURCE_YEAR', 2022),
];
