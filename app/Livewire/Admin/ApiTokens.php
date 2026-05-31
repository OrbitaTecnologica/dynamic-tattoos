<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ApiTokens extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function tokens(): LengthAwarePaginator
    {
        return PersonalAccessToken::query()
            ->with('tokenable')
            ->when($this->search !== '', fn ($q) => $q->where(function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('ip_address', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(20);
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function revoke(int $id): void
    {
        PersonalAccessToken::findOrFail($id)->delete();

        $this->dispatch('toast', message: 'Token revocado. El usuario tendrá que volver a iniciar sesión.', type: 'warning');
    }

    public function revokeAllExpired(): void
    {
        $count = PersonalAccessToken::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->dispatch('toast', message: "Se eliminaron {$count} token(s) expirado(s).", type: 'success');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.api-tokens');
    }
}
