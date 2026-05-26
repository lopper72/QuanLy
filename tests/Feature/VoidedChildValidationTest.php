<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\TrainingSession;
use App\Models\Assessment;
use App\Models\BehaviorLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoidedChildValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Child $voidedChild;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->voidedChild = Child::factory()->create([
            'status' => 'voided',
            'voided_at' => now(),
        ]);
    }

    public function test_cannot_create_training_session_for_voided_child(): void
    {
        $response = $this->post(route('training.store'), [
            'child_id' => $this->voidedChild->id,
            'session_date' => now()->format('Y-m-d'),
            'status' => 'planned',
        ]);

        $response->assertSessionHasErrors(['child_id']);
        $this->assertDatabaseMissing('training_sessions', [
            'child_id' => $this->voidedChild->id,
        ]);
    }

    public function test_cannot_update_training_session_for_voided_child(): void
    {
        $session = TrainingSession::factory()->create(); // active child

        $response = $this->put(route('training.update', $session), [
            'child_id' => $this->voidedChild->id, // trying to reassign to a voided child
            'session_date' => now()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $response->assertSessionHasErrors(['child_id']);
    }

    public function test_cannot_create_behavior_log_for_voided_child(): void
    {
        $response = $this->post(route('behavior.store'), [
            'child_id' => $this->voidedChild->id,
            'behavior_type' => 'tantrum',
            'severity' => 'medium',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors(['child_id']);
        $this->assertDatabaseMissing('behavior_logs', [
            'child_id' => $this->voidedChild->id,
        ]);
    }

    public function test_cannot_create_assessment_for_voided_child(): void
    {
        $response = $this->post(route('assessment.store'), [
            'child_id' => $this->voidedChild->id,
            'assessment_date' => now()->format('Y-m-d'),
            'items' => [
                ['skill_name' => 'gross_motor', 'score' => 80],
            ],
        ]);

        $response->assertSessionHasErrors(['child_id']);
        $this->assertDatabaseMissing('assessments', [
            'child_id' => $this->voidedChild->id,
        ]);
    }
}