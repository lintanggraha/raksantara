<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$r = \App\Risk::where('risk_id','RISK-2026-008')->first();
if (!$r) { echo "Risk not found\n"; exit; }
$ra = $r->residualAnalysis;
if (!$ra) { echo "No residual analysis\n"; exit; }
print_r($ra->toArray());
