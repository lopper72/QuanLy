<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Assessment;
use App\Models\BehaviorLog;
use App\Models\Report;
use App\Models\TrainingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class ChildControllerTest extends TestCase
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
     * Test children listing.
     */
    public function test_can_list_children(): void
    {
        Child::factory()->count(3)->create();

        $response = $this->get('/children');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Children/Index')
            ->has('children', 3)
            ->has('filters')
        );
    }

    /**
     * Test children searching.
     */
    public function test_can_filter_children_by_search(): void
    {
        Child::factory()->create(['full_name' => 'Liam Smith']);
        Child::factory()->create(['full_name' => 'John Doe']);

        $response = $this->get('/children?search=Liam');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Children/Index')
            ->has('children', 1)
            ->where('children.0.full_name', 'Liam Smith')
        );
    }

    /**
     * Test render create page.
     */
    public function test_can_render_create_page(): void
    {
        $response = $this->get('/children/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Children/Create')
        );
    }

    /**
     * Test store child record.
     */
    public function test_can_store_child(): void
    {
        $data = [
            'full_name' => 'Emma Watson',
            'nickname' => 'Emma',
            'date_of_birth' => '2018-05-12',
            'gender' => 'Female',
            'diagnosis_level' => 'ASD Level 1',
            'notes' => 'Very responsive to visual prompts.',
        ];

        $response = $this->post('/children', $data);

        $this->assertDatabaseHas('children', [
            'full_name' => 'Emma Watson',
            'nickname' => 'Emma',
            'gender' => 'Female',
        ]);

        $child = Child::where('full_name', 'Emma Watson')->first();

        $response->assertRedirect(route('children.show', $child->id));
        $response->assertSessionHas('success', 'Đã tạo hồ sơ trẻ.');
    }

    public function test_child_has_default_active_status(): void
    {
        $child = Child::factory()->create();

        $this->assertSame('active', $child->fresh()->status);
        $this->assertNull($child->fresh()->paused_at);
        $this->assertNull($child->fresh()->voided_at);
    }

    /**
     * Test store child validation.
     */
    public function test_store_child_requires_full_name(): void
    {
        $response = $this->post('/children', [
            'full_name' => '',
        ]);

        $response->assertSessionHasErrors(['full_name']);
    }

    /**
     * Test show child profile.
     */
    public function test_can_show_child_profile(): void
    {
        $child = Child::factory()->create([
            'full_name' => 'James Bond',
        ]);

        $response = $this->get("/children/{$child->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Children/Show')
            ->where('child.id', $child->id)
            ->where('child.full_name', 'James Bond')
        );
    }

    /**
     * Test render edit page.
     */
    public function test_can_render_edit_page(): void
    {
        $child = Child::factory()->create();

        $response = $this->get("/children/{$child->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Children/Edit')
            ->where('child.id', $child->id)
        );
    }

    /**
     * Test update child profile.
     */
    public function test_can_update_child(): void
    {
        $child = Child::factory()->create([
            'full_name' => 'Old Name',
            'nickname' => 'Oldy',
        ]);

        $data = [
            'full_name' => 'New Name',
            'nickname' => 'Newy',
            'date_of_birth' => '2019-01-01',
            'gender' => 'Male',
            'diagnosis_level' => 'ADHD',
            'notes' => 'Updated notes.',
        ];

        $response = $this->put("/children/{$child->id}", $data);

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'full_name' => 'New Name',
            'nickname' => 'Newy',
        ]);

        $response->assertRedirect(route('children.show', $child->id));
        $response->assertSessionHas('success', 'Đã cập nhật hồ sơ trẻ.');
    }

    public function test_user_can_pause_child(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $response = $this->patch(route('children.pause', $child), [
            'status_note' => 'Gia đình xin nghỉ một tuần.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã chuyển trẻ sang trạng thái tạm nghỉ.');
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'paused',
            'status_note' => 'Gia đình xin nghỉ một tuần.',
        ]);
        $this->assertNotNull($child->fresh()->paused_at);
    }

    public function test_user_can_reactivate_paused_child(): void
    {
        $child = Child::factory()->create([
            'status' => 'paused',
            'paused_at' => now(),
            'status_note' => 'Tạm nghỉ do lịch gia đình.',
        ]);

        $response = $this->patch(route('children.activate', $child));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã kích hoạt lại hồ sơ can thiệp.');
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'active',
            'status_note' => null,
        ]);
        $this->assertNull($child->fresh()->paused_at);
    }

    public function test_paused_child_can_resume(): void
    {
        $child = Child::factory()->create([
            'status' => 'paused',
            'paused_at' => now(),
            'status_note' => 'Tạm nghỉ do lịch gia đình.',
        ]);

        $response = $this->post(route('children.resume', $child));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã tiếp tục can thiệp cho trẻ.');
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'active',
            'status_note' => null,
        ]);
        $this->assertNull($child->fresh()->paused_at);
    }

    public function test_stopped_child_can_resume(): void
    {
        $child = Child::factory()->create([
            'status' => 'stopped',
            'status_note' => 'Dừng can thiệp theo lịch gia đình.',
        ]);

        $response = $this->post(route('children.resume', $child));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã tiếp tục can thiệp cho trẻ.');
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'active',
            'status_note' => null,
        ]);
    }

    public function test_voided_child_cannot_resume(): void
    {
        $child = Child::factory()->create([
            'status' => 'voided',
            'voided_at' => now(),
            'status_note' => 'Đã ngừng can thiệp.',
        ]);

        $response = $this->post(route('children.resume', $child));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Chỉ trẻ đang tạm nghỉ hoặc dừng can thiệp mới có thể tiếp tục can thiệp.');
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'voided',
            'status_note' => 'Đã ngừng can thiệp.',
        ]);
    }

    public function test_soft_deleted_child_cannot_resume(): void
    {
        $child = Child::factory()->create([
            'status' => 'paused',
            'paused_at' => now(),
        ]);
        $child->delete();

        $this->post("/children/{$child->id}/resume")->assertNotFound();

        $this->assertSoftDeleted('children', ['id' => $child->id]);
    }

    public function test_active_child_cannot_resume_again(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $response = $this->post(route('children.resume', $child));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Chỉ trẻ đang tạm nghỉ hoặc dừng can thiệp mới có thể tiếp tục can thiệp.');
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_void_child(): void
    {
        $child = Child::factory()->create();

        $response = $this->patch(route('children.void', $child), [
            'status_note' => 'Phụ huynh kết thúc chương trình.',
        ]);

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'voided',
            'status_note' => 'Phụ huynh kết thúc chương trình.',
        ]);
        $this->assertNull($child->fresh()->deleted_at);
        $this->assertNotNull($child->fresh()->voided_at);

        $response->assertRedirect(route('children.index'));
        $response->assertSessionHas('success', 'Đã ngừng can thiệp cho trẻ.');
    }

    public function test_voided_child_is_hidden_from_default_list(): void
    {
        Child::factory()->create(['full_name' => 'Nguyễn Hoàng Nam', 'status' => 'active']);
        Child::factory()->create(['full_name' => 'Trần Bảo Long', 'status' => 'paused']);
        Child::factory()->create(['full_name' => 'Lê Minh Quân', 'status' => 'voided']);

        $this->get('/children')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Children/Index')
                ->has('children', 2)
            );

        $this->get('/children?status=voided')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Children/Index')
                ->has('children', 1)
                ->where('children.0.status', 'voided')
            );
    }

    public function test_voided_child_keeps_historical_records_and_is_not_hard_deleted(): void
    {
        $child = Child::factory()->create();
        $trainingSession = TrainingSession::factory()->create(['child_id' => $child->id]);
        $assessment = Assessment::factory()->create(['child_id' => $child->id]);
        $behaviorLog = BehaviorLog::factory()->create(['child_id' => $child->id]);
        $report = Report::factory()->create(['child_id' => $child->id]);

        $this->patch(route('children.void', $child));

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'voided',
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('training_sessions', ['id' => $trainingSession->id, 'child_id' => $child->id]);
        $this->assertDatabaseHas('assessments', ['id' => $assessment->id, 'child_id' => $child->id]);
        $this->assertDatabaseHas('behavior_logs', ['id' => $behaviorLog->id, 'child_id' => $child->id]);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'child_id' => $child->id]);

        $this->get(route('children.show', $child))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Children/Show')
                ->where('child.id', $child->id)
                ->where('child.status', 'voided')
            );
    }

    public function test_active_child_cannot_be_deleted(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $response = $this->delete("/children/{$child->id}");

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'active',
            'deleted_at' => null,
        ]);

        $response->assertRedirect(route('children.index'));
        $response->assertSessionHas('error', 'Chỉ hồ sơ đã ngừng/dừng can thiệp mới có thể xóa.');
    }

    public function test_paused_child_cannot_be_deleted(): void
    {
        $child = Child::factory()->create(['status' => 'paused']);

        $response = $this->delete("/children/{$child->id}");

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'status' => 'paused',
            'deleted_at' => null,
        ]);

        $response->assertRedirect(route('children.index'));
        $response->assertSessionHas('error', 'Chỉ hồ sơ đã ngừng/dừng can thiệp mới có thể xóa.');
    }

    public function test_voided_child_can_be_deleted(): void
    {
        $child = Child::factory()->create([
            'status' => 'voided',
            'voided_at' => now(),
        ]);

        $response = $this->delete("/children/{$child->id}");

        $this->assertSoftDeleted('children', ['id' => $child->id]);

        $response->assertRedirect(route('children.index'));
        $response->assertSessionHas('success', 'Đã xóa hồ sơ trẻ.');
    }

    public function test_stopped_child_can_be_deleted(): void
    {
        $child = Child::factory()->create(['status' => 'stopped']);

        $response = $this->delete("/children/{$child->id}");

        $this->assertSoftDeleted('children', ['id' => $child->id]);

        $response->assertRedirect(route('children.index'));
        $response->assertSessionHas('success', 'Đã xóa hồ sơ trẻ.');
    }

    public function test_deleted_child_disappears_from_list(): void
    {
        $child = Child::factory()->create([
            'status' => 'voided',
            'voided_at' => now(),
        ]);

        $this->delete("/children/{$child->id}");

        // Should not appear in voided filter list
        $this->get('/children?status=voided')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Children/Index')
                ->has('children', 0)
            );

        // Should not appear in default (non-voided) list
        $this->get('/children')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Children/Index')
                ->has('children', 0)
            );
    }

    public function test_deleted_child_soft_delete_keeps_historical_records(): void
    {
        $child = Child::factory()->create([
            'status' => 'voided',
            'voided_at' => now(),
        ]);
        $trainingSession = TrainingSession::factory()->create(['child_id' => $child->id]);
        $assessment = Assessment::factory()->create(['child_id' => $child->id]);

        $this->delete("/children/{$child->id}");

        $this->assertSoftDeleted('children', ['id' => $child->id]);
        $this->assertDatabaseHas('training_sessions', ['id' => $trainingSession->id]);
        $this->assertDatabaseHas('assessments', ['id' => $assessment->id]);
    }
}
