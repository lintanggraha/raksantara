<?php

namespace App\Http\Controllers;

use App\ImpactScale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AppEntryController extends Controller
{
    public function redirectToMonitoring(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }


    public function pendingApproval(): View
    {
        return view('account.pending-approval');
    }

    public function impactScales(int $id): JsonResponse
    {
        $scales = ImpactScale::query()
            ->where('impact_category_id', $id)
            ->orderBy('score')
            ->get();

        return response()->json($scales);
    }
}
