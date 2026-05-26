<?php

namespace Tests\Feature;

use App\Models\BehaviorLog;
use App\Models\Child;
use App\Models\Exercise;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class BehaviorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * Test list behavior logs page is loaded correctly.
     */
    public function test_can_list_behavior_logs(): void
    {
        $child = Child::factory()->create();
        BehaviorLog::factory()->count(3)->create(['child_id' => $child->id]);

        $response = $this->get('/behavior');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('behaviorLogs', 3)
            ->has('behaviorGroups', 1)
            ->has('summary')
            ->has('children')
            ->has('activeChildren')
            ->has('behaviorTypes')
            ->has('severities')
            ->has('filters')
            ->where('filters.child_status', 'active')
        );
    }

    public function test_behavior_page_has_clear_create_button(): void
    {
        Child::factory()->create(['status' => 'active']);

        $response = $this->get('/behavior');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('activeChildren', 1)
        );

        $this->assertStringContainsString(
            '+ Ghi nhận hành vi',
            file_get_contents(resource_path('js/Pages/Behavior/Index.vue'))
        );
    }

    public function test_behavior_default_excludes_paused_and_voided_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);
        $deletedChild = Child::factory()->create(['status' => 'active']);

        BehaviorLog::factory()->create(['child_id' => $activeChild->id]);
        BehaviorLog::factory()->create(['child_id' => $pausedChild->id]);
        BehaviorLog::factory()->create(['child_id' => $voidedChild->id]);
        BehaviorLog::factory()->create(['child_id' => $deletedChild->id]);
        $deletedChild->delete();

        $response = $this->get('/behavior');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.child.id', $activeChild->id)
            ->has('behaviorGroups', 1)
            ->where('behaviorGroups.0.child.id', $activeChild->id)
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
        );
    }

    public function test_behavior_voided_filter_does_not_show_voided_historical_logs(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        BehaviorLog::factory()->create(['child_id' => $activeChild->id]);
        BehaviorLog::factory()->create(['child_id' => $voidedChild->id]);

        $response = $this->get('/behavior?child_status=voided');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->where('filters.child_status', 'active')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.child.id', $activeChild->id)
            ->has('behaviorGroups', 1)
            ->where('behaviorGroups.0.child.status', 'active')
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
        );
    }

    public function test_behavior_all_filter_still_only_shows_active_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        BehaviorLog::factory()->create(['child_id' => $activeChild->id]);
        BehaviorLog::factory()->create(['child_id' => $pausedChild->id]);
        BehaviorLog::factory()->create(['child_id' => $voidedChild->id]);

        $response = $this->get('/behavior?child_status=all');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->where('filters.child_status', 'active')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.child.id', $activeChild->id)
            ->has('behaviorGroups', 1)
            ->has('children', 1)
        );
    }

    public function test_grouped_timeline_response_includes_child_and_logs(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        BehaviorLog::factory()->count(2)->create(['child_id' => $child->id]);

        $response = $this->get('/behavior');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('behaviorGroups', 1)
            ->where('behaviorGroups.0.child.id', $child->id)
            ->where('behaviorGroups.0.child.status', 'active')
            ->has('behaviorGroups.0.logs', 2)
        );
    }

    /**
     * Test filtering behavior logs.
     */
    public function test_can_filter_behavior_logs(): void
    {
        $child1 = Child::factory()->create();
        $child2 = Child::factory()->create();

        BehaviorLog::factory()->create([
            'child_id' => $child1->id,
            'behavior_type' => 'aggression',
            'severity' => 'high'
        ]);

        BehaviorLog::factory()->create([
            'child_id' => $child2->id,
            'behavior_type' => 'other',
            'severity' => 'low'
        ]);

        // Filter by child_id
        $response = $this->get("/behavior?child_id={$child1->id}");
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.child.id', $child1->id)
        );

        // Filter by behavior_type
        $response = $this->get('/behavior?behavior_type=other');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.behavior_type', 'other')
        );

        // Filter by severity
        $response = $this->get('/behavior?severity=high');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.severity', 'high')
        );
    }

    /**
     * Test create behavior log page.
     */
    public function test_can_view_create_behavior_log_page(): void
    {
        Child::factory()->count(2)->create();

        $response = $this->get('/behavior/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Create')
            ->has('children', 2)
            ->has('behaviorTypes')
            ->has('severities')
        );
    }

    public function test_create_behavior_page_only_lists_active_children(): void
    {
        $activeChild = Child::factory()->create([
            'status' => 'active',
            'full_name' => 'Bé đang can thiệp',
        ]);
        Child::factory()->create([
            'status' => 'voided',
            'full_name' => 'Bé đã ngừng',
        ]);
        $deletedChild = Child::factory()->create([
            'status' => 'active',
            'full_name' => 'Bé đã xóa',
        ]);
        $deletedChild->delete();

        $response = $this->get('/behavior/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Create')
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
            ->where('children.0.full_name', 'Bé đang can thiệp')
        );
    }

    /**
     * Test storing behavior log successfully.
     */
    public function test_can_store_behavior_log(): void
    {
        $child = Child::factory()->create();
        $payload = [
            'child_id' => $child->id,
            'behavior_type' => 'tantrum',
            'severity' => 'medium',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
            'trigger' => 'Asked to transition to math class',
            'response' => 'Provided 5 minute sensory break',
            'note' => 'Child was tired'
        ];

        $response = $this->post('/behavior', $payload);

        $response
            ->assertRedirect('/behavior')
            ->assertSessionHas('success', 'Đã ghi nhận hành vi.');

        $this->assertDatabaseHas('behavior_logs', [
            'child_id' => $child->id,
            'behavior_type' => 'tantrum',
            'severity' => 'medium',
            'trigger' => 'Asked to transition to math class',
            'note' => 'Child was tired',
        ]);
    }

    public function test_can_create_behavior_log_linked_to_training_session(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);

        $response = $this->post('/behavior', [
            'child_id' => $child->id,
            'training_session_id' => $session->id,
            'behavior_type' => 'avoidance',
            'severity' => 'medium',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
            'trigger' => 'Bé né tránh khi chuyển bài tập',
        ]);

        $response->assertRedirect('/behavior');
        $this->assertDatabaseHas('behavior_logs', [
            'child_id' => $child->id,
            'training_session_id' => $session->id,
            'behavior_type' => 'avoidance',
        ]);
    }

    public function test_can_create_behavior_log_linked_to_training_session_item(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create(['title' => 'Bật nhảy trên thảm']);
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
        ]);

        $response = $this->post('/behavior', [
            'child_id' => $child->id,
            'training_session_id' => $session->id,
            'training_session_item_id' => $item->id,
            'behavior_type' => 'sensory_seeking',
            'severity' => 'low',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect('/behavior');
        $this->assertDatabaseHas('behavior_logs', [
            'child_id' => $child->id,
            'training_session_id' => $session->id,
            'training_session_item_id' => $item->id,
            'behavior_type' => 'sensory_seeking',
        ]);
    }

    public function test_mismatched_child_and_training_session_is_rejected(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $otherChild = Child::factory()->create(['status' => 'active']);
        $session = TrainingSession::factory()->create(['child_id' => $otherChild->id]);

        $response = $this->from('/behavior/create')->post('/behavior', [
            'child_id' => $child->id,
            'training_session_id' => $session->id,
            'behavior_type' => 'tantrum',
            'severity' => 'high',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect('/behavior/create');
        $response->assertSessionHasErrors(['training_session_id']);
        $this->assertDatabaseCount('behavior_logs', 0);
    }

    public function test_mismatched_training_session_item_is_rejected(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);
        $otherSession = TrainingSession::factory()->create(['child_id' => $child->id]);
        $item = TrainingSessionItem::factory()->create(['training_session_id' => $otherSession->id]);

        $response = $this->from('/behavior/create')->post('/behavior', [
            'child_id' => $child->id,
            'training_session_id' => $session->id,
            'training_session_item_id' => $item->id,
            'behavior_type' => 'tantrum',
            'severity' => 'high',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect('/behavior/create');
        $response->assertSessionHasErrors(['training_session_item_id']);
        $this->assertDatabaseCount('behavior_logs', 0);
    }

    public function test_invalid_behavior_log_fails_validation(): void
    {
        $response = $this->from('/behavior/create')->post('/behavior', [
            'child_id' => '',
            'behavior_type' => '',
            'recorded_at' => '',
        ]);

        $response->assertRedirect('/behavior/create');
        $response->assertSessionHasErrors(['child_id', 'behavior_type', 'recorded_at']);
        $this->assertDatabaseCount('behavior_logs', 0);
    }

    public function test_created_behavior_appears_on_behavior_page(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $log = BehaviorLog::factory()->create([
            'child_id' => $child->id,
            'behavior_type' => 'picky_eating',
            'severity' => 'low',
            'recorded_at' => now(),
        ]);

        $response = $this->get('/behavior');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.id', $log->id)
            ->where('behaviorLogs.0.behavior_type', 'picky_eating')
            ->has('behaviorGroups', 1)
            ->where('behaviorGroups.0.logs.0.id', $log->id)
        );
    }

    public function test_linked_behavior_shows_training_context_in_timeline_props(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create(['title' => 'Nhìn mắt khi gọi tên']);
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => '2026-05-26',
            'scheduled_time' => '08:00',
        ]);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
        ]);
        $log = BehaviorLog::factory()->create([
            'child_id' => $child->id,
            'training_session_id' => $session->id,
            'training_session_item_id' => $item->id,
            'behavior_type' => 'tantrum',
        ]);

        $response = $this->get('/behavior');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->where('behaviorGroups.0.logs.0.id', $log->id)
            ->where('behaviorGroups.0.logs.0.training_session.id', $session->id)
            ->where('behaviorGroups.0.logs.0.training_session_item.exercise.title', 'Nhìn mắt khi gọi tên')
        );
    }

    public function test_behavior_type_displays_vietnamese_label(): void
    {
        $response = $this->get('/behavior/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Create')
            ->where('behaviorTypes.tantrum', 'Ăn vạ')
            ->where('behaviorTypes.avoidance', 'Né tránh')
            ->where('behaviorTypes.difficulty_transitioning', 'Khó chuyển hoạt động')
            ->where('severities.low', 'Nhẹ')
            ->where('severities.medium', 'Trung bình')
            ->where('severities.high', 'Cao')
        );
    }

    /**
     * Test showing details of a behavior log.
     */
    public function test_can_show_behavior_log(): void
    {
        $child = Child::factory()->create();
        $log = BehaviorLog::factory()->create(['child_id' => $child->id]);

        $response = $this->get("/behavior/{$log->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Show')
            ->where('behaviorLog.id', $log->id)
            ->has('behaviorTypes')
            ->has('severities')
        );
    }

    /**
     * Test edit behavior log page.
     */
    public function test_can_view_edit_behavior_log_page(): void
    {
        $child = Child::factory()->create();
        $log = BehaviorLog::factory()->create(['child_id' => $child->id]);

        $response = $this->get("/behavior/{$log->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Edit')
            ->where('behaviorLog.id', $log->id)
            ->has('children')
            ->has('behaviorTypes')
            ->has('severities')
        );
    }

    /**
     * Test updating behavior log successfully.
     */
    public function test_can_update_behavior_log(): void
    {
        $child = Child::factory()->create();
        $log = BehaviorLog::factory()->create(['child_id' => $child->id, 'behavior_type' => 'tantrum']);

        $payload = [
            'child_id' => $child->id,
            'behavior_type' => 'sensory_seeking',
            'severity' => 'high',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
            'trigger' => 'Loud siren outside',
            'response' => 'Placed protective helmet and moved child to safe zone',
            'note' => 'Keep close observation'
        ];

        $response = $this->put("/behavior/{$log->id}", $payload);

        $response->assertRedirect("/behavior/{$log->id}");
        $this->assertDatabaseHas('behavior_logs', [
            'id' => $log->id,
            'behavior_type' => 'sensory_seeking',
            'severity' => 'high',
            'trigger' => 'Loud siren outside',
            'note' => 'Keep close observation',
        ]);
    }

    /**
     * Test deleting behavior log successfully.
     */
    public function test_can_delete_behavior_log(): void
    {
        $child = Child::factory()->create();
        $log = BehaviorLog::factory()->create(['child_id' => $child->id]);

        $response = $this->delete("/behavior/{$log->id}");

        $response->assertRedirect('/behavior');
        $this->assertSoftDeleted('behavior_logs', ['id' => $log->id]);
    }

    /**
     * Test quick behavior log page.
     */
    public function test_can_view_quick_behavior_log_page(): void
    {
        $child = Child::factory()->create();
        BehaviorLog::factory()->create(['child_id' => $child->id, 'recorded_at' => now()]);

        $response = $this->get('/behavior/quick');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Quick')
            ->has('children')
            ->has('defaultChildId')
            ->has('selectedChildId')
            ->has('presets')
            ->has('severities')
            ->has('recentBehaviors')
            ->has('dailySummary')
        );
    }

    public function test_behavior_quick_child_dropdown_only_includes_active_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        Child::factory()->create(['status' => 'paused']);
        Child::factory()->create(['status' => 'voided']);

        $response = $this->get('/behavior/quick');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Quick')
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
            ->where('defaultChildId', $activeChild->id)
        );
    }

    public function test_cannot_quick_store_behavior_for_paused_or_voided_child(): void
    {
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        foreach ([$pausedChild, $voidedChild] as $child) {
            $response = $this->from('/behavior/quick')->post('/behavior/quick-store', [
                'child_id' => $child->id,
                'behavior_type' => 'tantrum',
                'severity' => 'low',
                'note' => 'Ghi nhận nhanh',
            ]);

            $response->assertSessionHasErrors(['child_id']);
        }

        $this->assertDatabaseCount('behavior_logs', 0);
    }

    public function test_cannot_create_behavior_log_for_paused_or_voided_child(): void
    {
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);
        $deletedChild = Child::factory()->create(['status' => 'active']);
        $deletedChild->delete();

        foreach ([$pausedChild, $voidedChild, $deletedChild] as $child) {
            $response = $this->from('/behavior/create')->post('/behavior', [
                'child_id' => $child->id,
                'behavior_type' => 'tantrum',
                'severity' => 'low',
                'recorded_at' => now()->format('Y-m-d H:i:s'),
            ]);

            $response->assertSessionHasErrors(['child_id']);
        }

        $this->assertDatabaseCount('behavior_logs', 0);
    }

    /**
     * Test quick storing a behavior log.
     */
    public function test_can_quick_store_behavior_log(): void
    {
        $child = Child::factory()->create();
        $payload = [
            'child_id' => $child->id,
            'behavior_type' => 'transition_difficulty',
            'severity' => 'low',
            'note' => 'Quick incident description',
        ];

        $response = $this->from('/behavior/quick')->post('/behavior/quick-store', $payload);

        $response->assertRedirect('/behavior/quick');
        $this->assertDatabaseHas('behavior_logs', [
            'child_id' => $child->id,
            'behavior_type' => 'difficulty_transitioning', // Handled conversion
            'severity' => 'low',
            'note' => 'Quick incident description',
        ]);
    }
}
