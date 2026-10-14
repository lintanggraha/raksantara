<?php

namespace Tests\Feature;

use App\ComplianceWorkProgram;
use App\Risk;
use App\RiskCategory;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserApprovalAndIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pending_user_is_redirected_to_pending_page_when_opening_risk_register(): void
    {
        $user = $this->createPendingUser('BT - DIGITAL BANKING');
        $this->actingAs($user);

        $response = $this->get(route('risks.index'));

        $response->assertRedirect(route('account.pending'));
    }

    public function test_superadmin_can_verify_user_division_from_user_management(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $pendingUser = $this->createPendingUser('BT - DIGITAL BANKING');

        $this->actingAs($superadmin);

        $response = $this->put(route('users.updateDivision', $pendingUser->id), [
            'division' => 'BT - DIGITAL BANKING',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'division' => 'BT - DIGITAL BANKING',
            'pending_division' => null,
        ]);
        $this->assertNotNull(optional(User::find($pendingUser->id))->division_verified_at);
    }

    public function test_superadmin_can_create_regular_user_with_supplied_password(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $this->actingAs($superadmin);

        $response = $this->post(route('users.store'), [
            'name' => 'Created User',
            'username' => 'created_user',
            'email' => 'created@example.test',
            'division' => 'BT - DIGITAL BANKING',
            'password' => 'CreatedPassword123',
            'password_confirmation' => 'CreatedPassword123',
        ]);

        $response->assertRedirect(route('users.index', ['status' => 'all']));
        $response->assertSessionHas('success');

        $createdUser = User::query()->where('username', 'created_user')->firstOrFail();
        $this->assertFalse((bool) $createdUser->is_superadmin);
        $this->assertSame('BT - DIGITAL BANKING', $createdUser->division);
        $this->assertNull($createdUser->pending_division);
        $this->assertNotNull($createdUser->division_verified_at);
        $this->assertFalse((bool) $createdUser->force_password_change);
        $this->assertNotNull($createdUser->password_changed_at);
        $this->assertTrue(Hash::check('CreatedPassword123', (string) $createdUser->password));
    }

    public function test_superadmin_can_create_user_with_temporary_password(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $this->actingAs($superadmin);

        $response = $this->post(route('users.store'), [
            'name' => 'Temporary User',
            'username' => 'temporary_user',
            'email' => 'temporary@example.test',
            'division' => 'BT - DIGITAL BANKING',
        ]);

        $response->assertRedirect(route('users.index', ['status' => 'all']));
        $response->assertSessionHas('temporary_password');

        $temporaryPassword = (string) session('temporary_password');
        $createdUser = User::query()->where('username', 'temporary_user')->firstOrFail();
        $this->assertTrue((bool) $createdUser->force_password_change);
        $this->assertNull($createdUser->password_changed_at);
        $this->assertTrue(Hash::check($temporaryPassword, (string) $createdUser->password));
    }

    public function test_non_superadmin_cannot_create_user(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING');
        $this->actingAs($user);

        $this->post(route('users.store'), [
            'name' => 'Unauthorized User',
            'username' => 'unauthorized_user',
            'email' => 'unauthorized@example.test',
            'division' => 'BT - DIGITAL BANKING',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'unauthorized_user']);
    }

    public function test_non_superadmin_cannot_access_user_management_page(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING');
        $this->actingAs($user);

        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_superadmin_can_reset_password_and_force_user_to_change_it(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $user = $this->createUser('BT - DIGITAL BANKING', false);

        $this->actingAs($superadmin);

        $response = $this->put(route('users.resetPassword', $user->id));

        $response->assertSessionHas('success');
        $response->assertSessionHas('temporary_password');

        $temporaryPassword = (string) session('temporary_password');
        $this->assertNotSame('', trim($temporaryPassword));

        $user->refresh();
        $this->assertTrue((bool) $user->force_password_change);
        $this->assertNull($user->password_changed_at);
        $this->assertTrue(Hash::check($temporaryPassword, (string) $user->password));
    }

    public function test_forced_user_is_redirected_to_change_password_page(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING');
        $user->force_password_change = true;
        $user->password_changed_at = null;
        $user->save();

        $this->actingAs($user);

        $this->get(route('risks.index'))
            ->assertRedirect(route('change.password'));
    }

    public function test_forced_user_can_change_password_and_clear_flag(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING');
        $user->force_password_change = true;
        $user->password_changed_at = null;
        $user->save();

        $this->actingAs($user);

        $response = $this->post(route('profile.changePassword'), [
            'current_password' => 'password123',
            'new_password' => 'NewPassword123',
            'new_password_confirmation' => 'NewPassword123',
        ]);

        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertFalse((bool) $user->force_password_change);
        $this->assertNotNull($user->password_changed_at);
        $this->assertTrue(Hash::check('NewPassword123', (string) $user->password));
    }

    public function test_risk_store_generates_id_above_thousand_without_resetting_sequence(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $categoryId = $this->ensureRiskCategoryId();
        $year = (int) now()->year;

        DB::table('risks')->insert([
            'risk_id' => 'RISK-' . $year . '-1000',
            'year' => $year,
            'risk_title' => 'Seed Risk',
            'risk_description' => 'Seed row for rollover test',
            'risk_category_id' => $categoryId,
            'risk_owner' => 'CO - COMPLIANCE',
            'status' => 'Open',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'risk_title' => 'Sequence Rollover Risk',
            'risk_category_id' => $categoryId,
            'risk_owner' => 'CO - COMPLIANCE',
        ];

        $response = $this->post(route('risks.store'), $payload);
        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('success');

        $createdRiskId = (string) DB::table('risks')
            ->where('risk_title', 'Sequence Rollover Risk')
            ->value('risk_id');

        $this->assertSame('RISK-' . $year . '-1001', $createdRiskId);
    }

    public function test_work_id_preview_handles_numbers_above_999(): void
    {
        $year = (int) now()->year;

        ComplianceWorkProgram::create($this->programPayload('IA-' . $year . '-999'));
        $this->assertSame(
            'IA-' . $year . '-1000',
            ComplianceWorkProgram::nextWorkIdPreview(ComplianceWorkProgram::SECTION_INTERNAL_AUDIT)
        );

        ComplianceWorkProgram::create($this->programPayload('IA-' . $year . '-1000'));
        $this->assertSame(
            'IA-' . $year . '-1001',
            ComplianceWorkProgram::nextWorkIdPreview(ComplianceWorkProgram::SECTION_INTERNAL_AUDIT)
        );
    }

    public function test_radar_code_validation_rejects_more_than_ten_characters(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $payload = [
            'risk_title' => 'Radar Code Validation Risk',
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'CO - COMPLIANCE',
            'radar_code' => 'ABCDEFGHIJK',
        ];

        $response = $this
            ->from(route('risks.create'))
            ->post(route('risks.store'), $payload);

        $response->assertRedirect(route('risks.create'));
        $response->assertSessionHasErrors(['radar_code']);
    }

    public function test_inherent_analysis_enforces_single_row_per_risk(): void
    {
        $risk = $this->createRiskRecord();

        RiskInherentAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 3,
            'impact_score' => 3,
            'inherent_risk_score' => 13,
            'risk_level' => 'Moderate',
        ]);

        $this->expectException(QueryException::class);

        RiskInherentAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 4,
            'impact_score' => 4,
            'inherent_risk_score' => 19,
            'risk_level' => 'Moderate to High',
        ]);
    }

    public function test_residual_analysis_enforces_single_row_per_risk(): void
    {
        $risk = $this->createRiskRecord();

        RiskResidualAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 2,
            'impact_score' => 2,
            'residual_risk_score' => 6,
            'risk_level' => 'Low to Moderate',
        ]);

        $this->expectException(QueryException::class);

        RiskResidualAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 3,
            'impact_score' => 3,
            'residual_risk_score' => 13,
            'risk_level' => 'Moderate',
        ]);
    }

    private function createRiskRecord(): Risk
    {
        return Risk::create([
            'risk_id' => 'TEST-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => 'Risk ' . Str::random(8),
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'CO - COMPLIANCE',
            'status' => 'Open',
        ]);
    }

    private function programPayload(string $workId): array
    {
        return [
            'section' => ComplianceWorkProgram::SECTION_INTERNAL_AUDIT,
            'work_id' => $workId,
            'program_kerja' => 'Program ' . Str::random(5),
            'deskripsi' => 'Description ' . Str::random(12),
            'target_sasaran_kerja' => 'Target ' . Str::random(8),
            'kategori' => 'Program Kerja',
            'alignment_champion_program' => 'How to measure',
            'alignment_risk_profile_2025' => '1000000',
        ];
    }

    private function ensureRiskCategoryId(): int
    {
        $existingId = RiskCategory::query()->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        return (int) RiskCategory::query()->insertGetId([
            'name' => 'Test Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPendingUser(string $pendingDivision): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Pending User ' . $suffix,
            'username' => 'pending_' . $suffix,
            'pending_division' => $pendingDivision,
            'division' => null,
            'division_verified_at' => null,
            'is_superadmin' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin = false): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Test User ' . $suffix,
            'username' => 'user_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
