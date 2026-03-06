<?php

namespace App\Http\Controllers;

use App\Services\ThreatIntelligenceService;
use Illuminate\Http\Request;

class ThreatIntelligenceController extends Controller
{
    private ThreatIntelligenceService $threatIntelligenceService;

    public function __construct(ThreatIntelligenceService $threatIntelligenceService)
    {
        $this->threatIntelligenceService = $threatIntelligenceService;
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'keyword' => 'nullable|string|max:100',
            'severity' => 'nullable|string|in:critical,high,medium,low,CRITICAL,HIGH,MEDIUM,LOW',
            'days' => 'nullable|integer|in:7,30,90',
            'limit' => 'nullable|integer|in:10,12,20,50',
        ]);

        $dashboard = $this->threatIntelligenceService->buildDashboard($filters);

        return view('threat-intelligence.index', compact('dashboard'));
    }
}
