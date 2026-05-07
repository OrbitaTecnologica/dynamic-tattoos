<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Laravel\Cashier\Subscription;

final class BillingManager extends Component
{
    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    #[Computed]
    public function currentPlan(): ?Plan
    {
        return $this->user()->plan;
    }

    #[Computed]
    public function currentSubscription(): ?Subscription
    {
        return $this->user()->subscription('default');
    }

    #[Computed]
    public function availablePlans(): Collection
    {
        return Plan::query()
            ->active()
            ->ordered()
            ->get();
    }

    #[Computed]
    public function subscriptionStatus(): string
    {
        $subscription = $this->currentSubscription();

        if ($subscription === null) {
            return 'sin_plan';
        }

        if ($subscription->onGracePeriod()) {
            return 'grace_period';
        }

        if ($subscription->cancelled()) {
            return 'cancelada';
        }

        if ($subscription->valid()) {
            return 'activa';
        }

        return 'inactiva';
    }

    public function render(): View
    {
        return view('livewire.billing-manager');
    }
}
