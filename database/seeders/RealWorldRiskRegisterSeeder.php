<?php

namespace Database\Seeders;

use App\Risk;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\RiskScoreMatrix;
use App\RiskTreatment;
use App\RadarGroup;
use Database\Seeders\Data\RiskDataBatch1;
use Database\Seeders\Data\RiskDataBatch2;
use Database\Seeders\Data\RiskDataBatch3;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealWorldRiskRegisterSeeder extends Seeder
{
    public function run()
    {
        // 1. Clean up existing dummy risks and previously seeded 2026 real-world risks
        $targetRiskIds = DB::table('risks')
            ->where('risk_id', 'like', 'DUMMY-%')
            ->orWhere('risk_id', 'like', 'RISK-2026-%')
            ->pluck('id');

        if ($targetRiskIds->isNotEmpty()) {
            DB::table('risk_inherent_analyses')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_residual_analyses')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_treatments')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_controls')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_acceptances')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('bias')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('kris')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_alerts')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_comments')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_incidents')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_rcsa_workspaces')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_scenario_impacts')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_status_change_requests')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risk_committee_decisions')->whereIn('risk_id', $targetRiskIds)->delete();
            DB::table('risks')->whereIn('id', $targetRiskIds)->delete();
        }

        // Cache category and radar mappings
        $radarMap = DB::table('radar_groups')->get()->keyBy('code');

        $ownersData = array_merge(
            RiskDataBatch1::getData(),
            RiskDataBatch2::getData(),
            RiskDataBatch3::getData()
        );

        $globalCounter = 1;

        foreach ($ownersData as $ownerName => $items) {
            foreach ($items as $data) {
                $riskCode = sprintf('RISK-2026-%03d', $globalCounter++);
                
                $pScore = $data['inherent_p'];
                $iScore = $data['inherent_i'];
                $rScore = $pScore * $iScore;

                $resP = $data['residual_p'];
                $resI = $data['residual_i'];
                $resScore = $resP * $resI;

                $impactAmount = $data['impact_amount'] ?? ($iScore * 150000000);
                $pPercent = round($pScore * 0.18, 2);
                $loss = round($impactAmount * $pPercent);

                $resImpactAmount = $data['res_impact_amount'] ?? round($impactAmount * 0.6);
                $resPPercent = round($resP * 0.12, 2);
                $resLoss = round($resImpactAmount * $resPPercent);

                $radarCode = $data['radar_code'] ?? 'O2';
                $radarGroup = $radarMap->get($radarCode);

                $risk = Risk::create([
                    'risk_id' => $riskCode,
                    'year' => 2026,
                    'risk_title' => $data['title'],
                    'sasaran_target' => $data['target'],
                    'risk_description' => $data['description'],
                    'risk_category_id' => $data['category_id'],
                    'risk_cause_category_id' => $data['cause_category_id'],
                    'cause' => $data['cause'],
                    'existing_control' => $data['existing_control'],
                    'risk_owner' => $ownerName,
                    'created_by' => 1,
                    'status' => $data['status'] ?? 'Monitoring',
                    'probability_score' => $pScore,
                    'impact_score' => $iScore,
                    'risk_score' => $rScore,
                    'probability_notes' => 'Tingkat frekuensi berdasarkan riwayat insiden dan tren ancaman industri terkini.',
                    'impact_notes' => 'Estimasi dampak finansial, reputasi, operasional, dan kepatuhan regulasi.',
                    'impact_scale_id' => $iScore,
                ]);

                // Inherent
                RiskInherentAnalysis::create([
                    'risk_id' => $risk->id,
                    'probability_score' => $pScore,
                    'probability_description' => "Probabilitas inherent level {$pScore} berdasarkan exposure risiko eksternal/internal.",
                    'impact_score' => $iScore,
                    'impact_description' => "Dampak inherent level {$iScore} jika tidak terdapat kontrol tambahan.",
                    'impact_scale_id' => $iScore,
                    'impact_amount' => $impactAmount,
                    'probability_percent' => $pPercent,
                    'potential_loss' => $loss,
                    'inherent_risk_score' => $rScore,
                    'risk_level' => RiskScoreMatrix::getRiskLevel($rScore),
                ]);

                // Residual
                $calcAdjustment = max(1.0, round(($resP - 0.5) * $iScore, 2));
                $adjustmentInt = (int) round($calcAdjustment);
                $levelAdjustment = ($resI < 5 && $calcAdjustment < 6) ? 'Rendah' : (($resI >= 4 && $calcAdjustment >= 10) ? 'Tinggi' : 'Moderat');

                RiskResidualAnalysis::create([
                    'risk_id' => $risk->id,
                    'probability_score' => $resP,
                    'probability_description' => "Probabilitas tereduksi menjadi level {$resP} setelah mitigasi.",
                    'impact_score' => $resI,
                    'impact_description' => "Dampak residual terkontrol di level {$resI}.",
                    'impact_scale_id' => $resI,
                    'impact_amount' => $resImpactAmount,
                    'probability_percent' => $resPPercent,
                    'potential_loss' => $resLoss,
                    'residual_risk_score' => $resScore,
                    'risk_level' => RiskScoreMatrix::getRiskLevel($resScore),
                    'risk_adjustment' => $adjustmentInt,
                    'adjustment' => (string) $calcAdjustment,
                    'risk_level_adjustment' => $levelAdjustment,
                    'radar_group_id' => optional($radarGroup)->id,
                    'radar_code' => $radarCode,
                    'radar_description' => optional($radarGroup)->description ?? 'Operasional & Teknologi',
                ]);

                // Treatment
                RiskTreatment::create([
                    'risk_id' => $risk->id,
                    'option' => 'Mitigate',
                    'description' => $data['mitigation'],
                    'pic' => $ownerName,
                    'status' => 'In Progress',
                    'status_updated_at' => now(),
                    'deadline' => now()->addMonths(random_int(2, 8))->toDateString(),
                    'next_action_at' => now()->addWeeks(random_int(1, 4))->toDateString(),
                    'effectiveness' => 'Medium',
                    'effectiveness_score' => 0.50,
                    'playbook_checklist' => "1. SOP Review & Approval\n2. Implementation & Deployment Kontrol\n3. Monitoring & Evaluasi Berkala",
                ]);
            }
        }
    }
}
