<?php

namespace App\Http\Controllers;

use App\Support\RiskAccess;
use Illuminate\Http\Request;
use App\Bia;
use App\Bcp;
use App\Risk;

class BcpController extends Controller
{
    public function update(Request $request, Bia $bia)
    {
        $risk = $bia->risk;
        abort_if(is_null($risk), 404);

        $this->ensureCanManageRisk($risk);

        $validated = $request->validate([
            'strategy_summary' => 'nullable|string|max:5000',
            'recovery_procedures' => 'nullable|string|max:5000',
            'primary_workaround' => 'nullable|string|max:5000',
            'testing_frequency' => 'nullable|string|in:Monthly,Quarterly,Semi-Annual,Annual',
            'last_tested_at' => 'nullable|date',
            'last_test_result' => 'nullable|string|in:Pass,Partial,Fail',
        ]);

        $bcp = $bia->bcp()->updateOrCreate(
            ['bia_id' => $bia->id],
            $validated
        );

        return redirect()
            ->route('risks.show', $bia->risk_id)
            ->withFragment('bcm')
            ->with('success', 'Business Continuity Plan updated successfully.');
    }

    private function ensureCanManageRisk(Risk $risk): void
    {
        $user = auth()->user();

        abort_if(
            !is_null($user) && $user->isSuperadmin(),
            403,
            'Superadmin is read-only for risk dashboard.'
        );

        abort_unless(
            RiskAccess::canManage($user, $risk),
            403,
            'You are not allowed to modify this risk.'
        );
    }
}
