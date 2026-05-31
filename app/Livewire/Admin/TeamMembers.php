<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\TeamMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class TeamMembers extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterRole = '';

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function members(): LengthAwarePaginator
    {
        return TeamMember::query()
            ->with(['owner:id,name,email', 'member:id,name,email'])
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($q): void {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhereHas('owner', fn ($q) => $q->where('email', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterRole !== '', fn ($q) => $q->where('role', $this->filterRole))
            ->latest()
            ->paginate(20);
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function revoke(int $id): void
    {
        $member = TeamMember::findOrFail($id);
        $member->delete();

        $this->dispatch('toast', message: 'Acceso revocado correctamente.', type: 'warning');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.team-members');
    }
}
