<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class PricingPlans extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $name = '';

    public string $billingCycle = 'monthly';

    public string $stripePriceId = '';

    public string $price = '0';

    public int $maxTattoos = 1;

    public string $featuresRaw = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function plans(): Collection
    {
        return Plan::query()
            ->withCount('users')
            ->ordered()
            ->get();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'billingCycle', 'stripePriceId', 'price', 'maxTattoos', 'featuresRaw', 'isActive', 'sortOrder']);
        $this->billingCycle = 'monthly';
        $this->isActive = true;
        $this->showModal = true;
    }

    public function openEdit(int $planId): void
    {
        $plan = Plan::findOrFail($planId);

        $this->editingId = $plan->id;
        $this->name = $plan->name;
        $this->billingCycle = $plan->billing_cycle;
        $this->stripePriceId = $plan->stripe_price_id ?? '';
        $this->price = (string) $plan->price;
        $this->maxTattoos = $plan->max_tattoos;
        $this->featuresRaw = implode("\n", $plan->features ?? []);
        $this->isActive = $plan->is_active;
        $this->sortOrder = $plan->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'billingCycle' => ['required', 'in:monthly,yearly,lifetime'],
            'stripePriceId' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'maxTattoos' => ['required', 'integer', 'min:1', 'max:999'],
            'featuresRaw' => ['nullable', 'string'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
        ]);

        $features = array_filter(
            array_map('trim', explode("\n", $this->featuresRaw)),
            static fn (string $f): bool => $f !== ''
        );

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'billing_cycle' => $this->billingCycle,
            'stripe_price_id' => $this->stripePriceId !== '' ? $this->stripePriceId : null,
            'price' => (float) $this->price,
            'max_tattoos' => $this->maxTattoos,
            'features' => array_values($features),
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId !== null) {
            Plan::findOrFail($this->editingId)->update($data);
            $message = 'Plan actualizado.';
        } else {
            Plan::create($data);
            $message = 'Plan creado.';
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleActive(int $planId): void
    {
        $plan = Plan::findOrFail($planId);
        $plan->update(['is_active' => ! $plan->is_active]);
        $this->dispatch('toast', message: 'Estado del plan actualizado.', type: 'success');
    }

    public function deletePlan(int $planId): void
    {
        Plan::findOrFail($planId)->delete();
        $this->dispatch('toast', message: 'Plan eliminado.', type: 'warning');
    }

    public function render(): View
    {
        return view('livewire.admin.pricing-plans');
    }
}
