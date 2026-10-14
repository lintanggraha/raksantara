<?php

namespace Tests\Feature;

use App\BusinessService;
use App\Risk;
use App\RiskCategory;
use App\Support\BusinessContinuity;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessContinuityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private const UNIT = 'BT - DIGITAL BANKING';

    public function test_mtpd_and_tier_follow_the_first_unacceptable_impact(): void
    {
        $this->assertSame(240, BusinessContinuity::mtpdMinutes($this->matrix([60 => 2, 240 => 3])));
        $this->assertSame('Tier 1', BusinessContinuity::tierFor(240));
        $this->assertSame('Tier 2', BusinessContinuity::tierFor(1440));
        $this->assertSame('Tier 3', BusinessContinuity::tierFor(4320));
        $this->assertNull(BusinessContinuity::mtpdMinutes($this->matrix([60 => 1])));
        $this->assertSame('Tier 4', BusinessContinuity::tierFor(null));
    }

    public function test_service_bia_is_validated_and_needs_a_different_approver(): void
    {
        $maker = $this->createUser();
        $checker = $this->createUser();

        $this->actingAs($maker)
            ->post(route('bcm.services.store'), ['name' => 'Layanan BI-FAST', 'owner_unit' => self::UNIT])
            ->assertRedirect();
        $service = BusinessService::query()->where('name', 'Layanan BI-FAST')->firstOrFail();
        $this->assertSame('SVC-001', $service->code);
        $this->assertSame('no_bia', $service->continuityStatus());

        // RTO beyond MTPD is rejected.
        $this->put(route('bcm.bia.update', $service), $this->biaPayload([60 => 2, 240 => 3], 480))
            ->assertSessionHasErrors('rto_minutes', null, 'bia');
        // Impact that falls as the outage gets longer is rejected.
        $falling = $this->biaPayload([60 => 3], 60);
        $falling['impact'][240]['financial'] = 1;
        $this->put(route('bcm.bia.update', $service), $falling)->assertSessionHasErrors('impact.240.financial', null, 'bia');
        $this->assertNull($service->fresh()->bia);

        $this->put(route('bcm.bia.update', $service), $this->biaPayload([60 => 2, 240 => 3], 120))->assertRedirect();
        $bia = $service->fresh()->bia;
        $this->assertSame(240, (int) $bia->mtpd_minutes);
        $this->assertSame('Tier 1', $bia->tier);
        $this->assertSame($maker->id, (int) $bia->assessed_by);
        $this->assertNull($bia->approved_at);

        // Maker cannot approve their own BIA; a colleague can.
        $this->post(route('bcm.bia.approve', $service))->assertForbidden();
        $this->actingAs($checker)->post(route('bcm.bia.approve', $service))->assertRedirect();
        $this->assertNotNull($service->fresh()->bia->approved_at);

        // Re-saving the same BIA keeps the approval; a real change resets it.
        $this->put(route('bcm.bia.update', $service), $this->biaPayload([60 => 2, 240 => 3], 120));
        $this->assertNotNull($service->fresh()->bia->approved_at);
        $this->put(route('bcm.bia.update', $service), $this->biaPayload([60 => 2, 240 => 3], 60));
        $this->assertNull($service->fresh()->bia->approved_at);
        $this->assertSame($checker->id, (int) $service->fresh()->bia->assessed_by);
    }

    public function test_bcp_and_tests_follow_the_bia_and_drive_status_and_dashboard(): void
    {
        $maker = $this->createUser();
        $checker = $this->createUser();
        $service = BusinessService::create(['code' => 'SVC-010', 'name' => 'Core Banking', 'owner_unit' => self::UNIT, 'status' => 'active']);

        $this->actingAs($maker);
        $this->put(route('bcm.bcp.update', $service), $this->bcpPayload('Quarterly'))->assertStatus(422);
        $this->post(route('bcm.tests.store', $service), $this->testPayload(now()))->assertStatus(422);

        $this->put(route('bcm.bia.update', $service), $this->biaPayload([1440 => 3], 240))->assertRedirect();
        $this->actingAs($checker)->post(route('bcm.bia.approve', $service));
        $this->actingAs($maker)->put(route('bcm.bcp.update', $service), $this->bcpPayload('Quarterly'))->assertRedirect();
        $this->assertSame('never_tested', $service->fresh()->continuityStatus());

        $this->post(route('bcm.tests.store', $service), $this->testPayload(now()->subMonths(5)))->assertRedirect();
        $this->assertSame('test_overdue', $service->fresh()->continuityStatus());

        $assurance = $this->getJson(route('dashboard.sections', 'assurance'))->json('data.items');
        $this->assertContains('Uji BCP', array_column($assurance, 'label'));

        $this->post(route('bcm.tests.store', $service), $this->testPayload(now()->subDay()))->assertRedirect();
        $this->assertSame('ok', $service->fresh()->continuityStatus());

        $this->get(route('bcm.services.show', $service))
            ->assertOk()
            ->assertSee('Dalam RTO')
            ->assertSee('Tier 2');
    }

    public function test_risks_link_to_services_within_access_scope(): void
    {
        $user = $this->createUser();
        $foreignUser = $this->createUser('CO - COMPLIANCE');
        $service = BusinessService::create(['code' => 'SVC-020', 'name' => 'Mobile Banking', 'owner_unit' => self::UNIT, 'status' => 'active']);
        $own = $this->createRisk($user, self::UNIT, 'Own disruption risk');
        $foreign = $this->createRisk($foreignUser, 'CO - COMPLIANCE', 'Foreign disruption risk');

        $this->actingAs($user);
        $this->post(route('bcm.risks.attach', $service), ['risk_id' => $foreign->id])->assertNotFound();
        $this->post(route('bcm.risks.attach', $service), ['risk_id' => $own->id])->assertRedirect();
        $this->assertTrue($service->risks()->whereKey($own->id)->exists());

        $this->get(route('risks.show', $own->id))->assertOk()->assertSee('Mobile Banking')->assertSee('Layanan terdampak');

        $this->delete(route('bcm.risks.detach', [$service, $own->id]))->assertRedirect();
        $this->post(route('risks.business-services.attach', $own->id), ['business_service_id' => $service->id])
            ->assertRedirect(route('risks.show', $own->id) . '#services');
        $this->assertTrue($service->risks()->whereKey($own->id)->exists());
    }

    public function test_draft_catalogue_links_unit_risks_and_rolls_back_untouched_drafts(): void
    {
        $user = $this->createUser();
        $risk = $this->createRisk($user, self::UNIT, 'Catalogue risk');

        $this->artisan('bcm:seed-service-catalog')->assertSuccessful();
        $digital = BusinessService::query()->where('owner_unit', self::UNIT)->firstOrFail();
        $this->assertSame('draft', $digital->status);
        $this->assertTrue($digital->risks()->whereKey($risk->id)->exists());

        $this->artisan('bcm:seed-service-catalog')->assertSuccessful(); // idempotent
        $this->assertSame(1, BusinessService::query()->where('owner_unit', self::UNIT)->count());

        $this->artisan('bcm:seed-service-catalog', ['--rollback' => true])->assertSuccessful();
        $this->assertSame(0, BusinessService::query()->count());
    }

    // ---------------------------------------------------------------------

    /**
     * @param array<int, int> $from minutes => level reached from that horizon onwards
     */
    private function matrix(array $from): array
    {
        $matrix = [];
        $level = 1;
        foreach (array_keys(BusinessContinuity::HORIZONS) as $minutes) {
            $level = $from[$minutes] ?? $level;
            foreach (array_keys(BusinessContinuity::CATEGORIES) as $category) {
                $matrix[$minutes][$category] = $category === 'operational' ? $level : min($level, 2);
            }
        }

        return $matrix;
    }

    private function biaPayload(array $from, int $rto): array
    {
        return [
            'impact' => $this->matrix($from),
            'rto_minutes' => $rto,
            'rpo_minutes' => 15,
            'next_review_at' => now()->addYear()->toDateString(),
        ];
    }

    private function bcpPayload(string $frequency): array
    {
        return [
            'activation_criteria' => 'Layanan tidak tersedia lebih dari 30 menit.',
            'strategy_summary' => 'Failover ke DRC.',
            'recovery_procedures' => 'Ikuti runbook DR.',
            'contacts' => 'Duty manager TI.',
            'testing_frequency' => $frequency,
        ];
    }

    private function testPayload($date): array
    {
        return [
            'tested_at' => $date->toDateString(),
            'test_type' => 'full_dr',
            'result' => 'pass',
            'actual_recovery_minutes' => 150,
        ];
    }

    private function createUser(string $division = self::UNIT): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'BCM User ' . $suffix,
            'username' => 'bcm_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createRisk(User $creator, string $owner, string $title): Risk
    {
        $category = RiskCategory::query()->value('id') ?? RiskCategory::query()->insertGetId(['name' => 'BCM Category', 'created_at' => now(), 'updated_at' => now()]);

        return Risk::create([
            'risk_id' => 'BCM-' . Str::upper(Str::random(6)),
            'year' => 2026,
            'risk_title' => $title,
            'risk_category_id' => $category,
            'risk_owner' => $owner,
            'status' => 'Open',
            'created_by' => $creator->id,
        ]);
    }
}
