<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdvancedRiskSecurityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_non_compliance_user_is_blocked_from_compliance_pages(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING');
        $this->actingAs($user);

        $response = $this->get(route('compliance.compliance'));

        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('error', 'Menu Compliance hanya untuk divisi Compliance.');

        $jsonResponse = $this->getJson(route('compliance.compliance'));
        $jsonResponse->assertForbidden();
    }

    public function test_compliance_user_can_access_all_compliance_pages(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $this->get(route('compliance.internal-audit'))->assertOk();
        $this->get(route('compliance.risk-management'))->assertOk();
        $this->get(route('compliance.compliance'))->assertOk();
    }

    public function test_risk_creation_accepts_business_text_and_sql_like_literal_as_plain_text(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $payload = $this->validRiskPayload([
            'risk_title' => "R&D - Core Banking / Channel (Phase 2): O'Hara @QA_1!",
            'risk_description' => "Potential OR 1=1 -- treated as plain text, not executable query.",
            'cause' => "Dependency outage / network-latency issue.",
            'existing_control' => "Daily monitoring, backup-check, and incident runbook.",
        ]);

        $response = $this->post(route('risks.store'), $payload);

        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('risks', [
            'risk_title' => $payload['risk_title'],
            'risk_description' => $payload['risk_description'],
            'risk_owner' => 'CO - COMPLIANCE',
        ]);
    }

    public function test_risk_creation_rejects_script_like_payload(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $payload = $this->validRiskPayload([
            'risk_title' => '<script>alert(1)</script>',
        ]);

        $response = $this
            ->from(route('risks.create'))
            ->post(route('risks.store'), $payload);

        $response->assertRedirect(route('risks.create'));
        $response->assertSessionHasErrors(['risk_title']);
        $this->assertDatabaseMissing('risks', [
            'risk_title' => '<script>alert(1)</script>',
        ]);
    }

    public function test_risk_creation_normalizes_localized_numeric_inputs_before_validation(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $impactScaleId = $this->ensureImpactScaleId();

        $payload = $this->validRiskPayload([
            'risk_title' => 'Localized Numeric Parsing Test',
            'inherent_probability_score' => 3,
            'inherent_impact_scale_id' => $impactScaleId,
            'inherent_impact_score' => 2,
            'inherent_impact_amount' => '1.000',
            'inherent_probability_percent' => '10,5',
            'inherent_potential_loss' => '2,500',
            'residual_probability_score' => 2,
            'residual_impact_scale_id' => $impactScaleId,
            'residual_impact_score' => 2,
            'residual_impact_amount' => '2,000',
            'residual_probability_percent' => '20,75',
            'residual_potential_loss' => '3.000',
        ]);

        $response = $this->post(route('risks.store'), $payload);
        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('success');

        $riskId = DB::table('risks')
            ->where('risk_title', 'Localized Numeric Parsing Test')
            ->value('id');

        $this->assertNotNull($riskId);

        $inherent = DB::table('risk_inherent_analyses')->where('risk_id', $riskId)->first();
        $this->assertNotNull($inherent);
        $this->assertSame(1000, (int) $inherent->impact_amount);
        $this->assertEqualsWithDelta(0.105, (float) $inherent->probability_percent, 0.00001);
        $this->assertSame(2500, (int) $inherent->potential_loss);

        $residual = DB::table('risk_residual_analyses')->where('risk_id', $riskId)->first();
        $this->assertNotNull($residual);
        $this->assertSame(2000, (int) $residual->impact_amount);
        $this->assertEqualsWithDelta(0.2075, (float) $residual->probability_percent, 0.00001);
        $this->assertSame(3000, (int) $residual->potential_loss);
    }

    public function test_risk_creation_rejects_invalid_numeric_characters(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $payload = $this->validRiskPayload([
            'risk_title' => 'Invalid Numeric Character Test',
            'inherent_impact_amount' => '10O0',
        ]);

        $response = $this
            ->from(route('risks.create'))
            ->post(route('risks.store'), $payload);

        $response->assertRedirect(route('risks.create'));
        $response->assertSessionHasErrors(['inherent_impact_amount']);
        $this->assertDatabaseMissing('risks', [
            'risk_title' => 'Invalid Numeric Character Test',
        ]);
    }

    private function validRiskPayload(array $override = []): array
    {
        return array_merge([
            'risk_title' => 'Base Risk Title',
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'CO - COMPLIANCE',
            'risk_description' => 'Base risk description.',
            'cause' => 'Base cause.',
            'existing_control' => 'Base control.',
        ], $override);
    }

    private function ensureRiskCategoryId(): int
    {
        $existingId = DB::table('risk_categories')->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        return (int) DB::table('risk_categories')->insertGetId([
            'name' => 'Test Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureImpactScaleId(): int
    {
        $existingId = DB::table('impact_scales')->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        $impactCategoryId = DB::table('impact_categories')->value('id');
        if (is_null($impactCategoryId)) {
            $impactCategoryId = DB::table('impact_categories')->insertGetId([
                'name' => 'Test Impact Category ' . Str::random(6),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) DB::table('impact_scales')->insertGetId([
            'impact_category_id' => (int) $impactCategoryId,
            'description' => 'Test Impact Scale ' . Str::random(6),
            'score' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Test User ' . $suffix,
            'username' => 'user_' . $suffix,
            'division' => $division,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
