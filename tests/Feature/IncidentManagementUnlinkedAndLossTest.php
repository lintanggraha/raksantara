<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskIncident;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class IncidentManagementUnlinkedAndLossTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_unlinked_incident_with_loss_amount(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->post(route('incidents.store'), [
                'risk_id' => '', // Not linked to any existing risk
                'risk_owner' => 'BT - DIGITAL BANKING',
                'title' => 'Server room AC failure',
                'incident_description' => 'AC unit leaked water causing small server outage.',
                'occurred_at' => now()->format('Y-m-d\TH:i'),
                'severity' => 'High',
                'status' => 'Open',
                'mitigation_verdict' => 'Not Tested',
                'loss_amount' => '75.000.000', // Formatted Rupiah
            ]);

        $response->assertRedirect(route('incidents.index'));
        $response->assertSessionHas('success');

        $incident = RiskIncident::where('title', 'Server room AC failure')->first();
        $this->assertNotNull($incident);
        $this->assertNull($incident->risk_id);
        $this->assertTrue($incident->is_new_finding);
        $this->assertEquals('BT - DIGITAL BANKING', $incident->risk_owner);
        $this->assertEquals(75000000, (float) $incident->loss_amount);
        $this->assertEquals('Rp 75.000.000', $incident->formatted_loss_amount);
        $this->assertTrue($incident->isNewFinding());
    }

    public function test_user_can_update_unlinked_incident(): void
    {
        $user = $this->createUser();

        $incident = RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Database slowdown',
            'incident_description' => 'Unindexed query caused high CPU usage.',
            'occurred_at' => now(),
            'severity' => 'Medium',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => 10000000,
        ]);

        $response = $this->actingAs($user)
            ->put(route('incidents.update', $incident->id), [
                'risk_id' => '',
                'risk_owner' => 'BT - DIGITAL BANKING',
                'title' => 'Database slowdown resolved',
                'incident_description' => 'Index was added and query optimized.',
                'occurred_at' => now()->format('Y-m-d\TH:i'),
                'severity' => 'Medium',
                'status' => 'Mitigated',
                'mitigation_verdict' => 'Effective',
                'loss_amount' => '12.500.000',
            ]);

        $response->assertRedirect(route('incidents.index'));

        $incident->refresh();
        $this->assertEquals('Database slowdown resolved', $incident->title);
        $this->assertEquals('Mitigated', $incident->status);
        $this->assertEquals(12500000, (float) $incident->loss_amount);
    }

    public function test_user_can_promote_unlinked_incident_to_new_risk(): void
    {
        $user = $this->createUser();
        $category = RiskCategory::query()->firstOrCreate(['name' => 'IT Infrastructure Risk']);

        $incident = RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Unpatched third-party library vulnerability',
            'incident_description' => 'Security scan flagged zero-day in payment SDK.',
            'occurred_at' => now(),
            'severity' => 'High',
            'status' => 'Investigating',
            'mitigation_verdict' => 'Not Tested',
            'root_cause' => 'Dependency was not checked in CI pipeline.',
            'immediate_action' => 'Blocked vulnerable endpoints.',
            'loss_amount' => 50000000,
        ]);

        // 1. Visit risks.create with ?from_incident=
        $createPageResponse = $this->actingAs($user)
            ->get(route('risks.create', ['from_incident' => $incident->id]));
        $createPageResponse->assertOk();
        $createPageResponse->assertSee($incident->incident_code);
        $createPageResponse->assertSee('Unpatched third-party library vulnerability');

        $impactScaleId = $this->ensureImpactScaleId();

        // 2. Submit new risk creation with from_incident_id
        $storeRiskResponse = $this->actingAs($user)
            ->post(route('risks.store'), [
                'from_incident_id' => $incident->id,
                'risk_title' => $incident->title,
                'risk_category_id' => $category->id,
                'risk_owner' => 'BT - DIGITAL BANKING',
                'risk_description' => $incident->incident_description,
                'cause' => $incident->root_cause,
                'existing_control' => $incident->immediate_action,
                'inherent_probability_score' => 3,
                'inherent_impact_score' => 4,
                'inherent_impact_scale_id' => $impactScaleId,
                'inherent_potential_loss' => 50000000,
                'residual_probability_score' => 2,
                'residual_impact_score' => 2,
                'residual_impact_scale_id' => $impactScaleId,
            ]);

        $storeRiskResponse->assertRedirect(route('incidents.index'));
        $storeRiskResponse->assertSessionHas('success');

        $incident->refresh();
        $this->assertNotNull($incident->risk_id);
        $this->assertFalse($incident->is_new_finding);

        $linkedRisk = Risk::find($incident->risk_id);
        $this->assertNotNull($linkedRisk);
        $this->assertEquals('Unpatched third-party library vulnerability', $linkedRisk->risk_title);
    }

    private function ensureImpactScaleId(): int
    {
        $existingId = \Illuminate\Support\Facades\DB::table('impact_scales')->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        $impactCategoryId = \Illuminate\Support\Facades\DB::table('impact_categories')->insertGetId([
            'name' => 'Test Impact Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) \Illuminate\Support\Facades\DB::table('impact_scales')->insertGetId([
            'impact_category_id' => (int) $impactCategoryId,
            'description' => 'Test Impact Scale ' . Str::random(6),
            'score' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_incident_index_displays_financial_loss_aggregations(): void
    {
        $user = $this->createUser();

        // Incident 1: 30 million
        RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Loss event 1',
            'incident_description' => 'Loss event description 1',
            'occurred_at' => now(),
            'severity' => 'High',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => 30000000,
        ]);

        // Incident 2: 20 million
        RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Loss event 2',
            'incident_description' => 'Loss event description 2',
            'occurred_at' => now(),
            'severity' => 'Medium',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => 20000000,
        ]);

        $response = $this->actingAs($user)->get(route('incidents.index'));
        $response->assertOk();
        $response->assertSee('Total Kerugian Finansial');
        $response->assertSee('50.000.000'); // 30M + 20M = 50M
        $response->assertSee('Rekap Kerugian Bulanan');
        $response->assertSee('Rekap Kerugian Tahunan');
    }

    public function test_unlinked_incident_can_view_learning_page(): void
    {
        $user = $this->createUser();

        $incident = RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Unlinked incident learning',
            'incident_description' => 'Detail description',
            'occurred_at' => now(),
            'severity' => 'Medium',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
        ]);

        $response = $this->actingAs($user)->get(route('incidents.learning.show', $incident->id));
        $response->assertOk();
        $response->assertSee('Temuan Baru (Belum Terdaftar)');
        $response->assertSee('Jadikan Risk Baru');
    }

    public function test_user_can_access_incident_create_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('incidents.create'));
        $response->assertOk();
        $response->assertSee('Catat Insiden / Temuan Baru');
        $response->assertSee('Related Risk (Opsional)');
        $response->assertSee('Estimasi Kerugian Finansial (Rp)');

        // Superadmin is read-only and should be forbidden
        $superadmin = $this->createSuperadminUser();
        $superadminResponse = $this->actingAs($superadmin)->get(route('incidents.create'));
        $superadminResponse->assertForbidden();
    }

    public function test_user_can_access_incident_edit_page(): void
    {
        $user = $this->createUser();

        $incident = RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Incident for edit test',
            'incident_description' => 'Incident description for edit test',
            'occurred_at' => now(),
            'severity' => 'High',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => 45000000,
        ]);

        $response = $this->actingAs($user)->get(route('incidents.edit', $incident->id));
        $response->assertOk();
        $response->assertSee('Edit Insiden: ' . $incident->incident_code);
        $response->assertSee('Incident for edit test');
        $response->assertSee('45.000.000');

        // Superadmin is read-only and should be forbidden
        $superadmin = $this->createSuperadminUser();
        $superadminResponse = $this->actingAs($superadmin)->get(route('incidents.edit', $incident->id));
        $superadminResponse->assertForbidden();
    }

    public function test_dashboard_separates_input_and_links_to_create_and_edit(): void
    {
        $user = $this->createUser();

        $incident = RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Separated Dashboard Incident',
            'incident_description' => 'Testing links',
            'occurred_at' => now(),
            'severity' => 'Low',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => 5000000,
        ]);

        $response = $this->actingAs($user)->get(route('incidents.index'));
        $response->assertOk();

        // Dashboard should have link to create page
        $response->assertSee(route('incidents.create'));
        $response->assertSee('Catat Insiden / Temuan Baru');

        // Dashboard table should have direct link to edit page
        $response->assertSee(route('incidents.edit', $incident->id));

        // Dashboard should NOT have the old inline create form
        $response->assertDontSee('Record New Incident / New Finding');
    }

    public function test_user_can_create_and_update_incident_with_affected_client(): void
    {
        $user = $this->createUser();

        // 1. Create incident with affected_client BANK SULTENG
        $response = $this->actingAs($user)
            ->post(route('incidents.store'), [
                'risk_id' => '',
                'risk_owner' => 'BT - DIGITAL BANKING',
                'affected_client' => 'BANK SULTENG',
                'title' => 'Core Banking API Timeout at Bank Sulteng',
                'incident_description' => 'Response time from switching gateway spiked to 12s.',
                'occurred_at' => now()->format('Y-m-d\TH:i'),
                'severity' => 'High',
                'status' => 'Open',
                'mitigation_verdict' => 'Not Tested',
                'loss_amount' => '15.000.000',
            ]);

        $response->assertRedirect(route('incidents.index'));
        $incident = RiskIncident::where('title', 'Core Banking API Timeout at Bank Sulteng')->first();
        $this->assertNotNull($incident);
        $this->assertEquals('BANK SULTENG', $incident->affected_client);

        // 2. Check create and edit pages display affected client options
        $createResponse = $this->actingAs($user)->get(route('incidents.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Client Terdampak (Bank / Lembaga)');
        $createResponse->assertSee('BANK SULTENG');
        $createResponse->assertSee('BANK SUMUT');

        $editResponse = $this->actingAs($user)->get(route('incidents.edit', $incident->id));
        $editResponse->assertOk();
        $editResponse->assertSee('Client Terdampak (Bank / Lembaga)');
        $editResponse->assertSee('BANK SULTENG');

        // 3. Update affected client to BANK SUMUT
        $updateResponse = $this->actingAs($user)
            ->put(route('incidents.update', $incident->id), [
                'risk_id' => '',
                'risk_owner' => 'BT - DIGITAL BANKING',
                'affected_client' => 'BANK SUMUT',
                'title' => 'Core Banking API Timeout at Bank Sumut',
                'incident_description' => 'Response time issue resolved after failover.',
                'occurred_at' => now()->format('Y-m-d\TH:i'),
                'severity' => 'High',
                'status' => 'Mitigated',
                'mitigation_verdict' => 'Effective',
                'loss_amount' => '25.000.000',
            ]);

        $updateResponse->assertRedirect(route('incidents.index'));
        $incident->refresh();
        $this->assertEquals('BANK SUMUT', $incident->affected_client);

        // 4. Learning view displays affected client
        $learningResponse = $this->actingAs($user)->get(route('incidents.learning.show', $incident->id));
        $learningResponse->assertOk();
        $learningResponse->assertSee('Client Terdampak');
        $learningResponse->assertSee('BANK SUMUT');
    }

    public function test_user_can_filter_incidents_by_affected_client(): void
    {
        $user = $this->createUser();

        RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'affected_client' => 'BANK SULTENG',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Incident for Sulteng',
            'incident_description' => 'Sulteng specific incident',
            'occurred_at' => now(),
            'severity' => 'Medium',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => 5000000,
        ]);

        RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => null,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'affected_client' => 'BANK ACEH',
            'is_new_finding' => true,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Incident for Aceh',
            'incident_description' => 'Aceh specific incident',
            'occurred_at' => now(),
            'severity' => 'Medium',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => 3000000,
        ]);

        // Unfiltered index sees both
        $unfilteredResponse = $this->actingAs($user)->get(route('incidents.index'));
        $unfilteredResponse->assertOk();
        $unfilteredResponse->assertSee('Incident for Sulteng');
        $unfilteredResponse->assertSee('Incident for Aceh');

        // Filtered by BANK SULTENG
        $filteredResponse = $this->actingAs($user)->get(route('incidents.index', ['affected_client' => 'BANK SULTENG']));
        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Incident for Sulteng');
        $filteredResponse->assertDontSee('Incident for Aceh');
    }

    private function createUser(): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Test Incident User ' . $suffix,
            'username' => 'incident_user_' . $suffix,
            'division' => 'BT - DIGITAL BANKING',
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createSuperadminUser(): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Superadmin ' . $suffix,
            'username' => 'superadmin_' . $suffix,
            'division' => 'ALL',
            'is_superadmin' => true,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
