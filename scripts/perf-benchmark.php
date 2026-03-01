<?php

declare(strict_types=1);

use App\Http\Controllers\RiskController;
use App\Http\Controllers\TrendAnalysisController;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

/**
 * @param callable(): mixed $fn
 */
function measure(string $label, callable $fn): void
{
    $start = microtime(true);
    $result = $fn();
    $elapsedMs = (microtime(true) - $start) * 1000;
    $bytes = 0;

    if (is_object($result) && method_exists($result, 'getContent')) {
        $content = (string) $result->getContent();
        $bytes = strlen($content);
    } elseif (is_string($result)) {
        $bytes = strlen($result);
    }

    echo $label . ': ' . number_format($elapsedMs, 2, '.', '') . ' ms';
    if ($bytes > 0) {
        echo ' | payload=' . $bytes . ' bytes';
    }
    echo PHP_EOL;
}

$riskController = $app->make(RiskController::class);
$trendController = $app->make(TrendAnalysisController::class);

measure('risk.dashboardSummary', function () use ($riskController) {
    $request = Request::create('/risk-dashboard-summary', 'GET');

    return $riskController->dashboardSummary($request);
});

$summaryResponse = $riskController->dashboardSummary(Request::create('/risk-dashboard-summary', 'GET'));
$summaryPayload = json_decode((string) $summaryResponse->getContent(), true);
echo 'risk.dashboardSummary.generated_at=' . (string) ($summaryPayload['generated_at'] ?? '-') . PHP_EOL;

measure('trend.data.quarterly.365', function () use ($trendController) {
    $request = Request::create('/api/trend-analysis/data', 'GET', [
        'granularity' => 'quarterly',
        'days' => 365,
    ]);

    return $trendController->data($request);
});
