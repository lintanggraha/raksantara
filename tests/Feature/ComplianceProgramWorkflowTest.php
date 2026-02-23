<?php

namespace Tests\Feature;

use App\ComplianceWorkProgram;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ComplianceProgramWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_compliance_user_can_create_internal_audit_program_with_new_fields(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $payload = [
            'program_key_initiative' => 'Audit Program 2026',
            'description_rationales' => 'Audit proses end-to-end untuk area kritikal.',
            'deliverables' => 'Laporan audit dan rekomendasi perbaikan.',
            'how_to_measure' => 'Persentase temuan yang ditutup tepat waktu.',
            'budget' => '120.000.000',
            'support_needed' => 'Butuh dukungan data lintas unit.',
        ];

        $response = $this->post(route('compliance.internal-audit.store'), $payload);

        $response->assertRedirect(route('compliance.internal-audit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('compliance_work_programs', [
            'section' => ComplianceWorkProgram::SECTION_INTERNAL_AUDIT,
            'program_kerja' => 'Audit Program 2026',
            'deskripsi' => 'Audit proses end-to-end untuk area kritikal.',
            'target_sasaran_kerja' => 'Laporan audit dan rekomendasi perbaikan.',
            'alignment_champion_program' => 'Persentase temuan yang ditutup tepat waktu.',
            'alignment_risk_profile_2025' => '120.000.000',
            'support_needed' => 'Butuh dukungan data lintas unit.',
        ]);

        $workId = DB::table('compliance_work_programs')
            ->where('section', ComplianceWorkProgram::SECTION_INTERNAL_AUDIT)
            ->where('program_kerja', 'Audit Program 2026')
            ->value('work_id');

        $this->assertMatchesRegularExpression('/^IA-' . now()->year . '-\d{3}$/', (string) $workId);
    }

    public function test_non_compliance_user_is_blocked_from_storing_program(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING');
        $this->actingAs($user);

        $response = $this->post(route('compliance.internal-audit.store'), [
            'program_key_initiative' => 'Blocked Program',
            'description_rationales' => 'Should not pass middleware.',
            'deliverables' => 'Should not be stored.',
            'how_to_measure' => 'Should not be measured.',
            'budget' => '10.000',
        ]);

        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('error', 'Menu Compliance hanya untuk divisi Compliance.');
        $this->assertDatabaseMissing('compliance_work_programs', [
            'program_kerja' => 'Blocked Program',
        ]);
    }

    public function test_cross_section_edit_route_returns_not_found(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $workId = ComplianceWorkProgram::nextWorkIdPreview(ComplianceWorkProgram::SECTION_INTERNAL_AUDIT);

        $program = ComplianceWorkProgram::create([
            'section' => ComplianceWorkProgram::SECTION_INTERNAL_AUDIT,
            'work_id' => $workId,
            'program_kerja' => 'Section Isolation Program',
            'deskripsi' => 'Program for section isolation test.',
            'target_sasaran_kerja' => 'Ensure route level isolation.',
            'kategori' => 'Program Kerja',
            'q1' => true,
            'q2' => false,
            'q3' => false,
            'q4' => false,
            'alignment_champion_program' => 'Champion test',
            'alignment_risk_profile_2025' => 'Profile test',
            'created_by' => $user->id,
        ]);

        $response = $this->get(route('compliance.risk-management.edit', $program->id));
        $response->assertNotFound();
    }

    public function test_program_creation_rejects_script_like_payload(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $response = $this
            ->from(route('compliance.compliance.create'))
            ->post(route('compliance.compliance.store'), [
                'program_key_initiative' => '<script>alert(1)</script>',
                'description_rationales' => 'Bad payload',
                'deliverables' => 'Bad payload',
                'how_to_measure' => 'Bad payload',
                'budget' => '1000000',
            ]);

        $response->assertRedirect(route('compliance.compliance.create'));
        $response->assertSessionHasErrors(['program_key_initiative']);
        $this->assertDatabaseMissing('compliance_work_programs', [
            'program_kerja' => '<script>alert(1)</script>',
        ]);
    }

    public function test_program_creation_rejects_budget_with_letters(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $this->actingAs($user);

        $response = $this
            ->from(route('compliance.compliance.create'))
            ->post(route('compliance.compliance.store'), [
                'program_key_initiative' => 'Budget Validation Program',
                'description_rationales' => 'Valid field',
                'deliverables' => 'Valid field',
                'how_to_measure' => 'Valid field',
                'budget' => '100JT',
            ]);

        $response->assertRedirect(route('compliance.compliance.create'));
        $response->assertSessionHasErrors(['budget']);
        $this->assertDatabaseMissing('compliance_work_programs', [
            'program_kerja' => 'Budget Validation Program',
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
