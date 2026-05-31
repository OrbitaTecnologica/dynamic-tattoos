<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
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

    public bool $showModal = false;

    public ?int $editingId = null;

    // Modal form fields
    public string $userName = '';

    public string $userEmail = '';

    public string $userRole = 'user';

    public ?int $userPlanId = null;

    public string $userCity = '';

    public string $userCountry = '';

    public bool $userIsPremium = false;

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
    public function plans(): Collection
    {
        return Plan::query()->active()->ordered()->get(['id', 'name']);
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->userRole = $user->role;
        $this->userPlanId = $user->plan_id;
        $this->userCity = $user->city ?? '';
        $this->userCountry = $user->country ?? '';
        $this->userIsPremium = (bool) $user->is_premium;
        $this->showModal = true;
    }

    public function saveUser(): void
    {
        $this->validate([
            'userName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255', "unique:users,email,{$this->editingId}"],
            'userRole' => ['required', 'in:admin,artist,user'],
            'userPlanId' => ['nullable', 'integer', 'exists:plans,id'],
            'userCity' => ['nullable', 'string', 'max:120'],
            'userCountry' => ['nullable', 'string', 'max:120'],
            'userIsPremium' => ['boolean'],
        ]);

        User::findOrFail($this->editingId)->update([
            'name' => $this->userName,
            'email' => $this->userEmail,
            'role' => $this->userRole,
            'plan_id' => $this->userPlanId,
            'city' => $this->userCity !== '' ? $this->userCity : null,
            'country' => $this->userCountry !== '' ? $this->userCountry : null,
            'is_premium' => $this->userIsPremium,
        ]);

        $this->showModal = false;
        $this->dispatch('toast', message: 'Usuario actualizado.', type: 'success');
    }

    /** Limpia la configuración 2FA del usuario (p.ej. si perdió el dispositivo). */
    public function resetTwoFactor(int $userId): void
    {
        // Las columnas 2FA no son mass-assignable (se gestionan con forceFill).
        User::findOrFail($userId)->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->dispatch('toast', message: '2FA restablecido para el usuario.', type: 'success');
    }

    public function deleteUser(int $userId): void
    {
        if ($userId === auth()->id()) {
            $this->dispatch('toast', message: 'No puedes eliminar tu propia cuenta.', type: 'error');

            return;
        }

        User::findOrFail($userId)->delete();
        $this->dispatch('toast', message: 'Usuario eliminado.', type: 'warning');
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
