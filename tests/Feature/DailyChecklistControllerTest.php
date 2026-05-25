<?php

namespace Tests\Feature;

use App\Models\ChecklistItem;
use App\Models\Child;
use App\Models\DailyMood;
use App\Models\Exercise;
use App\Models\ProgressLog;
use App\Models\StreakTracking;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DailyChecklistControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
    }

    public function test_today_checklist_generates_from_training_items(): void
    {
        [$child, $session, $item] = $this->createTodayTrainingItem();

        $response = $this->get('/today');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Checklist/Today')
            ->where('summary.total_items', 1)
            ->where('timeline.0.id', ChecklistItem::where('training_session_item_id', $item->id)->first()->id)
            ->where('timeline.0.child.id', $child->id)
            ->where('timeline.0.exercise.id', $item->exercise_id)
        );
    }

    public function test_completion_tracking_updates_training_item_and_streak(): void
    {
        [$child, , $trainingItem] = $this->createTodayTrainingItem();
        $this->get('/today');
        $checklistItem = ChecklistItem::where('training_session_item_id', $trainingItem->id)->first();

        $response = $this->patch("/checklist/items/{$checklistItem->id}", [
            'status' => 'completed',
            'performance_result' => 'good',
            'parent_note' => 'Bé hợp tác hơn hôm nay',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checklist_items', [
            'id' => $checklistItem->id,
            'status' => 'completed',
            'performance_result' => 'good',
            'parent_note' => 'Bé hợp tác hơn hôm nay',
        ]);
        $this->assertDatabaseHas('training_session_items', [
            'id' => $trainingItem->id,
            'completion_status' => 'completed',
        ]);
        $this->assertDatabaseHas('streak_trackings', [
            'child_id' => $child->id,
            'current_streak' => 1,
        ]);
    }

    public function test_carry_over_creates_tomorrow_task_once(): void
    {
        [$child, , $trainingItem] = $this->createTodayTrainingItem();
        $this->get('/today');
        $checklistItem = ChecklistItem::where('training_session_item_id', $trainingItem->id)->first();

        $this->post("/checklist/items/{$checklistItem->id}/carry-over")->assertRedirect();
        $this->post("/checklist/items/{$checklistItem->id}/carry-over")->assertRedirect();

        $this->assertEquals(1, TrainingSession::where('child_id', $child->id)
            ->whereDate('session_date', today()->addDay())
            ->count());
    }

    public function test_mood_tracking(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $this->post("/checklist/children/{$child->id}/mood", ['mood' => 'good'])->assertRedirect();

        $this->assertDatabaseHas('daily_moods', [
            'child_id' => $child->id,
            'mood' => 'good',
        ]);
    }

    public function test_progress_log_is_added(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $this->post("/checklist/children/{$child->id}/progress-log", [
            'title' => 'Bé tự cất dép',
        ])->assertRedirect();

        $this->assertDatabaseHas('progress_logs', [
            'child_id' => $child->id,
            'title' => 'Bé tự cất dép',
        ]);
    }

    public function test_reminder_visibility_for_upcoming_task(): void
    {
        $time = now()->addMinutes(20)->format('H:i');
        $this->createTodayTrainingItem(['scheduled_time' => $time]);

        $response = $this->get('/today');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('reminders', 1)
        );
    }

    public function test_daily_timeline_contains_status_and_duration(): void
    {
        [, , $trainingItem] = $this->createTodayTrainingItem([
            'scheduled_time' => '15:30',
            'duration_minutes' => 20,
        ]);

        $response = $this->get('/today');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->where('timeline.0.time', '15:30')
            ->where('timeline.0.duration_minutes', 20)
            ->where('timeline.0.status', 'pending')
        );
    }

    protected function createTodayTrainingItem(array $overrides = []): array
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create();
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => $overrides['scheduled_time'] ?? '15:30',
            'status' => 'planned',
        ]);
        $item = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'duration_minutes' => $overrides['duration_minutes'] ?? 15,
            'completion_status' => 'not_started',
        ]);

        return [$child, $session, $item];
    }
}
