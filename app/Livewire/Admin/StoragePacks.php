<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\StoragePack;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class StoragePacks extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $label = '';

    public string $sizeMb = '1';

    public string $price = '0';

    public string $stripePriceId = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function packs(): Collection
    {
        return StoragePack::query()
            ->orderBy('sort_order')
            ->orderBy('size_mb')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->reset(['editingId', 'label', 'sizeMb', 'price', 'stripePriceId', 'isActive', 'sortOrder']);
        $this->sizeMb = '1';
        $this->price = '0';
        $this->isActive = true;
        $this->showModal = true;
    }

    public function openEdit(int $packId): void
    {
        $pack = StoragePack::findOrFail($packId);

        $this->editingId = $pack->id;
        $this->label = $pack->label;
        $this->sizeMb = (string) $pack->size_mb;
        $this->price = (string) $pack->price;
        $this->stripePriceId = $pack->stripe_price_id ?? '';
        $this->isActive = $pack->is_active;
        $this->sortOrder = $pack->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'label' => ['required', 'string', 'max:100'],
            'sizeMb' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'stripePriceId' => ['nullable', 'string', 'max:255'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
        ]);

        $data = [
            'label' => $this->label,
            'size_mb' => (int) $this->sizeMb,
            'price' => (float) $this->price,
            'stripe_price_id' => $this->stripePriceId !== '' ? $this->stripePriceId : null,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId !== null) {
            StoragePack::findOrFail($this->editingId)->update($data);
            $message = 'Pack actualizado.';
        } else {
            StoragePack::create($data);
            $message = 'Pack creado.';
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleActive(int $packId): void
    {
        $pack = StoragePack::findOrFail($packId);
        $pack->update(['is_active' => ! $pack->is_active]);
        $this->dispatch('toast', message: 'Estado del pack actualizado.', type: 'success');
    }

    public function deletePack(int $packId): void
    {
        StoragePack::findOrFail($packId)->delete();
        $this->dispatch('toast', message: 'Pack eliminado.', type: 'warning');
    }

    public function render(): View
    {
        return view('livewire.admin.storage-packs');
    }
}
