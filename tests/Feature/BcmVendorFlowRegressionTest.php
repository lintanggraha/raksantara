<?php

namespace Tests\Feature;

use App\BusinessService;
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

    public function test_bcm_catalogue_only_shows_services_of_visible_units(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);

        BusinessService::create(['code' => 'SVC-901', 'name' => 'Own Digital Service', 'owner_unit' => 'BT - DIGITAL BANKING', 'status' => 'active']);
        BusinessService::create(['code' => 'SVC-902', 'name' => 'Foreign Compliance Service', 'owner_unit' => 'CO - COMPLIANCE', 'status' => 'active']);

        $this->actingAs($viewer);

        $this->get(route('bcm.index'))
            ->assertOk()
            ->assertSee('Own Digital Service')
            ->assertDontSee('Foreign Compliance Service');
    }

    public function test_non_compliance_user_cannot_view_or_update_foreign_service(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $foreign = BusinessService::create(['code' => 'SVC-903', 'name' => 'Foreign Protected Service', 'owner_unit' => 'CO - COMPLIANCE', 'status' => 'active']);

        $this->actingAs($viewer);

        $this->get(route('bcm.services.show', $foreign))->assertNotFound();
        $this->put(route('bcm.bcp.update', $foreign), ['strategy_summary' => 'x'])->assertNotFound();
        $this->assertDatabaseMissing('service_bcps', ['business_service_id' => $foreign->id]);
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

    public function test_create_risk_page_includes_vendor_field_and_can_continue_to_services(): void
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
            ->assertSee('SAVE & LINK TO SERVICES', false);

        $response = $this->post(route('risks.store'), [
            'risk_title' => 'Create Flow BCM Risk',
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'BT - DIGITAL BANKING',
            'vendor_id' => $vendor->id,
            'post_submit_action' => 'bcm',
        ]);

        $risk = Risk::query()->where('risk_title', 'Create Flow BCM Risk')->firstOrFail();

        $response->assertRedirect(route('risks.show', $risk->id) . '#services');

        $this->assertDatabaseHas('risks', [
            'id' => $risk->id,
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_edit_risk_can_continue_to_services(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING', false);
        $risk = $this->createRiskRecord($user, 'BT - DIGITAL BANKING', 'Editable BCM Risk');

        $this->actingAs($user);

        $this->get(route('risks.edit', $risk->id))
            ->assertOk()
            ->assertSee('Mitigation Playbook (Optional)')
            ->assertSee('Add Treatment Manually')
            ->assertSee('ensureAtLeastOneTreatmentRow', false)
            ->assertSee('UPDATE & LINK TO SERVICES', false);

        $response = $this->put(route('risks.update', $risk->id), [
            'risk_title' => 'Editable BCM Risk Updated',
            'risk_category_id' => $risk->risk_category_id,
            'risk_owner' => $risk->risk_owner,
            'status' => 'Open',
            'post_submit_action' => 'bcm',
        ]);

        $response->assertRedirect(route('risks.show', $risk->id) . '#services');

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
