<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\TrainingSession;
use App\Models\BehaviorLog;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        
        // Create a child for testing
        $this->child = Child::create([
            'full_name' => 'John Doe',
            'date_of_birth' => '2018-01-01',
            'gender' => 'male',
            'diagnosis_level' => 'Level 1',
        ]);
    }

    public function test_can_open_weekly_report_page()
    {
        $response = $this->get(route('reports.weekly.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Weekly')
            ->has('children')
        );
    }

    public function test_can_generate_report_preview()
    {
        $response = $this->post(route('reports.weekly.generate'), [
            'child_id' => $this->child->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ]);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Weekly')
            ->has('reportData')
            ->where('reportData.child.id', $this->child->id)
        );
    }

    public function test_can_download_pdf_report()
    {
        $response = $this->get(route('reports.weekly.download', [
            'child' => $this->child->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_handles_no_data_gracefully()
    {
        // No sessions or logs created
        $response = $this->post(route('reports.weekly.generate'), [
            'child_id' => $this->child->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ]);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('reportData.training.total_sessions', 0)
            ->where('reportData.behavior.total_incidents', 0)
        );
    }
}