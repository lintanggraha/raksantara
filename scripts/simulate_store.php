<?php
// scripts/simulate_store.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\RiskCategory;
use App\RiskCauseCategory;
use App\ImpactScale;
use App\Risk;
use App\Http\Controllers\RiskController;
use Illuminate\Http\Request;

$cat = RiskCategory::first();
$cause = RiskCauseCategory::first();
$impact1 = ImpactScale::first();
$impact2 = ImpactScale::skip(1)->first() ?: $impact1;

$data = [
    'risk_title' => 'AutoTest Risk Full',
    'risk_owner' => 'BT - BANKING APPLICATIONS',
    'risk_category_id' => $cat ? $cat->id : null,
    'risk_cause_category_id' => $cause ? $cause->id : null,
    'risk_description' => 'auto desc',
    'cause' => 'auto cause',
    'existing_control' => 'auto control',
    'inherent_probability_score' => 3,
    'inherent_probability_description' => '3 - mungkin',
    'inherent_impact_scale_id' => $impact1 ? $impact1->id : null,
    'inherent_impact_score' => $impact1 ? $impact1->score : 3,
    'inherent_impact_description' => $impact1 ? $impact1->description : 'impact',
    'residual_probability_score' => 2,
    'residual_probability_description' => '2 - kecil',
    'residual_impact_scale_id' => $impact2 ? $impact2->id : null,
    'residual_impact_score' => $impact2 ? $impact2->score : 2,
    'residual_impact_description' => $impact2 ? $impact2->description : 'impact',
    'treatment_option' => ['Mitigation'],
    'treatment_description' => ['auto desc'],
    'treatment_pic' => ['autopic'],
    'treatment_deadline' => ['2026-12-31'],
    'treatment_effectiveness' => ['High'],
];

// Create a Request and call controller
$req = Request::create('/risks', 'POST', $data);
$controller = new RiskController();
try {
    $response = $controller->store($req);
    echo "Store executed. Response type: " . get_class($response) . PHP_EOL;
} catch (\Exception $e) {
    echo "Store threw exception: " . $e->getMessage() . PHP_EOL;
}

// Verify DB
$r = Risk::where('risk_title', 'AutoTest Risk Full')->first();
if ($r) {
    echo "Created Risk ID: {$r->id}, risk_id: {$r->risk_id}\n";
    echo "Has inherentAnalysis: " . ($r->inherentAnalysis ? 'yes' : 'no') . "\n";
    echo "Treatments count: " . $r->treatments()->count() . "\n";
    echo "Has residualAnalysis: " . ($r->residualAnalysis ? 'yes' : 'no') . "\n";
} else {
    echo "Risk not found after store." . PHP_EOL;
}
