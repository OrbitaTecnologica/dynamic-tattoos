<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\QrCodeMail;
use App\Models\Plan;
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

    private function paidPlan(string $slug = 'pro'): Plan
    {
        return Plan::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'price' => 65,
            'billing_cycle' => 'yearly',
            'features' => ['x'],
            'max_tattoos' => 5,
            'is_active' => true,
            'sort_order' => 4,
            'stripe_price_id' => 'price_'.$slug.'_123',
        ]);
    }

    private function freePlan(): Plan
    {
        return Plan::query()->create([
            'name' => 'Embajador',
            'slug' => 'embajador',
            'price' => 0,
            'billing_cycle' => 'yearly',
            'features' => ['x'],
            'max_tattoos' => 0,
            'is_active' => true,
            'sort_order' => 1,
            'is_referral' => true,
        ]);
    }

    private function paidUser(): User
    {
        return User::factory()->create(['plan_id' => $this->paidPlan()->id]);
    }

    public function test_paid_user_can_create_qr_with_auto_generated_code(): void
    {
        $user = $this->paidUser();

        $res = $this->withToken($this->token($user))
            ->postJson('/api/v1/qr-codes', [
                'name' => 'Floppy',
                'color' => '#1a1a1a',
                'dots_type' => 'rounded',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Floppy');

        // El slug se genera server-side: 6 caracteres MAYÚSCULAS sin ambiguos.
        $slug = $res->json('data.slug');
        $this->assertMatchesRegularExpression('/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{6}$/', (string) $slug);

        // La URL pública es d-t.me/{code}.
        $res->assertJsonPath('data.public_url', 'https://d-t.me/'.$slug);

        $this->assertDatabaseHas('qr_codes', ['user_id' => $user->id, 'slug' => $slug]);
    }

    public function test_client_supplied_slug_is_ignored(): void
    {
        $user = $this->paidUser();

        $slug = $this->withToken($this->token($user))
            ->postJson('/api/v1/qr-codes', ['slug' => 'mi-slug-elegido', 'name' => 'X'])
            ->assertCreated()
            ->json('data.slug');

        $this->assertNotSame('mi-slug-elegido', $slug);
        $this->assertMatchesRegularExpression('/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{6}$/', (string) $slug);
    }

    public function test_only_one_qr_per_user(): void
    {
        $user = $this->paidUser();
        QrCode::query()->create(['user_id' => $user->id, 'slug' => 'AAAAAA', 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($user))
            ->postJson('/api/v1/qr-codes', ['name' => 'segundo'])
            ->assertStatus(422);

        $this->assertSame(1, QrCode::query()->where('user_id', $user->id)->count());
    }

    public function test_free_plan_cannot_create_qr(): void
    {
        $free = User::factory()->create(['plan_id' => $this->freePlan()->id]);

        $this->withToken($this->token($free))
            ->postJson('/api/v1/qr-codes', ['name' => 'x'])
            ->assertForbidden();
    }

    public function test_user_without_plan_cannot_create_qr(): void
    {
        $user = User::factory()->create(['plan_id' => null]);

        $this->withToken($this->token($user))
            ->postJson('/api/v1/qr-codes', ['name' => 'x'])
            ->assertForbidden();
    }

    public function test_two_users_can_share_the_same_username(): void
    {
        $plan = $this->paidPlan();
        $u1 = User::factory()->create(['plan_id' => $plan->id]);
        $u2 = User::factory()->create(['plan_id' => $plan->id]);

        $s1 = $this->withToken($this->token($u1))
            ->postJson('/api/v1/qr-codes', ['name' => 'Maria'])->assertCreated()->json('data.slug');
        $s2 = $this->withToken($this->token($u2))
            ->postJson('/api/v1/qr-codes', ['name' => 'Maria'])->assertCreated()->json('data.slug');

        // Mismo username, pero códigos (slugs) distintos.
        $this->assertNotSame($s1, $s2);
    }

    public function test_index_only_returns_own_qr_codes(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        QrCode::query()->create(['user_id' => $other->id, 'slug' => 'OTHERX', 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($owner))
            ->getJson('/api/v1/qr-codes')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_view_other_users_qr_code(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $qr = QrCode::query()->create(['user_id' => $other->id, 'slug' => 'OTHERY', 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($owner))
            ->getJson('/api/v1/qr-codes/' . $qr->id)
            ->assertForbidden();
    }

    public function test_user_can_email_own_qr_code(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $qr = QrCode::query()->create(['user_id' => $user->id, 'slug' => 'MAILXX', 'url' => 'https://d-tattoo.com/x']);

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
        $qr = QrCode::query()->create(['user_id' => $user->id, 'slug' => 'DELXXX', 'url' => 'https://d-tattoo.com/x']);

        $this->withToken($this->token($user))
            ->deleteJson('/api/v1/qr-codes/' . $qr->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('qr_codes', ['id' => $qr->id]);
    }
}
