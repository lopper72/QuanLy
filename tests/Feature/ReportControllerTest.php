<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class ReportControllerTest extends TestCase
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
     * Test listing reports.
     */
    public function test_can_list_reports(): void
    {
        $child = Child::factory()->create();
        Report::factory()->count(3)->create(['child_id' => $child->id]);

        $response = $this->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Index')
            ->has('reports.data', 3)
            ->has('children')
            ->has('reportTypes')
            ->has('filters')
        );
    }

    /**
     * Test render create page.
     */
    public function test_can_render_create_report_page(): void
    {
        Child::factory()->count(2)->create();

        $response = $this->get('/reports/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Create')
            ->has('children', 2)
            ->has('reportTypes')
        );
    }

    public function test_reports_exclude_voided_and_deleted_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);
        $deletedChild = Child::factory()->create(['status' => 'active']);

        Report::factory()->create(['child_id' => $activeChild->id]);
        Report::factory()->create(['child_id' => $voidedChild->id]);
        Report::factory()->create(['child_id' => $deletedChild->id]);
        $deletedChild->delete();

        $response = $this->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Index')
            ->has('reports.data', 1)
            ->where('reports.data.0.child.id', $activeChild->id)
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
        );
    }

    public function test_report_create_child_dropdown_only_includes_active_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        Child::factory()->create(['status' => 'paused']);
        Child::factory()->create(['status' => 'voided']);
        $deletedChild = Child::factory()->create(['status' => 'active']);
        $deletedChild->delete();

        $response = $this->get('/reports/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Create')
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
        );
    }

    public function test_cannot_create_report_for_paused_or_voided_child(): void
    {
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);
        $deletedChild = Child::factory()->create(['status' => 'active']);
        $deletedChild->delete();

        foreach ([$pausedChild, $voidedChild, $deletedChild] as $child) {
            $response = $this->post('/reports', [
                'child_id' => $child->id,
                'report_type' => 'weekly',
                'report_date' => '2026-05-20',
                'summary' => 'Báo cáo thử nghiệm.',
            ]);

            $response->assertSessionHasErrors(['child_id']);
        }

        $this->assertDatabaseCount('reports', 0);
    }

    /**
     * Test storing a report.
     */
    public function test_can_store_report(): void
    {
        $child = Child::factory()->create();

        $payload = [
            'child_id' => $child->id,
            'report_type' => 'weekly',
            'report_date' => '2026-05-20',
            'summary' => 'Weekly summary of progress.',
        ];

        $response = $this->post('/reports', $payload);

        $response->assertRedirect('/reports');
        $this->assertDatabaseHas('reports', [
            'child_id' => $child->id,
            'report_type' => 'weekly',
            'report_date' => '2026-05-20 00:00:00',
            'summary' => 'Weekly summary of progress.',
        ]);
    }

    /**
     * Test showing report details.
     */
    public function test_can_show_report(): void
    {
        $child = Child::factory()->create();
        $report = Report::factory()->create(['child_id' => $child->id]);

        $response = $this->get("/reports/{$report->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Show')
            ->has('report')
        );
    }

    /**
     * Test render edit page.
     */
    public function test_can_render_edit_report_page(): void
    {
        $child = Child::factory()->create();
        $report = Report::factory()->create(['child_id' => $child->id]);

        $response = $this->get("/reports/{$report->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Edit')
            ->has('report')
            ->has('children')
            ->has('reportTypes')
        );
    }

    /**
     * Test updating a report.
     */
    public function test_can_update_report(): void
    {
        $child = Child::factory()->create();
        $report = Report::factory()->create([
            'child_id' => $child->id,
            'report_type' => 'weekly',
            'summary' => 'Original summary'
        ]);

        $payload = [
            'child_id' => $child->id,
            'report_type' => 'monthly',
            'report_date' => '2026-05-21',
            'summary' => 'Updated summary'
        ];

        $response = $this->put("/reports/{$report->id}", $payload);

        $response->assertRedirect("/reports/{$report->id}");
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'report_type' => 'monthly',
            'summary' => 'Updated summary'
        ]);
    }

    /**
     * Test deleting a report.
     */
    public function test_can_delete_report(): void
    {
        $child = Child::factory()->create();
        $report = Report::factory()->create(['child_id' => $child->id]);

        $response = $this->delete("/reports/{$report->id}");

        $response->assertRedirect('/reports');
        $this->assertSoftDeleted($report);
    }
}
