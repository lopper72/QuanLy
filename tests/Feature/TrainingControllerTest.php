<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Exercise;
use App\Models\ExerciseCombo;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class TrainingControllerTest extends TestCase
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
     * Test daily training session listing grouped by child (timeline).
     */
    public function test_can_list_training_sessions_grouped_by_child(): void
    {
        $child1 = Child::factory()->create(['full_name' => 'Nguyễn Văn A']);
        $child2 = Child::factory()->create(['full_name' => 'Trần Thị B']);
        
        // Child 1 gets newest session so appears first
        TrainingSession::factory()->create([
            'child_id' => $child2->id,
            'status' => 'completed',
            'created_at' => now()->subDays(1),
        ]);
        TrainingSession::factory()->count(2)->create([
            'child_id' => $child1->id,
            'status' => 'planned',
            'created_at' => now(),
        ]);

        $response = $this->get('/training');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('groupedSessions', 2)
            ->has('allChildren')
            ->has('filters')
            ->where('groupedSessions.0.child.id', $child1->id)
            ->where('groupedSessions.0.total_count', 2)
            ->where('groupedSessions.1.child.id', $child2->id)
            ->where('groupedSessions.1.total_count', 1)
        );
    }

    /**
     * Test filtering training sessions by child in grouped view.
     */
    public function test_can_filter_sessions_by_child(): void
    {
        $child1 = Child::factory()->create(['full_name' => 'Nguyễn Văn A']);
        $child2 = Child::factory()->create(['full_name' => 'Trần Thị B']);

        TrainingSession::factory()->create(['child_id' => $child1->id]);
        TrainingSession::factory()->create(['child_id' => $child2->id]);

        $response = $this->get("/training?child_id={$child1->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('groupedSessions', 1)
            ->where('groupedSessions.0.child.id', $child1->id)
        );
    }

    /**
     * Test training list includes children of all statuses for history.
     */
    public function test_training_index_includes_all_children(): void
    {
        Child::factory()->create(['full_name' => 'Active Child', 'status' => 'active']);
        Child::factory()->create(['full_name' => 'Paused Child', 'status' => 'paused']);
        Child::factory()->create(['full_name' => 'Voided Child', 'status' => 'voided']);

        $response = $this->get('/training?child_status=all');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('allChildren', 3)
        );
    }

    /**
     * Test timeline orders sessions by created_at DESC (newest scheduled first).
     */
    public function test_timeline_orders_by_newest_scheduled_first(): void
    {
        $child = Child::factory()->create(['full_name' => 'Test Child']);
        $session1 = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => now()->subDays(5),
            'created_at' => now()->subDays(2),
        ]);
        $session2 = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => now()->subDays(2),
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get('/training');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('groupedSessions', 1)
            ->where('groupedSessions.0.total_count', 2)
            // session2 was created later (now()->subDay()) so should be first
            ->where('groupedSessions.0.sessions.0.id', $session2->id)
            ->where('groupedSessions.0.sessions.1.id', $session1->id)
        );
    }

    /**
     * Test child groups ordered by latest created_at DESC.
     */
    public function test_child_groups_ordered_by_newest_scheduled_first(): void
    {
        $childA = Child::factory()->create(['full_name' => 'Child A']);
        $childB = Child::factory()->create(['full_name' => 'Child B']);

        // Child B has newer training
        TrainingSession::factory()->create([
            'child_id' => $childA->id,
            'session_date' => now(),
            'created_at' => now()->subDays(3),
        ]);
        TrainingSession::factory()->create([
            'child_id' => $childB->id,
            'session_date' => now(),
            'created_at' => now()->subHours(5),
        ]);

        $response = $this->get('/training');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('groupedSessions', 2)
            // Child B has newest training (created 5 hours ago) → first
            ->where('groupedSessions.0.child.id', $childB->id)
            ->where('groupedSessions.1.child.id', $childA->id)
        );
    }

    /**
     * Test inactive child historical training remains visible in timeline.
     */
    public function test_inactive_child_historical_training_remains_visible(): void
    {
        $voided = Child::factory()->create(['full_name' => 'Voided Child', 'status' => 'voided']);
        TrainingSession::factory()->create([
            'child_id' => $voided->id,
            'session_date' => now()->subDays(5),
        ]);

        $response = $this->get('/training?child_id=' . $voided->id);

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('groupedSessions', 1)
            ->where('groupedSessions.0.child.id', $voided->id)
            ->where('groupedSessions.0.total_count', 1)
        );
    }

    /**
     * Test deleted training session disappears from list.
     */
    public function test_deleted_training_session_disappears_from_list(): void
    {
        $child = Child::factory()->create();
        TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => now()->subDays(3),
        ]);
        $toDelete = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => now()->subDays(1),
        ]);

        $this->delete("/training/{$toDelete->id}");

        $response = $this->get('/training');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('groupedSessions', 1)
            ->where('groupedSessions.0.total_count', 1)
        );
    }

    /**
     * Test rendering create daily training page.
     */
    public function test_can_render_create_page(): void
    {
        Child::factory()->count(2)->create();
        Exercise::factory()->count(3)->create(['is_active' => true]);

        $response = $this->get('/training/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Create')
            ->has('children', 2)
            ->has('exercises', 3)
        );
    }

    public function test_training_create_child_dropdown_only_includes_active_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        Child::factory()->create(['status' => 'paused']);
        Child::factory()->create(['status' => 'voided']);
        Exercise::factory()->count(2)->create(['is_active' => true]);

        $response = $this->get('/training/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Create')
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
        );
    }

    public function test_training_create_child_dropdown_includes_resumed_child(): void
    {
        $child = Child::factory()->create([
            'status' => 'paused',
            'paused_at' => now(),
        ]);
        Exercise::factory()->count(2)->create(['is_active' => true]);

        $this->post(route('children.resume', $child))->assertRedirect();

        $response = $this->get('/training/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Create')
            ->has('children', 1)
            ->where('children.0.id', $child->id)
        );
    }

    public function test_training_timeline_displays_exercise_thumbnail_data(): void
    {
        $child = Child::factory()->create();
        $exercise = Exercise::factory()->create([
            'is_active' => true,
            'thumbnail_path' => 'exercises/placeholders/gross_motor.jpg',
        ]);
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => now()->toDateString(),
        ]);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
        ]);

        $response = $this->get('/training');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->has('groupedSessions.0.sessions.0.items', 1)
            ->where('groupedSessions.0.sessions.0.items.0.exercise.thumbnail_path', 'exercises/placeholders/gross_motor.jpg')
        );
    }

    public function test_cannot_create_training_session_for_paused_child(): void
    {
        $child = Child::factory()->create(['status' => 'paused']);

        $response = $this->post('/training', [
            'child_id' => $child->id,
            'session_date' => '2026-05-25',
            'status' => 'planned',
            'items' => [],
        ]);

        $response->assertSessionHasErrors(['child_id']);
        $this->assertDatabaseMissing('training_sessions', ['child_id' => $child->id]);
    }

    public function test_cannot_create_training_session_for_voided_child(): void
    {
        $child = Child::factory()->create(['status' => 'voided']);

        $response = $this->post('/training', [
            'child_id' => $child->id,
            'session_date' => '2026-05-25',
            'status' => 'planned',
            'items' => [],
        ]);

        $response->assertSessionHasErrors(['child_id']);
        $this->assertDatabaseMissing('training_sessions', ['child_id' => $child->id]);
    }

    /**
     * Test storing a new training session with items.
     */
    public function test_can_store_training_session_with_items(): void
    {
        $child = Child::factory()->create();
        $exercise1 = Exercise::factory()->create(['is_active' => true]);
        $exercise2 = Exercise::factory()->create(['is_active' => true]);

        $data = [
            'child_id' => $child->id,
            'session_date' => '2026-05-25',
            'scheduled_time' => '14:30',
            'status' => 'planned',
            'notes' => 'Perform standard afternoon training program.',
            'items' => [
                [
                    'exercise_id' => $exercise1->id,
                    'duration_minutes' => 15,
                    'completion_status' => 'not_started',
                    'therapist_note' => 'Focus on eye contact.',
                    'sort_order' => 1,
                ],
                [
                    'exercise_id' => $exercise2->id,
                    'duration_minutes' => 10,
                    'completion_status' => 'not_started',
                    'therapist_note' => 'Let them play freely first.',
                    'sort_order' => 2,
                ],
            ],
        ];

        $response = $this->post('/training', $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_sessions', [
            'child_id' => $child->id,
            'session_date' => '2026-05-25 00:00:00',
            'scheduled_time' => '14:30',
            'status' => 'planned',
            'total_minutes' => 25,
        ]);

        $session = TrainingSession::where('child_id', $child->id)->first();

        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'exercise_id' => $exercise1->id,
            'duration_minutes' => 15,
            'completion_status' => 'not_started',
        ]);

        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'exercise_id' => $exercise2->id,
            'duration_minutes' => 10,
            'completion_status' => 'not_started',
        ]);

        $response->assertRedirect(route('training.show', $session->id));
        $response->assertSessionHas('success', 'Đã tạo buổi tập.');
    }

    /**
     * Test training session validation.
     */
    public function test_create_page_includes_exercise_combos(): void
    {
        $exercise = Exercise::factory()->create(['is_active' => true]);
        $combo = ExerciseCombo::create([
            'title' => 'Combo tăng tập trung',
            'slug' => 'combo-tang-tap-trung',
            'description' => 'Chuỗi bài tập giúp bé duy trì chú ý.',
            'target_skill' => 'attention',
            'estimated_minutes' => 20,
            'difficulty' => 'easy',
        ]);
        $combo->exercises()->attach($exercise->id, ['sort_order' => 1]);

        $response = $this->get('/training/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Create')
            ->has('exerciseCombos', 1)
            ->where('exerciseCombos.0.title', 'Combo tăng tập trung')
            ->where('exerciseCombos.0.exercises.0.id', $exercise->id)
        );
    }

    public function test_can_create_training_session_with_one_combo(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise1 = Exercise::factory()->create(['is_active' => true, 'estimated_minutes' => 10]);
        $exercise2 = Exercise::factory()->create(['is_active' => true, 'estimated_minutes' => 15]);
        $combo = ExerciseCombo::create([
            'title' => 'Combo vận động buổi sáng',
            'slug' => 'combo-van-dong-buoi-sang',
            'description' => 'Các bài ngắn cho buổi sáng.',
            'target_skill' => 'gross_motor',
            'estimated_minutes' => 25,
            'difficulty' => 'easy',
        ]);
        $combo->exercises()->attach([
            $exercise1->id => ['sort_order' => 1],
            $exercise2->id => ['sort_order' => 2],
        ]);

        $response = $this->post('/training', [
            'child_id' => $child->id,
            'session_date' => '2026-05-25',
            'status' => 'planned',
            'combo_ids' => [$combo->id],
        ]);

        $session = TrainingSession::latest('id')->first();

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('training.show', $session->id));
        $this->assertSame(2, $session->items()->count());
        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'total_minutes' => 25,
        ]);
        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'exercise_id' => $exercise1->id,
            'sort_order' => 1,
            'duration_minutes' => 10,
            'completion_status' => 'not_started',
        ]);
        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'exercise_id' => $exercise2->id,
            'sort_order' => 2,
            'duration_minutes' => 15,
            'completion_status' => 'not_started',
        ]);
    }

    public function test_combo_duplicate_exercises_are_not_added_twice(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $sharedExercise = Exercise::factory()->create(['is_active' => true, 'estimated_minutes' => 10]);
        $otherExercise = Exercise::factory()->create(['is_active' => true, 'estimated_minutes' => 8]);
        $combo1 = ExerciseCombo::create([
            'title' => 'Combo chú ý',
            'slug' => 'combo-chu-y',
            'target_skill' => 'attention',
            'estimated_minutes' => 10,
            'difficulty' => 'easy',
        ]);
        $combo2 = ExerciseCombo::create([
            'title' => 'Combo giao tiếp',
            'slug' => 'combo-giao-tiep',
            'target_skill' => 'communication',
            'estimated_minutes' => 18,
            'difficulty' => 'easy',
        ]);
        $combo1->exercises()->attach($sharedExercise->id, ['sort_order' => 1]);
        $combo2->exercises()->attach([
            $sharedExercise->id => ['sort_order' => 1],
            $otherExercise->id => ['sort_order' => 2],
        ]);

        $this->post('/training', [
            'child_id' => $child->id,
            'session_date' => '2026-05-25',
            'status' => 'planned',
            'combo_ids' => [$combo1->id, $combo2->id],
        ])->assertSessionHasNoErrors();

        $session = TrainingSession::latest('id')->first();

        $this->assertSame(2, $session->items()->count());
        $this->assertSame(1, $session->items()->where('exercise_id', $sharedExercise->id)->count());
    }

    public function test_can_create_training_session_with_combo_and_manual_exercise(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $comboExercise = Exercise::factory()->create(['is_active' => true, 'estimated_minutes' => 12]);
        $manualExercise = Exercise::factory()->create(['is_active' => true, 'estimated_minutes' => 9]);
        $combo = ExerciseCombo::create([
            'title' => 'Combo kỹ năng tự lập',
            'slug' => 'combo-ky-nang-tu-lap',
            'target_skill' => 'self_care',
            'estimated_minutes' => 12,
            'difficulty' => 'medium',
        ]);
        $combo->exercises()->attach($comboExercise->id, ['sort_order' => 1]);

        $this->post('/training', [
            'child_id' => $child->id,
            'session_date' => '2026-05-25',
            'status' => 'planned',
            'combo_ids' => [$combo->id],
            'items' => [
                ['exercise_id' => $manualExercise->id],
            ],
        ])->assertSessionHasNoErrors();

        $session = TrainingSession::latest('id')->first();

        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'exercise_id' => $comboExercise->id,
            'sort_order' => 1,
            'duration_minutes' => 12,
        ]);
        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'exercise_id' => $manualExercise->id,
            'sort_order' => 2,
            'duration_minutes' => 9,
        ]);
    }

    public function test_invalid_combo_id_validation_fails(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $response = $this->post('/training', [
            'child_id' => $child->id,
            'session_date' => '2026-05-25',
            'status' => 'planned',
            'combo_ids' => [999999],
        ]);

        $response->assertSessionHasErrors(['combo_ids.0']);
    }

    public function test_store_session_validation(): void
    {
        $response = $this->post('/training', [
            'child_id' => '',
            'session_date' => '',
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors(['child_id', 'session_date', 'status']);
    }

    /**
     * Test showing detailed training session page.
     */
    public function test_can_show_training_session(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'status' => 'in_progress',
        ]);

        $response = $this->get("/training/{$session->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Show')
            ->where('session.id', $session->id)
            ->where('session.status', 'in_progress')
        );
    }

    public function test_training_show_has_behavior_logging_entry_point(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);
        $exercise = Exercise::factory()->create();
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
        ]);

        $response = $this->get("/training/{$session->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Show')
            ->where('session.id', $session->id)
            ->where('session.items.0.id', $item->id)
        );

        $this->assertStringContainsString(
            'Ghi nhận hành vi trong buổi tập',
            file_get_contents(resource_path('js/Pages/Training/Show.vue'))
        );
    }

    /**
     * Test rendering edit daily training session page.
     */
    public function test_can_render_edit_page(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);

        $response = $this->get("/training/{$session->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Edit')
            ->where('session.id', $session->id)
            ->has('children')
            ->has('exercises')
        );
    }

    /**
     * Test updating a training session and its items.
     */
    public function test_can_update_training_session_and_items(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => '2026-05-20',
            'status' => 'planned',
            'notes' => 'Old general session observations.',
            'total_minutes' => 30,
        ]);

        $exercise1 = Exercise::factory()->create(['is_active' => true]);
        $exercise2 = Exercise::factory()->create(['is_active' => true]);

        // Create an existing item to test updating/deleting
        $existingItem = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise1->id,
            'duration_minutes' => 30,
            'completion_status' => 'not_started',
            'sort_order' => 1,
        ]);

        $data = [
            'child_id' => $child->id,
            'session_date' => '2026-05-21', // Changed date
            'scheduled_time' => '10:00',
            'status' => 'in_progress', // Changed status
            'notes' => 'Updated therapist notes.',
            'items' => [
                // 1. Update existing item
                [
                    'id' => $existingItem->id,
                    'exercise_id' => $exercise1->id,
                    'duration_minutes' => 20, // Reduced duration
                    'completion_status' => 'completed', // Completed
                    'therapist_note' => 'Performed extremely well!',
                    'sort_order' => 1,
                ],
                // 2. Add a new item
                [
                    'exercise_id' => $exercise2->id,
                    'duration_minutes' => 15,
                    'completion_status' => 'not_started',
                    'therapist_note' => 'Add some relaxation steps.',
                    'sort_order' => 2,
                ],
            ],
        ];

        $response = $this->put("/training/{$session->id}", $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'session_date' => '2026-05-21 00:00:00',
            'scheduled_time' => '10:00',
            'status' => 'in_progress',
            'notes' => 'Updated therapist notes.',
            'total_minutes' => 35, // 20 + 15
        ]);

        // Verify updated item
        $this->assertDatabaseHas('training_session_items', [
            'id' => $existingItem->id,
            'duration_minutes' => 20,
            'completion_status' => 'completed',
            'therapist_note' => 'Performed extremely well!',
        ]);

        // Verify newly added item
        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'exercise_id' => $exercise2->id,
            'duration_minutes' => 15,
            'completion_status' => 'not_started',
        ]);

        $response->assertRedirect(route('training.show', $session->id));
        $response->assertSessionHas('success', 'Đã cập nhật buổi tập.');
    }

    /**
     * Test deleting a training session.
     */
    public function test_can_delete_training_session(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
        ]);

        $response = $this->delete("/training/{$session->id}");
        $response->assertSessionHasNoErrors();

        // Assert soft delete
        $this->assertSoftDeleted('training_sessions', [
            'id' => $session->id,
        ]);

        // Assert cascade delete on items is soft/hard deleted (in our migration, it is cascade on foreign key or soft deleted if set up)
        $this->assertDatabaseMissing('training_session_items', [
            'training_session_id' => $session->id,
        ]);

        $response->assertRedirect(route('training.index'));
        $response->assertSessionHas('success', 'Đã xóa buổi tập.');
    }

    /**
     * Test viewing today's training checklist dashboard.
     */
    public function test_can_view_today_training_checklist(): void
    {
        $child1 = Child::factory()->create();
        $child2 = Child::factory()->create();
        $exercise = Exercise::factory()->create(['is_active' => true]);

        $session1 = TrainingSession::factory()->create([
            'child_id' => $child1->id,
            'session_date' => now()->toDateString(),
            'status' => 'planned',
        ]);
        $item1 = TrainingSessionItem::factory()->create([
            'training_session_id' => $session1->id,
            'exercise_id' => $exercise->id,
        ]);

        $session2 = TrainingSession::factory()->create([
            'child_id' => $child2->id,
            'session_date' => now()->addDay()->toDateString(),
            'status' => 'planned',
        ]);

        $response = $this->get('/training/today');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Today')
            ->has('sessions', 1) // Only today's sessions
            ->where('sessions.0.id', $session1->id)
        );
    }

    public function test_today_training_excludes_paused_and_voided_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        $activeSession = TrainingSession::factory()->create([
            'child_id' => $activeChild->id,
            'session_date' => now()->toDateString(),
        ]);
        TrainingSession::factory()->create([
            'child_id' => $pausedChild->id,
            'session_date' => now()->toDateString(),
        ]);
        TrainingSession::factory()->create([
            'child_id' => $voidedChild->id,
            'session_date' => now()->toDateString(),
        ]);

        $response = $this->get('/training/today');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Today')
            ->has('sessions', 1)
            ->where('sessions.0.id', $activeSession->id)
        );
    }

    /**
     * Test quick-completing a training session item.
     */
    public function test_can_quick_complete_training_item(): void
    {
        $child = Child::factory()->create();
        $exercise = Exercise::factory()->create(['is_active' => true]);
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'completion_status' => 'not_started',
        ]);

        $response = $this->patch("/training-items/{$item->id}/quick-complete", []);

        $response->assertStatus(302);
        $this->assertDatabaseHas('training_session_items', [
            'id' => $item->id,
            'completion_status' => 'completed',
        ]);
    }

    /**
     * Test quick-skipping a training session item.
     */
    public function test_can_quick_skip_training_item(): void
    {
        $child = Child::factory()->create();
        $exercise = Exercise::factory()->create(['is_active' => true]);
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'completion_status' => 'not_started',
        ]);

        $response = $this->patch("/training-items/{$item->id}/quick-skip", []);

        $response->assertStatus(302);
        $this->assertDatabaseHas('training_session_items', [
            'id' => $item->id,
            'completion_status' => 'skipped',
        ]);
    }

    /**
     * Test saving quick notes to a training session.
     */
    public function test_can_save_quick_notes_to_session(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);

        $response = $this->patch("/training/{$session->id}/quick-note", [
            'notes' => 'Child performed well today, showed good engagement.',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'notes' => 'Child performed well today, showed good engagement.',
        ]);
    }

    /**
     * Test updating training item status.
     */
    public function test_can_update_training_item_status(): void
    {
        $child = Child::factory()->create();
        $exercise = Exercise::factory()->create(['is_active' => true]);
        $session = TrainingSession::factory()->create(['child_id' => $child->id]);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'completion_status' => 'completed',
        ]);

        $response = $this->patch("/training/items/{$item->id}/status", [
            'status' => 'not_started',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('training_session_items', [
            'id' => $item->id,
            'completion_status' => 'not_started',
        ]);
    }

    public function test_user_can_update_training_session_status_to_completed(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'status' => 'planned',
        ]);

        $response = $this->patch("/training/{$session->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    public function test_training_index_shows_completed_status_after_reload(): void
    {
        $child = Child::factory()->create();
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'status' => 'planned',
        ]);

        $this->patch("/training/{$session->id}/status", [
            'status' => 'completed',
        ])->assertRedirect();

        $response = $this->get('/training');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Training/Index')
            ->where('groupedSessions.0.sessions.0.id', $session->id)
            ->where('groupedSessions.0.sessions.0.status', 'completed')
        );
    }

    public function test_invalid_training_session_status_is_rejected(): void
    {
        $session = TrainingSession::factory()->create(['status' => 'planned']);

        $response = $this->patch("/training/{$session->id}/status", [
            'status' => 'not_started',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'planned',
        ]);
    }

    public function test_invalid_training_item_status_is_rejected(): void
    {
        $item = TrainingSessionItem::factory()->create([
            'completion_status' => 'not_started',
        ]);

        $response = $this->patch("/training/items/{$item->id}/status", [
            'status' => 'in_progress',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('training_session_items', [
            'id' => $item->id,
            'completion_status' => 'not_started',
        ]);
    }

    public function test_session_status_and_item_status_do_not_use_mixed_enum_values(): void
    {
        $session = TrainingSession::factory()->create(['status' => 'planned']);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'completion_status' => 'not_started',
        ]);

        $this->patch("/training/{$session->id}/status", [
            'status' => 'partially_completed',
        ])->assertSessionHasErrors('status');

        $this->patch("/training/items/{$item->id}/status", [
            'status' => 'planned',
        ])->assertSessionHasErrors('status');
    }

    public function test_new_training_session_defaults_to_planned(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create(['is_active' => true]);

        $response = $this->post('/training', [
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => '09:00',
            'items' => [
                ['exercise_id' => $exercise->id, 'duration_minutes' => 10],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $session = TrainingSession::where('child_id', $child->id)->firstOrFail();

        $this->assertSame('planned', $session->status);
        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'completion_status' => 'not_started',
        ]);
    }

    public function test_close_missed_marks_yesterday_pending_session_as_missed(): void
    {
        $session = TrainingSession::factory()->create([
            'session_date' => today()->subDay()->toDateString(),
            'status' => 'pending',
        ]);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'completion_status' => 'pending',
        ]);

        $this->artisan('training:close-missed')->assertExitCode(0);

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'missed',
            'auto_closed_reason' => 'Quá ngày chưa hoàn thành',
        ]);
        $this->assertDatabaseHas('training_session_items', [
            'training_session_id' => $session->id,
            'completion_status' => 'missed',
        ]);
    }

    public function test_close_missed_marks_yesterday_in_progress_session_as_missed(): void
    {
        $session = TrainingSession::factory()->create([
            'session_date' => today()->subDay()->toDateString(),
            'status' => 'in_progress',
        ]);

        $this->artisan('training:close-missed')->assertExitCode(0);

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'missed',
        ]);
    }

    public function test_close_missed_does_not_change_completed_session(): void
    {
        $session = TrainingSession::factory()->create([
            'session_date' => today()->subDay()->toDateString(),
            'status' => 'completed',
        ]);

        $this->artisan('training:close-missed')->assertExitCode(0);

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    public function test_missed_session_item_can_be_updated_later_with_audit_note(): void
    {
        $session = TrainingSession::factory()->create([
            'session_date' => today()->subDay()->toDateString(),
            'status' => 'missed',
        ]);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'completion_status' => 'missed',
            'therapist_note' => null,
        ]);

        $this->patch("/training/items/{$item->id}/status", [
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('training_session_items', [
            'id' => $item->id,
            'completion_status' => 'completed',
            'therapist_note' => 'Cập nhật sau ngày tập',
        ]);
    }

    /**
     * Test deleting an unknown training session group.
     */
    public function test_can_delete_unknown_training_session_group(): void
    {
        // 1. Session with null child_id
        $session1 = TrainingSession::factory()->create([
            'child_id' => null,
        ]);

        // 2. Another session with null child_id
        $session2 = TrainingSession::factory()->create([
            'child_id' => null,
        ]);

        // 3. Normal session (should NOT be deleted)
        $child = Child::factory()->create();
        $session3 = TrainingSession::factory()->create([
            'child_id' => $child->id,
        ]);

        $response = $this->delete('/training/unknown-groups/unknown');

        $response->assertRedirect(route('training.index'));
        $response->assertSessionHas('success', 'Đã xóa 2 buổi tập không xác định.');

        $this->assertSoftDeleted('training_sessions', ['id' => $session1->id]);
        $this->assertSoftDeleted('training_sessions', ['id' => $session2->id]);
        $this->assertDatabaseHas('training_sessions', ['id' => $session3->id, 'deleted_at' => null]);
    }
}
