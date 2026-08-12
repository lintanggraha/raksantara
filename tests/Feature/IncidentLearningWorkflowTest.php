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

class IncidentLearningWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_complete_capa_and_post_incident_learning_review(): void
    {
        $user = $this->createUser();
        $risk = $this->createRisk($user);
        $incident = RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => $risk->id,
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Service disruption',
            'incident_description' => 'A temporary service disruption affected transaction processing.',
            'occurred_at' => now(),
            'severity' => 'High',
            'status' => 'Investigating',
            'mitigation_verdict' => 'Not Tested',
            'root_cause' => 'Capacity threshold was not calibrated.',
        ]);

        $this->actingAs($user)
            ->post(route('incidents.learning.actions.store', $incident->id), [
                'action_type' => 'Corrective',
                'title' => 'Calibrate capacity threshold',
                'description' => 'Review and update capacity alert threshold.',
                'owner_name' => 'Digital Banking Operations',
                'due_at' => now()->addDays(7)->toDateString(),
            ])
            ->assertRedirect(route('incidents.learning.show', $incident->id));

        $actionId = (int) $incident->actions()->value('id');

        $this->actingAs($user)
            ->put(route('incidents.learning.actions.update', [$incident->id, $actionId]), [
                'status' => 'completed',
                'completion_notes' => 'Threshold was updated and validated.',
            ])
            ->assertRedirect(route('incidents.learning.show', $incident->id));

        $this->actingAs($user)
            ->put(route('incidents.learning.review', $incident->id), [
                'lessons_learned' => 'Capacity KRIs and the linked control require quarterly review after material incidents.',
                'risk_review_status' => 'reviewed',
                'control_review_status' => 'reviewed',
                'kri_review_status' => 'reviewed',
            ])
            ->assertRedirect(route('incidents.learning.show', $incident->id));

        $this->assertDatabaseHas('risk_incident_actions', [
            'id' => $actionId,
            'status' => 'completed',
            'completed_by' => $user->id,
        ]);
        $this->assertDatabaseHas('risk_incidents', [
            'id' => $incident->id,
            'risk_review_status' => 'reviewed',
            'control_review_status' => 'reviewed',
            'kri_review_status' => 'reviewed',
            'post_incident_reviewed_by' => $user->id,
        ]);
    }

    private function createUser(): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Incident Learning User ' . $suffix,
            'username' => 'incident_learning_' . $suffix,
            'division' => 'BT - DIGITAL BANKING',
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createRisk(User $user): Risk
    {
        $category = RiskCategory::query()->firstOrCreate(['name' => 'Incident Learning Category']);

        return Risk::create([
            'risk_id' => 'INC-LRN-' . Str::upper(Str::random(6)),
            'year' => now()->year,
            'risk_title' => 'Incident Learning Risk',
            'risk_category_id' => $category->id,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'status' => 'Open',
            'created_by' => $user->id,
        ]);
    }
}
