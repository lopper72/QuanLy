<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Child;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class AssessmentControllerTest extends TestCase
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
     * Test listing assessments.
     */
    public function test_can_list_assessments(): void
    {
        $child = Child::factory()->create();
        Assessment::factory()->count(3)->create(['child_id' => $child->id]);

        $response = $this->get('/assessment');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Index')
            ->has('assessments.data', 3)
            ->has('summary')
            ->has('children')
            ->has('filters')
        );
    }

    public function test_assessment_page_loads_with_empty_filters(): void
    {
        $child = Child::factory()->create();
        Assessment::factory()->create(['child_id' => $child->id]);

        $response = $this->get('/assessment?child_id=&start_date=&end_date=&search=');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Index')
            ->has('assessments.data', 1)
            ->where('filters', [])
        );
    }

    public function test_assessment_page_handles_invalid_child_filter_without_crashing(): void
    {
        $child = Child::factory()->create();
        Assessment::factory()->create(['child_id' => $child->id]);

        $response = $this->get('/assessment?child_id=999999');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Index')
            ->has('assessments.data', 0)
            ->where('filters.child_id', '999999')
        );
    }

    public function test_assessment_progress_page_loads_with_empty_filters(): void
    {
        $response = $this->get('/assessment/progress?child_id=&skill_name=');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Progress')
            ->has('progressData')
            ->where('filters', [])
        );
    }

    /**
     * Test create assessment page.
     */
    public function test_can_render_create_assessment_page(): void
    {
        Child::factory()->count(2)->create();

        $response = $this->get('/assessment/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Create')
            ->has('children', 2)
            ->has('defaultItems')
            ->has('skillTypes')
            ->has('levels')
        );
    }

    public function test_assessment_create_child_dropdown_only_includes_active_children(): void
    {
        $activeChild = Child::factory()->create(['status' => 'active']);
        Child::factory()->create(['status' => 'paused']);
        Child::factory()->create(['status' => 'voided']);

        $response = $this->get('/assessment/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Create')
            ->has('children', 1)
            ->where('children.0.id', $activeChild->id)
        );
    }

    public function test_cannot_create_assessment_for_paused_or_voided_child(): void
    {
        $pausedChild = Child::factory()->create(['status' => 'paused']);
        $voidedChild = Child::factory()->create(['status' => 'voided']);

        foreach ([$pausedChild, $voidedChild] as $child) {
            $response = $this->post('/assessment', [
                'child_id' => $child->id,
                'assessment_date' => '2026-05-20',
                'overall_score' => 75,
                'items' => [
                    [
                        'skill_name' => 'gross_motor',
                        'score' => 75,
                        'level' => 'developing',
                    ],
                ],
            ]);

            $response->assertSessionHasErrors(['child_id']);
        }

        $this->assertDatabaseCount('assessments', 0);
    }

    /**
     * Test storing an assessment.
     */
    public function test_can_store_assessment(): void
    {
        $child = Child::factory()->create();

        $payload = [
            'child_id' => $child->id,
            'assessment_date' => '2026-05-20',
            'overall_score' => 85,
            'notes' => 'Looking very promising.',
            'items' => [
                [
                    'skill_name' => 'gross_motor',
                    'score' => 80,
                    'level' => 'developing',
                    'note' => 'Gross motor looks solid'
                ],
                [
                    'skill_name' => 'fine_motor',
                    'score' => 90,
                    'level' => 'achieved',
                    'note' => 'Excellent hand eye'
                ]
            ]
        ];

        $response = $this->post('/assessment', $payload);

        $response->assertRedirect('/assessment');
        $this->assertDatabaseHas('assessments', [
            'child_id' => $child->id,
            'overall_score' => 85,
            'notes' => 'Looking very promising.',
        ]);

        $this->assertDatabaseHas('assessment_items', [
            'skill_name' => 'gross_motor',
            'score' => 80,
            'level' => 'developing',
        ]);
    }

    /**
     * Test show assessment details.
     */
    public function test_can_show_assessment(): void
    {
        $child = Child::factory()->create();
        $assessment = Assessment::factory()->create(['child_id' => $child->id]);
        AssessmentItem::factory()->create([
            'assessment_id' => $assessment->id,
            'skill_name' => 'cognitive'
        ]);

        $response = $this->get("/assessment/{$assessment->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Show')
            ->has('assessment')
            ->has('skillTypes')
            ->has('levels')
        );
    }

    /**
     * Test edit assessment page.
     */
    public function test_can_render_edit_assessment_page(): void
    {
        $child = Child::factory()->create();
        $assessment = Assessment::factory()->create(['child_id' => $child->id]);

        $response = $this->get("/assessment/{$assessment->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Edit')
            ->has('assessment')
            ->has('children')
            ->has('skillTypes')
            ->has('levels')
        );
    }

    /**
     * Test updating an assessment.
     */
    public function test_can_update_assessment(): void
    {
        $child = Child::factory()->create();
        $assessment = Assessment::factory()->create([
            'child_id' => $child->id,
            'overall_score' => 60
        ]);
        $item = AssessmentItem::factory()->create([
            'assessment_id' => $assessment->id,
            'skill_name' => 'gross_motor',
            'score' => 60
        ]);

        $payload = [
            'child_id' => $child->id,
            'assessment_date' => '2026-05-21',
            'overall_score' => 70,
            'notes' => 'Updated notes here',
            'items' => [
                [
                    'id' => $item->id,
                    'skill_name' => 'gross_motor',
                    'score' => 70,
                    'level' => 'developing',
                    'note' => 'Slight improvement'
                ]
            ]
        ];

        $response = $this->put("/assessment/{$assessment->id}", $payload);

        $response->assertRedirect("/assessment/{$assessment->id}");
        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'overall_score' => 70,
            'notes' => 'Updated notes here',
        ]);

        $this->assertDatabaseHas('assessment_items', [
            'assessment_id' => $assessment->id,
            'skill_name' => 'gross_motor',
            'score' => 70,
            'note' => 'Slight improvement'
        ]);
    }

    /**
     * Test deleting an assessment.
     */
    public function test_can_delete_assessment(): void
    {
        $child = Child::factory()->create();
        $assessment = Assessment::factory()->create(['child_id' => $child->id]);
        AssessmentItem::factory()->create(['assessment_id' => $assessment->id]);

        $response = $this->delete("/assessment/{$assessment->id}");

        $response->assertRedirect('/assessment');
        $this->assertSoftDeleted($assessment);
    }

    /**
     * Test progress page renders with data.
     */
    public function test_can_view_progress_page(): void
    {
        $child = Child::factory()->create();
        $assessment = Assessment::factory()->create(['child_id' => $child->id]);

        foreach (['gross_motor', 'fine_motor'] as $skill) {
            AssessmentItem::factory()->create([
                'assessment_id' => $assessment->id,
                'skill_name' => $skill,
                'score' => 75,
                'level' => 'developing',
            ]);
        }

        $response = $this->get('/assessment/progress');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Progress')
            ->has('progressData')
            ->has('latestSkillLevels', 10)
            ->has('children')
            ->has('filters')
            ->has('skillTypes')
            ->has('levels')
        );
    }

    /**
     * Test progress page filtered by child.
     */
    public function test_progress_page_can_filter_by_child(): void
    {
        $child = Child::factory()->create();
        $assessment = Assessment::factory()->create(['child_id' => $child->id]);
        AssessmentItem::factory()->create([
            'assessment_id' => $assessment->id,
            'skill_name' => 'gross_motor',
        ]);

        $response = $this->get('/assessment/progress?child_id=' . $child->id);

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Assessment/Progress')
            ->where('filters.child_id', (string) $child->id)
        );
    }
}
