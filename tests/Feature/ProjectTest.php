<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    }

    public function test_dashboard_shows_user_projects(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($project->title);
    }

    public function test_user_can_view_their_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/flowchart/{$project->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_others_project(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/flowchart/{$project->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_create_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'title' => 'New Project',
            'description' => 'New Description',
            'content' => json_encode(['nodes' => [], 'edges' => []]),
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('projects', [
            'title' => 'New Project',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_their_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/projects', [
            'id' => $project->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'content' => json_encode(['nodes' => [], 'edges' => []]),
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_others_project(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post('/projects', [
            'id' => $project->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'content' => json_encode(['nodes' => [], 'edges' => []]),
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/projects/{$project->id}");

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_user_cannot_delete_others_project(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete("/projects/{$project->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_user_can_autosave_their_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $newContent = json_encode(['nodes' => [['id' => '1', 'label' => 'New Node']], 'edges' => []]);
        $response = $this->actingAs($user)->patch("/projects/{$project->id}/autosave", [
            'content' => $newContent,
            'title' => 'Autosaved Title',
            'description' => 'Autosaved Description',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'content' => $newContent,
            'title' => 'Autosaved Title',
        ]);
    }

    public function test_user_cannot_autosave_others_project(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->patch("/projects/{$project->id}/autosave", [
            'content' => json_encode(['nodes' => [], 'edges' => []]),
        ]);

        $response->assertStatus(403);
    }

    public function test_autosave_requires_content(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patch("/projects/{$project->id}/autosave", [
            'title' => 'New Title',
        ]);

        $response->assertSessionHasErrors('content');
    }
}
