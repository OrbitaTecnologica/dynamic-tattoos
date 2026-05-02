<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Tattoo;
use App\Models\TattooContent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class TattooList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterType = '';

    public bool $showHistoryModal = false;
    public ?int  $historyTattooId = null;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function tattoos(): LengthAwarePaginator
    {
        return Tattoo::query()
            ->with(['activeContent', 'user:id,name,email'])
            ->withCount('contents')
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($q): void {
                    $q->where('short_code', 'like', "%{$this->search}%")
                      ->orWhere('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType !== '', function ($q): void {
                $q->whereHas('activeContent', fn ($q) => $q->where('type', $this->filterType));
            })
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function historyItems(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->historyTattooId === null) {
            return collect();
        }

        return TattooContent::query()
            ->where('tattoo_id', $this->historyTattooId)
            ->orderByDesc('updated_at')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openHistory(int $tattooId): void
    {
        $this->historyTattooId = $tattooId;
        $this->showHistoryModal = true;
    }

    public function closeHistory(): void
    {
        $this->showHistoryModal = false;
        $this->historyTattooId = null;
    }

    public function toggleActive(int $tattooId): void
    {
        $tattoo = Tattoo::findOrFail($tattooId);
        $tattoo->update(['is_active' => ! $tattoo->is_active]);
        Cache::forget("tattoo_content_{$tattoo->short_code}");

        $this->dispatch('toast', message: 'Estado actualizado.', type: 'success');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.tattoo-list');
    }
}
