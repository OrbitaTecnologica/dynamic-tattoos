<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class UserList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterRole = '';

    public bool  $showModal   = false;
    public ?int  $editingId   = null;

    // Modal form fields
    public string  $userName   = '';
    public string  $userEmail  = '';
    public string  $userRole   = 'user';
    public ?int    $userPlanId = null;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->with('plan:id,name')
            ->withCount('tattoos')
            ->when($this->search !== '', fn ($q) => $q->where(function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterRole !== '', fn ($q) => $q->where('role', $this->filterRole))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function plans(): \Illuminate\Database\Eloquent\Collection
    {
        return Plan::query()->active()->ordered()->get(['id', 'name']);
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingId  = $user->id;
        $this->userName   = $user->name;
        $this->userEmail  = $user->email;
        $this->userRole   = $user->role;
        $this->userPlanId = $user->plan_id;
        $this->showModal  = true;
    }

    public function saveUser(): void
    {
        $this->validate([
            'userName'   => ['required', 'string', 'max:255'],
            'userEmail'  => ['required', 'email', 'max:255', "unique:users,email,{$this->editingId}"],
            'userRole'   => ['required', 'in:admin,artist,user'],
            'userPlanId' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        User::findOrFail($this->editingId)->update([
            'name'    => $this->userName,
            'email'   => $this->userEmail,
            'role'    => $this->userRole,
            'plan_id' => $this->userPlanId,
        ]);

        $this->showModal = false;
        $this->dispatch('toast', message: 'Usuario actualizado.', type: 'success');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.user-list');
    }
}
