<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

final class ActivityLog extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterLogName = '';

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function activities(): LengthAwarePaginator
    {
        return Activity::query()
            ->with('causer')
            ->when(
                $this->search !== '',
                fn ($q) => $q->where('description', 'like', "%{$this->search}%")
            )
            ->when(
                $this->filterLogName !== '',
                fn ($q) => $q->where('log_name', $this->filterLogName)
            )
            ->latest()
            ->paginate(30);
    }

    #[Computed]
    public function logNames(): Collection
    {
        return Activity::query()
            ->select('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->filter();
    }

    // -------------------------------------------------------------------------
    // Lifecycle hooks (reset pagination on filter change)
    // -------------------------------------------------------------------------

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterLogName(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.activity-log');
    }
}
