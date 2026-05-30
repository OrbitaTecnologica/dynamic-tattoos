<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\QrCodeMail;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class QrCodeApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_user_can_create_and_list_own_qr_codes(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->postJson('/api/v1/qr-codes', [
                'slug' => 'sesion-mayo',
                'url' => 'https://d-tattoo.com/sesion-mayo',
                'color' => '#1a1a1a',
                'dots_type' => 'rounded',
                'corners_square_type' => 'extra-rounded',
                'corners_dot_type' => 'dot',
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'sesion-mayo')
            ->assertJsonPath('data.dots_type', 'rounded');

        $this->withToken($this->token($user))
            ->getJson('/api/v1/qr-codes')
            ->assertOk()
            ->assertJsonPath('data.0.url', 'https://d-tattoo.com/sesion-mayo');

        $this->assertDatabaseHas('qr_codes', [
            'user_id' => $user->id,
            'slug' => 'sesion-mayo',
        ]);
    }

    public function test_index_only_returns_own_qr_codes(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        QrCode::query()->create(['user_id' => $other->id, 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($owner))
            ->getJson('/api/v1/qr-codes')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_view_other_users_qr_code(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $qr = QrCode::query()->create(['user_id' => $other->id, 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($owner))
            ->getJson('/api/v1/qr-codes/' . $qr->id)
            ->assertForbidden();
    }

    public function test_slug_availability(): void
    {
        $user = User::factory()->create();
        QrCode::query()->create(['user_id' => $user->id, 'slug' => 'tomado', 'url' => 'https://d-tattoo.com/tomado']);

        $this->withToken($this->token($user))
            ->getJson('/api/v1/qr-codes/slug-available?slug=libre')
            ->assertOk()
            ->assertJsonPath('data.available', true);

        $this->withToken($this->token($user))
            ->getJson('/api/v1/qr-codes/slug-available?slug=tomado')
            ->assertOk()
            ->assertJsonPath('data.available', false);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        QrCode::query()->create(['user_id' => $user->id, 'slug' => 'tomado', 'url' => 'https://d-tattoo.com/tomado']);

        $this->withToken($this->token($user))
            ->postJson('/api/v1/qr-codes', [
                'slug' => 'tomado',
                'url' => 'https://d-tattoo.com/tomado',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['slug']]]);
    }

    public function test_user_can_email_own_qr_code(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $qr = QrCode::query()->create(['user_id' => $user->id, 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($user))
            ->postJson('/api/v1/qr-codes/' . $qr->id . '/email', [
                'email' => 'amigo@example.com',
                'image' => 'data:image/png;base64,iVBORw0KGgo=',
            ])
            ->assertOk();

        Mail::assertSent(QrCodeMail::class);
    }

    public function test_user_can_delete_own_qr_code(): void
    {
        $user = User::factory()->create();
        $qr = QrCode::query()->create(['user_id' => $user->id, 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($user))
            ->deleteJson('/api/v1/qr-codes/' . $qr->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('qr_codes', ['id' => $qr->id]);
    }
}
