<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\LinkPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class LinkPageApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_show_creates_link_page_for_user_on_first_access(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->getJson('/api/v1/link-page')
            ->assertOk()
            ->assertJsonPath('data.slug', 'u'.$user->id)
            ->assertJsonStructure(['data' => ['id', 'slug', 'public_url', 'links']]);

        $this->assertDatabaseHas('link_pages', ['user_id' => $user->id]);
    }

    public function test_user_can_update_page_and_manage_links(): void
    {
        $user = User::factory()->create();
        $token = $this->token($user);

        $this->withToken($token)
            ->patchJson('/api/v1/link-page', ['title' => 'Mi estudio', 'bio' => 'Tatuajes'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Mi estudio');

        $linkId = $this->withToken($token)
            ->postJson('/api/v1/link-page/links', ['type' => 'instagram', 'value' => 'blackink'])
            ->assertCreated()
            ->assertJsonPath('data.type', 'instagram')
            ->json('data.id');

        $this->withToken($token)
            ->patchJson('/api/v1/link-page/links/'.$linkId, ['label' => 'Síguenos'])
            ->assertOk()
            ->assertJsonPath('data.label', 'Síguenos');

        $this->withToken($token)
            ->getJson('/api/v1/link-page/links')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withToken($token)
            ->deleteJson('/api/v1/link-page/links/'.$linkId)
            ->assertNoContent();
    }

    public function test_user_cannot_modify_other_users_link(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $page = LinkPage::query()->create(['user_id' => $other->id, 'slug' => 'otro', 'title' => 'Otro']);
        $link = $page->links()->create(['type' => 'website', 'value' => 'https://x.com', 'position' => 1]);

        $this->withToken($this->token($owner))
            ->patchJson('/api/v1/link-page/links/'.$link->id, ['label' => 'hack'])
            ->assertForbidden();
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->postJson('/api/v1/link-page/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertOk()
            ->assertJsonPath('data.avatar_url', fn ($url) => is_string($url) && $url !== null);
    }
}
