<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlipPlayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_can_be_created_listed_shown_updated_and_deleted(): void
    {
        $payload = $this->mediaPayload();

        $this->postJson('/api/media', $payload)
            ->assertCreated()
            ->assertJsonPath('data.title', $payload['title']);

        $media = Media::firstOrFail();

        $this->getJson('/api/media?search=Frieren')
            ->assertOk()
            ->assertJsonPath('data.0.id', $media->id);

        $this->getJson("/api/media/{$media->id}")
            ->assertOk()
            ->assertJsonPath('data.users', []);

        $this->patchJson("/api/media/{$media->id}", ['title' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        $this->patchJson("/api/media/{$media->id}", ['score' => 9.5])
            ->assertOk()
            ->assertJsonPath('data.score', 9.5);

        $this->deleteJson("/api/media/{$media->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_media_title_is_required_and_unique_for_its_type(): void
    {
        $this->postJson('/api/media', array_merge($this->mediaPayload(), ['title' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        Media::create($this->mediaPayload());

        $this->postJson('/api/media', $this->mediaPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_users_can_manage_a_media_collection_without_duplicates(): void
    {
        $userResponse = $this->postJson('/api/users', [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password123',
        ])->assertCreated()
            ->assertJsonMissingPath('data.password');
        $user = User::findOrFail($userResponse->json('data.id'));
        $media = Media::create($this->mediaPayload());

        $this->postJson("/api/users/{$user->id}/collection/{$media->id}")
            ->assertCreated()
            ->assertJsonPath('data.progress', 0);

        $this->postJson("/api/users/{$user->id}/collection/{$media->id}")
            ->assertStatus(409);

        $this->getJson("/api/users/{$user->id}/collection")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $media->id);

        $this->getJson("/api/media/{$media->id}")
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id);

        $this->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.media.0.id', $media->id);

        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('media_user', [
            'media_id' => $media->id,
            'user_id' => $user->id,
        ]);
    }

    private function mediaPayload(): array
    {
        return [
            'title' => 'Frieren: Beyond Journey\'s End',
            'type' => 'anime',
            'year' => 2023,
            'score' => 9.1,
            'genres' => ['Adventure', 'Drama', 'Fantasy'],
            'status' => 'finished',
            'total_units' => 28,
            'creator' => 'Madhouse',
        ];
    }
}
