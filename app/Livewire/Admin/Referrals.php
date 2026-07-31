<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Referral;
use App\Models\ReferralVisit;
use App\Models\User;
use App\Services\Referrals\ReferralService;
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

    #[Url]
    public string $tab = 'conversiones';

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

    /** Todos los usuarios con su código, visitas y conversiones (pestaña Recomendadores). */
    #[Computed]
    public function recommenders(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('referral_code', 'like', "%{$this->search}%");
            }))
            ->withCount([
                'referralVisits',
                'referralsMade',
                'referralsMade as paid_referrals_count' => fn ($q) => $q->where('status', Referral::STATUS_PAID),
            ])
            ->withSum(['referralsMade as paid_reward_cents' => fn ($q) => $q->where('status', Referral::STATUS_PAID)], 'reward_cents')
            ->orderByDesc('referral_visits_count')
            ->orderBy('id')
            ->paginate(20, pageName: 'recPage');
    }

    /** Base pública para construir el link de recomendación. */
    public function shareBase(): string
    {
        return rtrim((string) (config('app.frontend_url') ?: 'https://www.dynamic-tattoos.com'), '/');
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function showTab(string $tab): void
    {
        $this->tab = in_array($tab, ['conversiones', 'recomendadores'], true) ? $tab : 'conversiones';
        $this->resetPage();
        $this->resetPage('recPage');
    }

    public function generateCode(int $userId, ReferralService $referrals): void
    {
        $referrals->ensureCode(User::findOrFail($userId));

        unset($this->recommenders);
        $this->dispatch('toast', message: 'Código de recomendación generado.', type: 'success');
    }

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
        $this->resetPage('recPage');
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
