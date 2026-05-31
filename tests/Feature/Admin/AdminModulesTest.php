<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PricingPlans;
use App\Livewire\Admin\Referrals;
use App\Livewire\Admin\StoragePacks;
use App\Livewire\Admin\TattooList;
use App\Livewire\Admin\UserList;
use App\Models\Plan;
use App\Models\Referral;
use App\Models\StoragePack;
use App\Models\Tattoo;
use App\Models\TattooContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_storage_pack_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(StoragePacks::class)
            ->set('label', 'Pack 1 GB')
            ->set('sizeMb', 1024)
            ->set('price', '4.90')
            ->set('isActive', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('storage_packs', ['label' => 'Pack 1 GB', 'size_mb' => 1024]);
        $pack = StoragePack::firstOrFail();

        Livewire::test(StoragePacks::class)
            ->call('openEdit', $pack->id)
            ->set('label', 'Pack 2 GB')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('storage_packs', ['id' => $pack->id, 'label' => 'Pack 2 GB']);

        Livewire::test(StoragePacks::class)->call('deletePack', $pack->id);
        $this->assertDatabaseMissing('storage_packs', ['id' => $pack->id]);
    }

    public function test_plan_can_be_created_as_referral_with_per_plan_reward(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(PricingPlans::class)
            ->set('name', 'Partner X')
            ->set('price', '29.90')
            ->set('billingCycle', 'yearly')
            ->set('maxTattoos', 99)
            ->set('storageMb', 10000)
            ->set('isFeatured', true)
            ->set('isReferral', true)
            ->set('referralReward', '8')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('plans', [
            'name' => 'Partner X',
            'is_referral' => 1,
            'is_featured' => 1,
            'storage_mb' => 10000,
        ]);

        $plan = Plan::query()->where('name', 'Partner X')->firstOrFail();
        $this->assertSame('8.00', (string) $plan->referral_reward);
    }

    public function test_non_referral_plan_does_not_store_reward(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(PricingPlans::class)
            ->set('name', 'Básico Y')
            ->set('price', '4.90')
            ->set('isReferral', false)
            ->set('referralReward', '8')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(Plan::query()->where('name', 'Básico Y')->firstOrFail()->referral_reward);
    }

    public function test_admin_cannot_delete_self_but_can_delete_others_and_reset_2fa(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        // No puede borrarse a sí mismo.
        Livewire::test(UserList::class)->call('deleteUser', $admin->id);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Puede borrar a otro usuario.
        $victim = User::factory()->create();
        Livewire::test(UserList::class)->call('deleteUser', $victim->id);
        $this->assertDatabaseMissing('users', ['id' => $victim->id]);

        // Restablece el 2FA.
        $with2fa = User::factory()->create([
            'two_factor_secret' => 'SECRET',
            'two_factor_confirmed_at' => now(),
        ]);
        Livewire::test(UserList::class)->call('resetTwoFactor', $with2fa->id);
        $this->assertNull($with2fa->fresh()->two_factor_confirmed_at);
    }

    public function test_tattoo_content_can_be_activated_and_tattoo_deleted(): void
    {
        $this->actingAs($this->admin());
        $owner = User::factory()->create();

        $tattoo = Tattoo::create([
            'user_id' => $owner->id,
            'short_code' => 'ABC123',
            'name' => 'Mi tatuaje',
            'is_active' => true,
        ]);
        $v1 = TattooContent::create(['tattoo_id' => $tattoo->id, 'type' => 'link', 'payload' => ['url' => 'https://a.test'], 'is_active' => true, 'order' => 0]);
        $v2 = TattooContent::create(['tattoo_id' => $tattoo->id, 'type' => 'link', 'payload' => ['url' => 'https://b.test'], 'is_active' => false, 'order' => 1]);

        Livewire::test(TattooList::class)->call('activateContent', $v2->id);

        $this->assertTrue($v2->fresh()->is_active);
        $this->assertFalse($v1->fresh()->is_active);

        Livewire::test(TattooList::class)->call('delete', $tattoo->id);
        $this->assertDatabaseMissing('tattoos', ['id' => $tattoo->id]);
    }

    public function test_referral_can_be_marked_paid_manually(): void
    {
        $this->actingAs($this->admin());

        $ref = Referral::create([
            'referrer_id' => User::factory()->create()->id,
            'referred_id' => User::factory()->create()->id,
            'status' => Referral::STATUS_REGISTERED,
            'reward_cents' => 0,
        ]);

        Livewire::test(Referrals::class)->call('markPaid', $ref->id);

        $fresh = $ref->fresh();
        $this->assertSame(Referral::STATUS_PAID, $fresh->status);
        $this->assertNotNull($fresh->credited_at);
        $this->assertGreaterThan(0, $fresh->reward_cents);
    }
}
