<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;

final class AccountSettings extends Component
{
    public string $name  = '';
    public string $email = '';

    // Password change
    public string $currentPassword = '';
    public string $newPassword     = '';
    public string $confirmPassword = '';

    public bool $passwordSaved = false;
    public bool $profileSaved  = false;

    public function mount(): void
    {
        $user        = auth()->user();
        $this->name  = $user->name;
        $this->email = $user->email;
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$this->getAuthId()}"],
        ]);

        auth()->user()->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $this->profileSaved = true;
        $this->dispatch('toast', message: 'Perfil actualizado correctamente.', type: 'success');
    }

    public function savePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'string'],
            'newPassword'     => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = auth()->user();

        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'La contraseña actual no es correcta.');

            return;
        }

        $user->update(['password' => $this->newPassword]);

        $this->reset(['currentPassword', 'newPassword', 'confirmPassword']);
        $this->passwordSaved = true;
        $this->dispatch('toast', message: 'Contraseña actualizada.', type: 'success');
    }

    private function getAuthId(): int|string
    {
        return auth()->id() ?? 0;
    }

    public function render(): View
    {
        return view('livewire.admin.account-settings');
    }
}
