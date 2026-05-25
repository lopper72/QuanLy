<?php

namespace Tests\Feature;

use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class ExerciseControllerTest extends TestCase
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
     * Test exercises listing.
     */
    public function test_can_list_exercises(): void
    {
        Exercise::factory()->count(3)->create(['is_active' => true, 'thumbnail_path' => 'exercises/placeholders/gross_motor.jpg']);

        $response = $this->get('/exercises');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Index')
            ->has('exercises', 3)
            ->where('exercises.0.thumbnail_path', 'exercises/placeholders/gross_motor.jpg')
            ->has('filters')
            ->has('categories')
            ->has('difficulties')
        );
    }

    /**
     * Test exercises searching.
     */
    public function test_can_filter_exercises_by_search(): void
    {
        Exercise::factory()->create(['title' => 'Block Stacking', 'is_active' => true]);
        Exercise::factory()->create(['title' => 'Puzzles Level 1', 'is_active' => true]);

        $response = $this->get('/exercises?search=Block');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Index')
            ->has('exercises', 1)
            ->where('exercises.0.title', 'Block Stacking')
        );
    }

    /**
     * Test exercises filtering by category.
     */
    public function test_can_filter_exercises_by_category(): void
    {
        Exercise::factory()->create(['category' => 'gross_motor', 'is_active' => true]);
        Exercise::factory()->create(['category' => 'fine_motor', 'is_active' => true]);

        $response = $this->get('/exercises?category=gross_motor');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Index')
            ->has('exercises', 1)
            ->where('exercises.0.category', 'gross_motor')
        );
    }

    /**
     * Test exercises filtering by difficulty.
     */
    public function test_can_filter_exercises_by_difficulty(): void
    {
        Exercise::factory()->create(['difficulty' => 'easy', 'is_active' => true]);
        Exercise::factory()->create(['difficulty' => 'hard', 'is_active' => true]);

        $response = $this->get('/exercises?difficulty=easy');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Index')
            ->has('exercises', 1)
            ->where('exercises.0.difficulty', 'easy')
        );
    }

    /**
     * Test exercises filtering by active status.
     */
    public function test_can_filter_exercises_by_status(): void
    {
        Exercise::factory()->create(['is_active' => true]);
        Exercise::factory()->create(['is_active' => false]);

        $response = $this->get('/exercises?is_active=1');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Index')
            ->has('exercises', 1)
            ->where('exercises.0.is_active', true)
        );
    }

    /**
     * Test render create page.
     */
    public function test_can_render_create_page(): void
    {
        $response = $this->get('/exercises/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Create')
            ->has('categories')
            ->has('difficulties')
        );
    }

    /**
     * Test store exercise.
     */
    public function test_can_store_exercise(): void
    {
        $data = [
            'title' => 'Sensory Sandbox Play',
            'slug' => 'sensory-sandbox',
            'category' => 'sensory',
            'difficulty' => 'easy',
            'estimated_minutes' => 15,
            'is_active' => true,
            'instructions' => 'Let the child play in the sand with tools.',
        ];

        $response = $this->post('/exercises', $data);

        $this->assertDatabaseHas('exercises', [
            'title' => 'Sensory Sandbox Play',
            'slug' => 'sensory-sandbox',
            'category' => 'sensory',
            'difficulty' => 'easy',
            'is_active' => true,
        ]);

        $exercise = Exercise::where('title', 'Sensory Sandbox Play')->first();

        $response->assertRedirect(route('exercises.show', $exercise->id));
        $response->assertSessionHas('success', 'Đã tạo bài tập.');
    }

    /**
     * Test store exercise validation.
     */
    public function test_store_exercise_validation(): void
    {
        $response = $this->post('/exercises', [
            'title' => '',
            'category' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'category']);
    }

    /**
     * Test show exercise.
     */
    public function test_can_show_exercise(): void
    {
        $exercise = Exercise::factory()->create(['title' => 'Specific Test Title']);

        $response = $this->get("/exercises/{$exercise->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Show')
            ->where('exercise.id', $exercise->id)
            ->where('exercise.title', 'Specific Test Title')
        );
    }

    /**
     * Test render edit page.
     */
    public function test_can_render_edit_page(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->get("/exercises/{$exercise->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Exercises/Edit')
            ->where('exercise.id', $exercise->id)
        );
    }

    /**
     * Test update exercise.
     */
    public function test_can_update_exercise(): void
    {
        $exercise = Exercise::factory()->create([
            'title' => 'Old Title',
            'category' => 'social',
        ]);

        $data = [
            'title' => 'New Awesome Title',
            'category' => 'cognitive',
            'difficulty' => 'medium',
            'estimated_minutes' => 20,
            'is_active' => false,
            'instructions' => 'Updated instruction steps.',
        ];

        $response = $this->put("/exercises/{$exercise->id}", $data);

        $this->assertDatabaseHas('exercises', [
            'id' => $exercise->id,
            'title' => 'New Awesome Title',
            'category' => 'cognitive',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('exercises.show', $exercise->id));
        $response->assertSessionHas('success', 'Đã cập nhật bài tập.');
    }

    /**
     * Test delete exercise.
     */
    public function test_can_delete_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->delete("/exercises/{$exercise->id}");

        $this->assertSoftDeleted('exercises', [
            'id' => $exercise->id,
        ]);

        $response->assertRedirect(route('exercises.index'));
        $response->assertSessionHas('success', 'Đã xóa bài tập.');
    }

    /**
     * Test store exercise with media and steps.
     */
    public function test_can_store_exercise_with_media_and_steps(): void
    {
        Storage::fake('public');

        $data = [
            'title' => 'Media Exercise',
            'category' => 'sensory',
            'difficulty' => 'medium',
            'thumbnail' => UploadedFile::fake()->create('thumb.jpg', 100, 'image/jpeg'),
            'video' => UploadedFile::fake()->create('video.mp4', 1000, 'video/mp4'),
            'video_url' => 'https://youtube.com/watch?v=123',
            'steps' => [
                [
                    'title' => 'Step 1',
                    'instruction' => 'Do this',
                    'image' => UploadedFile::fake()->create('step1.jpg', 100, 'image/jpeg'),
                ],
                [
                    'title' => 'Step 2',
                    'instruction' => 'Do that',
                ]
            ]
        ];

        $response = $this->post('/exercises', $data);

        $exercise = Exercise::where('title', 'Media Exercise')->first();
        $this->assertNotNull($exercise);
        $this->assertNotNull($exercise->thumbnail_path);
        $this->assertNotNull($exercise->video_path);
        $this->assertEquals('https://youtube.com/watch?v=123', $exercise->video_url);
        $this->assertCount(2, $exercise->steps);
        $this->assertNotNull($exercise->steps[0]->image_path);

        Storage::disk('public')->assertExists($exercise->thumbnail_path);
        Storage::disk('public')->assertExists($exercise->video_path);
        Storage::disk('public')->assertExists($exercise->steps[0]->image_path);

        $response->assertRedirect(route('exercises.show', $exercise->id));
    }

    /**
     * Test update exercise with media and steps.
     */
    public function test_can_update_exercise_with_media_and_steps(): void
    {
        Storage::fake('public');

        $exercise = Exercise::factory()->create();
        $exercise->steps()->create(['title' => 'Old Step', 'order' => 1]);

        $data = [
            'title' => 'Updated Media Exercise',
            'category' => 'sensory',
            'thumbnail' => UploadedFile::fake()->create('new_thumb.jpg', 100, 'image/jpeg'),
            'steps' => [
                [
                    'title' => 'New Step 1',
                    'instruction' => 'New instruction',
                    'image' => UploadedFile::fake()->create('new_step1.jpg', 100, 'image/jpeg'),
                ]
            ]
        ];

        // Use POST with _method=PUT for file uploads in tests too if needed, 
        // but Laravel's $this->put() handles it if we don't use multipart/form-data explicitly in the test helper.
        // Actually, for files, we should use $this->post() with _method.
        $response = $this->post("/exercises/{$exercise->id}", array_merge($data, ['_method' => 'PUT']));

        $exercise->refresh();
        $this->assertEquals('Updated Media Exercise', $exercise->title);
        $this->assertNotNull($exercise->thumbnail_path);
        $this->assertCount(1, $exercise->steps);
        $this->assertEquals('New Step 1', $exercise->steps[0]->title);

        Storage::disk('public')->assertExists($exercise->thumbnail_path);
        Storage::disk('public')->assertExists($exercise->steps[0]->image_path);

        $response->assertRedirect(route('exercises.show', $exercise->id));
    }

    public function test_placeholder_generation_does_not_overwrite_existing_thumbnail(): void
    {
        Storage::fake('public');
        Http::fake();

        $exercise = Exercise::factory()->create([
            'thumbnail_path' => 'exercises/thumbnails/existing.jpg',
        ]);
        Storage::disk('public')->put('exercises/thumbnails/existing.jpg', 'existing-image');

        Artisan::call('exercises:generate-placeholders');

        $exercise->refresh();
        $this->assertSame('exercises/thumbnails/existing.jpg', $exercise->thumbnail_path);
        Storage::disk('public')->assertExists('exercises/thumbnails/existing.jpg');
    }

    public function test_placeholder_generation_uses_local_fallback_when_download_fails(): void
    {
        Storage::fake('public');
        Http::fake([
            '*' => Http::response('Không có ảnh', 500),
        ]);

        $exercise = Exercise::factory()->create([
            'category' => 'gross_motor',
            'thumbnail_path' => null,
        ]);

        Artisan::call('exercises:generate-placeholders');

        $exercise->refresh();
        $this->assertSame('exercises/placeholders/gross_motor.svg', $exercise->thumbnail_path);
        Storage::disk('public')->assertExists('exercises/placeholders/gross_motor.svg');
    }
}
