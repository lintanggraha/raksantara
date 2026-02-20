<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$ra = \App\RiskResidualAnalysis::orderBy('id','desc')->first();
if (!$ra) { echo "No residual analyses\n"; exit; }
print_r($ra->toArray());
