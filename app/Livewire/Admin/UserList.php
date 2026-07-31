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

    /** Pestaña papelera (usuarios en soft-delete). */
    #[Url]
    public bool $showTrashed = false;

    /** Selección múltiple para acciones masivas (llega como strings desde Livewire). */
    public array $selected = [];

    public bool $selectPage = false;

    public bool $showModal = false;

    public ?int $editingId = null;

    // Modal form fields
    public string $userName = '';

    public string $userEmail = '';

    public string $userPassword = '';

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
            ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
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
    public function trashedCount(): int
    {
        return User::onlyTrashed()->count();
    }

    #[Computed]
    public function plans(): Collection
    {
        return Plan::query()->active()->ordered()->get(['id', 'name']);
    }

    // -------------------------------------------------------------------------
    // Tabs / selección
    // -------------------------------------------------------------------------

    public function showTab(string $tab): void
    {
        $this->showTrashed = $tab === 'trash';
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedSelectPage(bool $value): void
    {
        $this->selected = $value
            ? $this->users->getCollection()
                ->reject(fn (User $u): bool => $u->id === auth()->id())
                ->pluck('id')
                ->map(fn ($id): string => (string) $id)
                ->all()
            : [];
    }

    private function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    // -------------------------------------------------------------------------
    // Alta / edición
    // -------------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->reset([
            'editingId', 'userName', 'userEmail', 'userPassword', 'userRole',
            'userPlanId', 'userCity', 'userCountry', 'userIsPremium',
        ]);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->userPassword = '';
        $this->userRole = $user->role;
        $this->userPlanId = $user->plan_id;
        $this->userCity = $user->city ?? '';
        $this->userCountry = $user->country ?? '';
        $this->userIsPremium = (bool) $user->is_premium;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveUser(): void
    {
        $isCreate = $this->editingId === null;

        $this->validate([
            'userName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255', "unique:users,email,{$this->editingId}"],
            'userPassword' => [$isCreate ? 'required' : 'nullable', 'string', 'min:8', 'max:255'],
            'userRole' => ['required', 'in:admin,artist,user,ambassador'],
            'userPlanId' => ['nullable', 'integer', 'exists:plans,id'],
            'userCity' => ['nullable', 'string', 'max:120'],
            'userCountry' => ['nullable', 'string', 'max:120'],
            'userIsPremium' => ['boolean'],
        ]);

        $data = [
            'name' => $this->userName,
            'email' => $this->userEmail,
            'role' => $this->userRole,
            'plan_id' => $this->userPlanId,
            'city' => $this->userCity !== '' ? $this->userCity : null,
            'country' => $this->userCountry !== '' ? $this->userCountry : null,
            'is_premium' => $this->userIsPremium,
        ];

        if ($isCreate) {
            // El cast 'password' => 'hashed' del modelo se encarga del hash.
            $user = new User($data + ['password' => $this->userPassword]);
            // email_verified_at no es mass-assignable: el admin da de alta cuentas ya verificadas.
            $user->email_verified_at = now();
            $user->save();

            $this->dispatch('toast', message: 'Usuario creado.', type: 'success');
        } else {
            if ($this->userPassword !== '') {
                $data['password'] = $this->userPassword;
            }
            User::findOrFail($this->editingId)->update($data);

            $this->dispatch('toast', message: 'Usuario actualizado.', type: 'success');
        }

        $this->showModal = false;
        $this->userPassword = '';
        unset($this->users);
    }

    /** Limpia la configuración 2FA del usuario (p.ej. si perdió el dispositivo). */
    public function resetTwoFactor(int $userId): void
    {
        // Las columnas 2FA no son mass-assignable (se gestionan con forceFill).
        $user = User::findOrFail($userId);
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        // Acción sensible (con 2FA obligatorio, deja al usuario fuera del panel
        // hasta re-enrolarse): siempre con rastro de quién la ejecutó.
        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->event('2fa_reset')
            ->withProperties(['detail' => "2FA restablecido para {$user->email}"])
            ->log('Seguridad actualizada');

        $this->dispatch('toast', message: '2FA restablecido para el usuario.', type: 'success');
    }

    // -------------------------------------------------------------------------
    // Papelera (soft-delete)
    // -------------------------------------------------------------------------

    /** Envía un usuario a la papelera (borrado reversible). */
    public function deleteUser(int $userId): void
    {
        if ($userId === auth()->id()) {
            $this->dispatch('toast', message: 'No puedes eliminar tu propia cuenta.', type: 'error');

            return;
        }

        // ->delete() dispara el UserObserver (libera email, revoca tokens,
        // cancela Stripe, despublica contenido).
        User::findOrFail($userId)->delete();
        unset($this->users);

        $this->dispatch('toast', message: 'Usuario enviado a la papelera.', type: 'warning');
    }

    /** Envía en lote los usuarios seleccionados a la papelera. */
    public function deleteSelected(): void
    {
        $ids = array_values(array_diff(
            array_map('intval', $this->selected),
            [(int) auth()->id()],
        ));

        if ($ids === []) {
            $this->dispatch('toast', message: 'No hay usuarios seleccionados.', type: 'info');

            return;
        }

        // Se itera modelo a modelo para que el observer se dispare por cada uno
        // (un delete() masivo por query NO lanza eventos de modelo).
        $users = User::whereIn('id', $ids)->get();
        foreach ($users as $user) {
            $user->delete();
        }

        $this->clearSelection();
        unset($this->users);

        $count = $users->count();
        $this->dispatch('toast', message: "{$count} usuario(s) enviados a la papelera.", type: 'warning');
    }

    /** Restaura un usuario desde la papelera. */
    public function restore(int $userId): void
    {
        User::onlyTrashed()->findOrFail($userId)->restore();
        unset($this->users);

        $this->dispatch('toast', message: 'Usuario restaurado.', type: 'success');
    }

    /** Borrado DEFINITIVO (cascade real de tatuajes, QRs, link page, etc.). */
    public function forceDelete(int $userId): void
    {
        if ($userId === auth()->id()) {
            $this->dispatch('toast', message: 'No puedes eliminar tu propia cuenta.', type: 'error');

            return;
        }

        User::onlyTrashed()->findOrFail($userId)->forceDelete();
        unset($this->users);

        $this->dispatch('toast', message: 'Usuario eliminado definitivamente.', type: 'warning');
    }

    // -------------------------------------------------------------------------
    // Paginación
    // -------------------------------------------------------------------------

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function render(): View
    {
        return view('livewire.admin.user-list');
    }
}
