<?php

namespace Tests\Feature;

use App\Models\BehaviorLog;
use App\Models\Child;
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

    public function test_behavior_default_excludes_paused_and_voided_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        BehaviorLog::factory()->create(['child_id' => $activeChild->id]);
        BehaviorLog::factory()->create(['child_id' => $pausedChild->id]);
        BehaviorLog::factory()->create(['child_id' => $voidedChild->id]);

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

    public function test_behavior_voided_filter_shows_voided_historical_logs(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        BehaviorLog::factory()->create(['child_id' => $activeChild->id]);
        BehaviorLog::factory()->create(['child_id' => $voidedChild->id]);

        $response = $this->get('/behavior?child_status=voided');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Behavior/Index')
            ->where('filters.child_status', 'voided')
            ->has('behaviorLogs', 1)
            ->where('behaviorLogs.0.child.id', $voidedChild->id)
            ->has('behaviorGroups', 1)
            ->where('behaviorGroups.0.child.status', 'voided')
            ->has('children', 1)
            ->where('children.0.id', $voidedChild->id)
        );
    }

    public function test_behavior_all_filter_shows_all_historical_logs(): void
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
            ->where('filters.child_status', 'all')
            ->has('behaviorLogs', 3)
            ->has('behaviorGroups', 3)
            ->has('children', 3)
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

        $newLog = BehaviorLog::first();
        $response->assertRedirect("/behavior/{$newLog->id}");
        $this->assertDatabaseHas('behavior_logs', [
            'child_id' => $child->id,
            'behavior_type' => 'tantrum',
            'severity' => 'medium',
            'trigger' => 'Asked to transition to math class',
            'note' => 'Child was tired',
        ]);
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

        foreach ([$pausedChild, $voidedChild] as $child) {
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
