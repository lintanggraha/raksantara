<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskComment;
use App\RiskStatusChangeRequest;
use App\Role;
use App\RoleAssignment;
use App\ScheduledReport;
use App\Support\RiskAccess;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Compliance owns the application: it sees every division's records and may
 * edit anything, while maker-checker and user administration stay in place.
 */
class ComplianceOwnershipAccessTest extends TestCase
{
    use RefreshDatabase;

    private const COMPLIANCE = 'CO - COMPLIANCE';
    private const OTHER = 'BT - DIGITAL BANKING';

    public function test_role_assignments_never_restrict_compliance(): void
    {
        $compliance = $this->createUser(self::COMPLIANCE);
        $this->assignRole($compliance, 'auditor'); // view-only role
        $risk = $this->createRisk($this->createUser(self::OTHER));

        $compliance = $compliance->fresh();
        $this->assertTrue($compliance->hasPermission('risks.manage'));
        $this->assertTrue(RiskAccess::canManage($compliance, $risk));
        $this->actingAs($compliance)->get(route('risks.edit', $risk->id))->assertOk();

        // The same role still restricts other divisions.
        $auditor = $this->createUser(self::OTHER);
        $this->assignRole($auditor, 'auditor');
        $this->assertFalse(RiskAccess::canManage($auditor->fresh(), $risk));
    }

    public function test_compliance_can_edit_and_delete_other_users_comments(): void
    {
        $author = $this->createUser(self::OTHER);
        $risk = $this->createRisk($author);
        $comment = RiskComment::create(['risk_id' => $risk->id, 'user_id' => $author->id, 'content' => 'Komentar asli']);

        $this->actingAs($this->createUser(self::OTHER))
            ->put(route('risks.comments.update', [$risk->id, $comment->id]), ['content' => 'Diubah orang lain'])
            ->assertForbidden();

        $compliance = $this->createUser(self::COMPLIANCE);
        $this->actingAs($compliance)
            ->put(route('risks.comments.update', [$risk->id, $comment->id]), ['content' => 'Dirapikan Compliance'])
            ->assertRedirect();
        $this->assertSame('Dirapikan Compliance', $comment->fresh()->content);

        $this->actingAs($compliance)
            ->delete(route('risks.comments.destroy', [$risk->id, $comment->id]))
            ->assertRedirect();
        $this->assertNull(RiskComment::find($comment->id));
    }

    public function test_compliance_manages_other_users_scheduled_reports_without_taking_them_over(): void
    {
        $owner = $this->createUser(self::OTHER);
        $schedule = ScheduledReport::query()->create([
            'user_id' => $owner->id,
            'name' => 'Laporan Milik Divisi Lain',
            'scope' => 'monitoring',
            'format' => 'xlsx',
            'frequency' => 'daily',
            'send_time' => '06:00',
            'timezone' => 'UTC',
            'recipients' => [$owner->email],
            'filters' => [],
            'is_active' => true,
            'next_run_at' => now()->addDay(),
        ]);

        $this->actingAs($this->createUser(self::OTHER))
            ->patch(route('reports.scheduled.toggle', $schedule->id))
            ->assertForbidden();

        $this->actingAs($this->createUser(self::COMPLIANCE))
            ->get(route('reports.scheduled.index'))
            ->assertOk()
            ->assertSee('Laporan Milik Divisi Lain');
        $this->patch(route('reports.scheduled.toggle', $schedule->id))->assertRedirect();

        $schedule->refresh();
        $this->assertFalse((bool) $schedule->is_active);
        $this->assertSame($owner->id, (int) $schedule->user_id);
    }

    public function test_compliance_reviews_status_requests_except_its_own(): void
    {
        $requester = $this->createUser(self::OTHER);
        $compliance = $this->createUser(self::COMPLIANCE);
        $risk = $this->createRisk($requester);

        $otherRequest = $this->statusRequest($risk, $requester);
        $this->actingAs($compliance)->get(route('risk-status-approvals.index'))->assertOk();
        $this->put(route('risk-status-approvals.approve', $otherRequest->id))->assertRedirect();
        $this->assertSame('Closed', $risk->fresh()->status);

        $risk->update(['status' => 'Open']);
        $ownRequest = $this->statusRequest($risk, $compliance);
        $this->put(route('risk-status-approvals.approve', $ownRequest->id))->assertForbidden();
        $this->put(route('risk-status-approvals.reject', $ownRequest->id), ['review_note' => 'x'])->assertForbidden();
        $this->assertSame('pending', $ownRequest->fresh()->status);

        $this->actingAs($requester)->get(route('risk-status-approvals.index'))->assertForbidden();
    }

    public function test_compliance_manages_parameters_but_not_users(): void
    {
        $this->actingAs($this->createUser(self::COMPLIANCE));
        $this->get(route('parameters.index'))->assertOk();
        $this->get(route('users.index'))->assertStatus(403);
        $this->get(route('access-control.index'))->assertStatus(403);

        $this->actingAs($this->createUser(self::OTHER));
        $this->get(route('parameters.index'))->assertForbidden();
    }

    private function statusRequest(Risk $risk, User $requester): RiskStatusChangeRequest
    {
        return RiskStatusChangeRequest::create([
            'risk_id' => $risk->id,
            'requested_by' => $requester->id,
            'from_status' => 'Open',
            'to_status' => 'Closed',
            'reason' => 'Treatment selesai',
            'status' => 'pending',
        ]);
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Owner Test ' . $suffix,
            'username' => 'owner_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'is_superadmin' => false,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('test-password'),
        ]);
    }

    private function assignRole(User $user, string $roleSlug): void
    {
        RoleAssignment::create([
            'user_id' => $user->id,
            'role_id' => Role::query()->where('slug', $roleSlug)->firstOrFail()->id,
            'organizational_unit_id' => null,
            'assigned_by' => null,
            'expires_at' => null,
        ]);
    }

    private function createRisk(User $creator): Risk
    {
        return Risk::create([
            'risk_id' => 'OWN-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => 'Compliance ownership test risk',
            'risk_category_id' => RiskCategory::query()->firstOrCreate(['name' => 'Compliance Ownership Test'])->id,
            'risk_owner' => self::OTHER,
            'status' => 'Open',
            'created_by' => $creator->id,
        ]);
    }
}
