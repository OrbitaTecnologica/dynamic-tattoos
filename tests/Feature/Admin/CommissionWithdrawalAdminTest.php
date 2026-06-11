<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CommissionWithdrawals;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CommissionWithdrawalAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_mark_paid_and_reject_withdrawals(): void
    {
        $user = User::factory()->create();
        $w = $user->commissionWithdrawals()->create(['amount_cents' => 2000, 'status' => 'requested']);

        Livewire::test(CommissionWithdrawals::class)->call('approve', $w->id);
        $this->assertSame('approved', $w->fresh()->status);

        Livewire::test(CommissionWithdrawals::class)->call('markPaid', $w->id);
        $this->assertSame('paid', $w->fresh()->status);
        $this->assertNotNull($w->fresh()->processed_at);

        $w2 = $user->commissionWithdrawals()->create(['amount_cents' => 500, 'status' => 'requested']);
        Livewire::test(CommissionWithdrawals::class)->call('reject', $w2->id);
        $this->assertSame('rejected', $w2->fresh()->status);
    }
}
