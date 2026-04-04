<?php

namespace App\Http\Controllers;

use App\Support\RiskAccess;
use Illuminate\Http\Request;
use App\Risk;
use App\Bia;

class BiaController extends Controller
{
    public function update(Request $request, Risk $risk)
    {
        $this->ensureCanManageRisk($risk);

        $validated = $request->validate([
            'rto' => 'nullable|string|in:1 Hour,4 Hours,24 Hours,1 Week,1 Month',
            'rpo' => 'nullable|string|in:1 Hour,4 Hours,24 Hours,1 Week,1 Month',
            'mao' => 'nullable|string|in:1 Hour,4 Hours,24 Hours,1 Week,1 Month',
            'impact_financial' => 'nullable|string|in:Low,Medium,High,Critical',
            'impact_operational' => 'nullable|string|in:Low,Medium,High,Critical',
            'impact_regulatory' => 'nullable|string|in:Low,Medium,High,Critical',
            'impact_reputational' => 'nullable|string|in:Low,Medium,High,Critical',
            'criticality_tier' => 'nullable|string|in:Tier 1,Tier 2,Tier 3,Tier 4',
            'justification_notes' => 'nullable|string|max:2000',
        ]);

        $bia = $risk->bia()->updateOrCreate(
            ['risk_id' => $risk->id],
            $validated
        );

        return redirect()
            ->route('risks.show', $risk->id)
            ->withFragment('bcm')
            ->with('success', 'Business Impact Analysis updated successfully.');
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
