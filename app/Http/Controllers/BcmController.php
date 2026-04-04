<?php

namespace App\Http\Controllers;

use App\Bia;
use App\Risk;
use App\Support\RiskAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BcmController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $baseQuery = Bia::query()
            ->with(['risk', 'bcp'])
            ->whereHas('risk', function (Builder $riskQuery) use ($user) {
                RiskAccess::applyVisibilityScope($riskQuery, $user);
            });

        // Stats
        $totalBias = (clone $baseQuery)->count();
        $criticalBias = (clone $baseQuery)->where('criticality_tier', 'Tier 1')->count();
        $missingBcps = (clone $baseQuery)->doesntHave('bcp')->count();
        $testedBcps = (clone $baseQuery)->whereHas('bcp', function ($q) {
            $q->whereNotNull('last_tested_at')->where('last_test_result', 'Pass');
        })->count();

        // Filters
        $query = clone $baseQuery;
        if ($request->filled('tier')) {
            $query->where('criticality_tier', $request->tier);
        }

        $bias = $query->latest()->paginate(20);

        $availableRisksQuery = Risk::query()
            ->doesntHave('bia')
            ->orderByDesc('id');
        RiskAccess::applyVisibilityScope($availableRisksQuery, $user);
        $availableRisks = $availableRisksQuery
            ->limit(200)
            ->get(['id', 'risk_id', 'risk_title']);

        return view('bcm.index', compact(
            'bias',
            'totalBias',
            'criticalBias',
            'missingBcps',
            'testedBcps',
            'availableRisks'
        ));
    }
}
