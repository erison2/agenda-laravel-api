<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_activity()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/activities', [
            'title' => 'Meeting',
            'type' => 'Work',
            'description' => 'Project meeting',
            'user_id' => $user->id,
            'status' => 'open',
            'start_date' => '2024-05-31 13:00:00',
            'end_date' => '2024-05-31 15:00:00',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'title', 'type', 'description', 'user_id', 'start_date', 'end_date', 'status']);
    }

    public function test_update_activity()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2024-05-31 13:00:00',
            'end_date' => '2024-05-31 15:00:00',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/v1/activities/{$activity->id}", [
            'title' => 'Updated Meeting',
            'start_date' => '2024-05-31 13:00:00',
            'end_date' => '2024-05-31 15:00:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['title' => 'Updated Meeting']);
    }

    public function test_show_activity()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $activity = Activity::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/activities/{$activity->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $activity->id]);
    }

    public function test_list_activities()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        Activity::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/activities');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_delete_activity()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $activity = Activity::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/v1/activities/{$activity->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }

    public function test_activity_cannot_overlap()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $existingActivity = Activity::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2024-05-31 13:00:00',
            'end_date' => '2024-05-31 15:00:00',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/activities', [
            'title' => 'Overlapping Meeting',
            'type' => 'Work',
            'description' => 'Another meeting',
            'user_id' => $user->id,
            'start_date' => '2024-05-31 14:00:00',
            'end_date' => '2024-05-31 16:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Activities cannot overlap for the same user.']);
    }

    public function test_activity_cannot_be_scheduled_on_weekend()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/activities', [
            'title' => 'Weekend Meeting',
            'type' => 'Work',
            'description' => 'Meeting on weekend',
            'user_id' => $user->id,
            'start_date' => '2024-06-01 13:00:00', // Saturday
            'end_date' => '2024-06-01 15:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Activities cannot be scheduled on weekends.']);
    }
}
