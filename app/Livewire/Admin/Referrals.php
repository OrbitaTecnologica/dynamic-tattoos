<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Referral;
use App\Models\ReferralVisit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class Referrals extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    // -------------------------------------------------------------------------
    // Computed — KPIs
    // -------------------------------------------------------------------------

    #[Computed]
    public function totalReferrals(): int
    {
        return Referral::query()->count();
    }

    #[Computed]
    public function totalRegistered(): int
    {
        return Referral::query()->where('status', Referral::STATUS_REGISTERED)->count();
    }

    #[Computed]
    public function totalPaid(): int
    {
        return Referral::query()->where('status', Referral::STATUS_PAID)->count();
    }

    #[Computed]
    public function totalRewardEuros(): float
    {
        return round(
            (Referral::query()
                ->where('status', Referral::STATUS_PAID)
                ->sum('reward_cents') / 100),
            2
        );
    }

    #[Computed]
    public function totalVisits(): int
    {
        return ReferralVisit::query()->count();
    }

    // -------------------------------------------------------------------------
    // Computed — Paginated list
    // -------------------------------------------------------------------------

    #[Computed]
    public function referrals(): LengthAwarePaginator
    {
        return Referral::query()
            ->with(['referrer:id,name,email', 'referred:id,name,email'])
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->search !== '', function ($q): void {
                $term = $this->search;
                $q->where(function ($q) use ($term): void {
                    $q->whereHas('referrer', fn ($r) => $r->where('email', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%"))
                        ->orWhereHas('referred', fn ($r) => $r->where('email', 'like', "%{$term}%")
                            ->orWhere('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(20);
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function markPaid(int $id): void
    {
        $referral = Referral::findOrFail($id);

        $configRewardCents = (int) round((float) config('billing.referral_reward', 0) * 100);

        $referral->update([
            'status' => Referral::STATUS_PAID,
            'credited_at' => now(),
            'reward_cents' => max((int) $referral->reward_cents, $configRewardCents),
        ]);

        $this->dispatch('toast', message: 'Referido marcado como pagado.', type: 'success');
    }

    public function markRegistered(int $id): void
    {
        Referral::findOrFail($id)->update([
            'status' => Referral::STATUS_REGISTERED,
            'credited_at' => null,
        ]);

        $this->dispatch('toast', message: 'Referido revertido a registrado.', type: 'warning');
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.referrals');
    }
}
