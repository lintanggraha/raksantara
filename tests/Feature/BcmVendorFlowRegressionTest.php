<?php

namespace Tests\Feature;

use App\Bia;
use App\Risk;
use App\RiskCategory;
use App\User;
use App\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BcmVendorFlowRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_bcm_dashboard_only_shows_visible_risks_and_hides_foreign_bia_targets(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $owner = $this->createUser('CO - COMPLIANCE', false);

        $ownRisk = $this->createRiskRecord($viewer, 'BT - DIGITAL BANKING', 'Own BCM Risk');
        $foreignRiskWithBia = $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Foreign BCM Risk');
        $foreignRiskWithoutBia = $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Foreign Available BCM Risk');

        Bia::create([
            'risk_id' => $ownRisk->id,
            'criticality_tier' => 'Tier 2',
        ]);

        Bia::create([
            'risk_id' => $foreignRiskWithBia->id,
            'criticality_tier' => 'Tier 1',
        ]);

        $this->actingAs($viewer);

        $this->get(route('bcm.index'))
            ->assertOk()
            ->assertSee('Own BCM Risk')
            ->assertDontSee('Foreign BCM Risk')
            ->assertDontSee('Foreign Available BCM Risk');
    }

    public function test_non_compliance_user_cannot_update_foreign_bia_or_bcp(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $owner = $this->createUser('CO - COMPLIANCE', false);
        $foreignRisk = $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Foreign Protected BCM Risk');
        $foreignBia = Bia::create([
            'risk_id' => $foreignRisk->id,
            'criticality_tier' => 'Tier 1',
        ]);

        $this->actingAs($viewer);

        $this->post(route('bia.store', $foreignRisk->id), [
            'rto' => '1 Hour',
            'criticality_tier' => 'Tier 1',
        ])->assertForbidden();

        $this->post(route('bcp.store', $foreignBia->id), [
            'testing_frequency' => 'Annual',
            'last_test_result' => 'Pass',
        ])->assertForbidden();
    }

    public function test_vendor_detail_only_lists_visible_risks(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $owner = $this->createUser('CO - COMPLIANCE', false);
        $vendor = Vendor::create([
            'name' => 'Scoped Vendor',
            'status' => 'Active',
        ]);

        $ownRisk = $this->createRiskRecord($viewer, 'BT - DIGITAL BANKING', 'Own Vendor Risk', $vendor->id);
        $foreignRisk = $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Foreign Vendor Risk', $vendor->id);

        $this->actingAs($viewer);

        $this->get(route('vendors.show', $vendor->id))
            ->assertOk()
            ->assertSee('Own Vendor Risk')
            ->assertDontSee('Foreign Vendor Risk');
    }

    public function test_create_risk_page_includes_vendor_field_and_can_continue_to_bcm(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING', false);
        $vendor = Vendor::create([
            'name' => 'Create Flow Vendor',
            'status' => 'Active',
        ]);

        $this->actingAs($user);

        $this->get(route('risks.create'))
            ->assertOk()
            ->assertSee('name="vendor_id"', false)
            ->assertSee('Mitigation Playbook (Optional)')
            ->assertSee('Add Treatment Manually')
            ->assertSee('ensureAtLeastOneTreatmentRow', false)
            ->assertSee('SAVE & CONTINUE TO BCM', false);

        $response = $this->post(route('risks.store'), [
            'risk_title' => 'Create Flow BCM Risk',
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'BT - DIGITAL BANKING',
            'vendor_id' => $vendor->id,
            'post_submit_action' => 'bcm',
        ]);

        $risk = Risk::query()->where('risk_title', 'Create Flow BCM Risk')->firstOrFail();

        $response->assertRedirect(route('risks.show', $risk->id) . '#bcm');

        $this->assertDatabaseHas('risks', [
            'id' => $risk->id,
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_edit_risk_can_continue_to_bcm(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING', false);
        $risk = $this->createRiskRecord($user, 'BT - DIGITAL BANKING', 'Editable BCM Risk');

        $this->actingAs($user);

        $this->get(route('risks.edit', $risk->id))
            ->assertOk()
            ->assertSee('Mitigation Playbook (Optional)')
            ->assertSee('Add Treatment Manually')
            ->assertSee('ensureAtLeastOneTreatmentRow', false)
            ->assertSee('UPDATE & OPEN BCM', false);

        $response = $this->put(route('risks.update', $risk->id), [
            'risk_title' => 'Editable BCM Risk Updated',
            'risk_category_id' => $risk->risk_category_id,
            'risk_owner' => $risk->risk_owner,
            'status' => 'Open',
            'post_submit_action' => 'bcm',
        ]);

        $response->assertRedirect(route('risks.show', $risk->id) . '#bcm');

        $this->assertDatabaseHas('risks', [
            'id' => $risk->id,
            'risk_title' => 'Editable BCM Risk Updated',
        ]);
    }

    private function createRiskRecord(User $creator, string $riskOwner, string $title, ?int $vendorId = null): Risk
    {
        return Risk::create([
            'risk_id' => 'FLOW-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => $title,
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => $riskOwner,
            'vendor_id' => $vendorId,
            'status' => 'Open',
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureRiskCategoryId(): int
    {
        $existingId = RiskCategory::query()->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        return (int) RiskCategory::query()->insertGetId([
            'name' => 'BCM Vendor Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'BCM Vendor User ' . $suffix,
            'username' => 'bcm_vendor_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => 'bcm_vendor_' . $suffix . '@example.test',
            'password' => Hash::make('password'),
        ]);
    }
}
