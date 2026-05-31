<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingGateway;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

final class Subscriptions extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function subscribers(): LengthAwarePaginator
    {
        return User::query()
            ->whereHas('subscriptions')
            ->with(['subscriptions', 'plan:id,name'])
            ->when($this->search !== '', fn ($q) => $q->where(function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus !== '', function ($q): void {
                match ($this->filterStatus) {
                    'active' => $q->whereHas('subscriptions', fn ($s) => $s
                        ->where('type', 'default')
                        ->where('stripe_status', 'active')
                        ->whereNull('ends_at')),
                    'canceled' => $q->whereHas('subscriptions', fn ($s) => $s
                        ->where('type', 'default')
                        ->whereNotNull('ends_at')),
                    default => null,
                };
            })
            ->latest()
            ->paginate(20);
    }

    /** @return Collection<string, string>  stripe_price_id => plan name */
    #[Computed]
    public function planMap(): Collection
    {
        return Plan::query()
            ->whereNotNull('stripe_price_id')
            ->get(['stripe_price_id', 'name'])
            ->pluck('name', 'stripe_price_id');
    }

    // -------------------------------------------------------------------------
    // Watchers
    // -------------------------------------------------------------------------

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function cancel(int $userId): void
    {
        $user = User::findOrFail($userId);

        try {
            app(BillingGateway::class)->cancelSubscription($user, 'default');
            $this->dispatch('toast', message: 'Suscripción cancelada correctamente.', type: 'success');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: 'Error al cancelar la suscripción: '.$e->getMessage(), type: 'error');
        }

        unset($this->subscribers);
    }

    public function resume(int $userId): void
    {
        $user = User::findOrFail($userId);

        try {
            app(BillingGateway::class)->resumeSubscription($user, 'default');
            $this->dispatch('toast', message: 'Suscripción reanudada correctamente.', type: 'success');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: 'Error al reanudar la suscripción: '.$e->getMessage(), type: 'error');
        }

        unset($this->subscribers);
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render(): View
    {
        return view('livewire.admin.subscriptions');
    }
}
