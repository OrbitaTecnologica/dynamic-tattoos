<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\CommissionWithdrawal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class CommissionWithdrawals extends Component
{
    /** @return Collection<int, CommissionWithdrawal> */
    #[Computed]
    public function withdrawals(): Collection
    {
        return CommissionWithdrawal::query()->with('user')->latest()->get();
    }

    public function approve(int $id): void
    {
        CommissionWithdrawal::findOrFail($id)->update(['status' => CommissionWithdrawal::STATUS_APPROVED]);
        $this->dispatch('toast', message: 'Retiro aprobado.', type: 'success');
    }

    public function markPaid(int $id): void
    {
        CommissionWithdrawal::findOrFail($id)->update([
            'status' => CommissionWithdrawal::STATUS_PAID,
            'processed_at' => now(),
        ]);
        $this->dispatch('toast', message: 'Retiro marcado como pagado.', type: 'success');
    }

    public function reject(int $id): void
    {
        CommissionWithdrawal::findOrFail($id)->update([
            'status' => CommissionWithdrawal::STATUS_REJECTED,
            'processed_at' => now(),
        ]);
        $this->dispatch('toast', message: 'Retiro rechazado.', type: 'warning');
    }

    public function render(): View
    {
        return view('livewire.admin.commission-withdrawals');
    }
}
