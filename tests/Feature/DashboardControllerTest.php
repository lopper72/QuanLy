<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Exercise;
use App\Models\TrainingSession;
use App\Models\BehaviorLog;
use App\Models\Assessment;
use App\Models\TrainingSessionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class DashboardControllerTest extends TestCase
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
     * Test that the dashboard page renders with all required stats and datasets.
     */
    public function test_dashboard_renders_with_complete_data(): void
    {
        // 1. Create some children
        $children = Child::factory()->count(3)->create();
        
        // 2. Create some exercises
        Exercise::factory()->count(5)->create(['is_active' => true]);

        // 3. Create some training sessions
        // Today session
        TrainingSession::factory()->create([
            'child_id' => $children[0]->id,
            'session_date' => now()->toDateString(),
            'status' => 'completed',
            'total_minutes' => 45,
        ]);
        
        // Today session (Pending)
        TrainingSession::factory()->create([
            'child_id' => $children[1]->id,
            'session_date' => now()->toDateString(),
            'status' => 'planned',
            'total_minutes' => 30,
        ]);

        // Older session
        TrainingSession::factory()->create([
            'child_id' => $children[2]->id,
            'session_date' => now()->subDays(2)->toDateString(),
            'status' => 'completed',
            'total_minutes' => 60,
        ]);

        // 4. Create some behavior logs
        BehaviorLog::factory()->create([
            'child_id' => $children[0]->id,
            'behavior_type' => 'Tantrum',
            'severity' => 'Severe',
            'recorded_at' => now(),
        ]);

        // 5. Create some assessments
        Assessment::factory()->create([
            'child_id' => $children[1]->id,
            'assessment_date' => now()->toDateString(),
            'overall_score' => 85,
            'notes' => 'Great physical responses.',
        ]);

        // Visit the dashboard
        $response = $this->get('/dashboard');

        // Assert 200 OK and that it renders Index component under Dashboard
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            // Assert overview_stats structure
            ->has('overview_stats.total_children')
            ->has('overview_stats.active_exercises')
            ->has('overview_stats.today_sessions_count')
            ->has('overview_stats.today_completed_count')
            
            // Assert numbers are calculated correctly
            ->where('overview_stats.total_children', 3)
            ->where('overview_stats.active_exercises', 5)
            ->where('overview_stats.today_sessions_count', 2)
            ->where('overview_stats.today_completed_count', 1)

            // Assert operational summaries
            ->has('today_training_summary')
            ->has('weekly_training_completion.completion_rate')
            ->has('weekly_training_completion.completed_sessions')
            ->has('weekly_training_completion.total_sessions')
            ->has('weekly_training_completion.daily_breakdown')

            // Assert list lengths / existence
            ->has('recent_training_sessions')
            ->has('recent_behavior_logs')
            ->has('latest_assessments')
            ->has('children_progress_summary')
        );
    }

    public function test_dashboard_counts_and_progress_summary_only_active_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        TrainingSession::factory()->create([
            'child_id' => $activeChild->id,
            'session_date' => now()->toDateString(),
            'status' => 'planned',
        ]);
        TrainingSession::factory()->create([
            'child_id' => $pausedChild->id,
            'session_date' => now()->toDateString(),
            'status' => 'planned',
        ]);
        TrainingSession::factory()->create([
            'child_id' => $voidedChild->id,
            'session_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('overview_stats.total_children', 1)
            ->where('overview_stats.active_children_count', 1)
            ->where('overview_stats.paused_children_count', 1)
            ->where('overview_stats.voided_children_count', 1)
            ->where('overview_stats.today_sessions_count', 1)
            ->has('today_training_summary', 1)
            ->where('today_training_summary.0.child_id', $activeChild->id)
            ->has('children_progress_summary', 1)
            ->where('children_progress_summary.0.id', $activeChild->id)
        );
    }

    public function test_dashboard_shows_resumed_child_again(): void
    {
        $child = Child::factory()->create([
            'status' => 'paused',
            'paused_at' => now(),
        ]);

        $this->post(route('children.resume', $child))->assertRedirect();

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('overview_stats.total_children', 1)
            ->where('overview_stats.active_children_count', 1)
            ->has('children_progress_summary', 1)
            ->where('children_progress_summary.0.id', $child->id)
        );
    }

    public function test_dashboard_displays_exercise_thumbnail_data(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create([
            'is_active' => true,
            'thumbnail_path' => 'exercises/placeholders/gross_motor.jpg',
        ]);
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => now()->toDateString(),
            'status' => 'planned',
        ]);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->has('today_training_summary.0.sessions.0.exercise_thumbnails', 1)
            ->where('today_training_summary.0.sessions.0.exercise_thumbnails.0.thumbnail_path', 'exercises/placeholders/gross_motor.jpg')
            ->where('today_training_summary.0.sessions.0.items.0.exercise.thumbnail_path', 'exercises/placeholders/gross_motor.jpg')
            ->has('recent_training_sessions.0.exercise_thumbnails', 1)
            ->where('recent_training_sessions.0.exercise_thumbnails.0.thumbnail_path', 'exercises/placeholders/gross_motor.jpg')
        );
    }

    public function test_dashboard_today_training_is_today_only_and_sorted_by_time(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create(['is_active' => true]);
        $later = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => '15:30',
            'status' => 'pending',
        ]);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $later->id,
            'exercise_id' => $exercise->id,
            'completion_status' => 'pending',
        ]);
        $earlier = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => '08:00',
            'status' => 'pending',
        ]);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $earlier->id,
            'exercise_id' => $exercise->id,
            'completion_status' => 'pending',
        ]);
        TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->subDay()->toDateString(),
            'scheduled_time' => '07:00',
            'status' => 'completed',
        ]);

        $response = $this->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('today_training_summary', 1)
            ->where('today_training_summary.0.sessions.0.id', $earlier->id)
            ->where('today_training_summary.0.sessions.0.scheduled_time', '08:00')
            ->where('today_training_summary.0.sessions.1.id', $later->id)
            ->where('today_training_summary.0.sessions.1.scheduled_time', '15:30')
        );
    }
}
