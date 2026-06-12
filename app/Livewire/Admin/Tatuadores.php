<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Mail\TatuadorApprovedMail;
use App\Models\Tatuador;
use App\Models\TatuadorSolicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Tatuadores extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $fromSolicitudId = null;

    // Form fields
    public string $studioName = '';

    public string $artistName = '';

    public string $city = '';

    public string $address = '';

    public string $phone = '';

    public string $email = '';

    public string $mapsUrl = '';

    public string $lat = '';

    public string $lng = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    /** @return Collection<int, Tatuador> */
    #[Computed]
    public function tatuadores(): Collection
    {
        return Tatuador::query()->orderBy('sort_order')->orderBy('studio_name')->get();
    }

    /** @return Collection<int, TatuadorSolicitud> */
    #[Computed]
    public function solicitudes(): Collection
    {
        return TatuadorSolicitud::query()
            ->where('status', TatuadorSolicitud::STATUS_PENDING)
            ->latest()
            ->get();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $t = Tatuador::findOrFail($id);

        $this->editingId = $t->id;
        $this->fromSolicitudId = null;
        $this->studioName = $t->studio_name;
        $this->artistName = $t->artist_name ?? '';
        $this->city = $t->city;
        $this->address = $t->address ?? '';
        $this->phone = $t->phone ?? '';
        $this->email = $t->email ?? '';
        $this->mapsUrl = $t->maps_url ?? '';
        $this->lat = (string) $t->lat;
        $this->lng = (string) $t->lng;
        $this->isActive = $t->is_active;
        $this->sortOrder = $t->sort_order;
        $this->showModal = true;
    }

    /** Pre-rellena el formulario con los datos de una solicitud para certificarla. */
    public function certifySolicitud(int $id): void
    {
        $sol = TatuadorSolicitud::findOrFail($id);

        $this->resetForm();
        $this->fromSolicitudId = $sol->id;
        $this->studioName = $sol->studio_name;
        $this->artistName = $sol->name;
        $this->city = $sol->city ?? '';
        $this->phone = $sol->phone ?? '';
        $this->email = $sol->email;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'studioName' => ['required', 'string', 'max:150'],
            'artistName' => ['nullable', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'mapsUrl' => ['nullable', 'string', 'max:1000'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
        ]);

        $data = [
            'studio_name' => $this->studioName,
            'artist_name' => $this->artistName !== '' ? $this->artistName : null,
            'city' => $this->city,
            'address' => $this->address !== '' ? $this->address : null,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'email' => $this->email !== '' ? $this->email : null,
            'maps_url' => $this->mapsUrl !== '' ? $this->mapsUrl : null,
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId !== null) {
            Tatuador::findOrFail($this->editingId)->update($data);
            $message = 'Tatuador actualizado.';
        } else {
            Tatuador::create($data);
            $message = 'Tatuador certificado y añadido al mapa.';

            if ($this->fromSolicitudId !== null) {
                TatuadorSolicitud::whereKey($this->fromSolicitudId)
                    ->update(['status' => TatuadorSolicitud::STATUS_APPROVED]);
            }
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $t = Tatuador::findOrFail($id);
        $t->update(['is_active' => ! $t->is_active]);
        $this->dispatch('toast', message: 'Estado del tatuador actualizado.', type: 'success');
    }

    public function deleteTatuador(int $id): void
    {
        Tatuador::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Tatuador eliminado.', type: 'warning');
    }

    public function rejectSolicitud(int $id): void
    {
        TatuadorSolicitud::whereKey($id)->update(['status' => TatuadorSolicitud::STATUS_REJECTED]);
        $this->dispatch('toast', message: 'Solicitud rechazada.', type: 'warning');
    }

    /**
     * Aprueba la solicitud: crea (o promueve) el User con role=artist, envía
     * email con link de reset password y marca la solicitud aprobada. No añade
     * automáticamente la entrada al mapa — eso lo hace `certifySolicitud`.
     */
    public function approveSolicitud(int $id): void
    {
        $solicitud = TatuadorSolicitud::findOrFail($id);

        if ($solicitud->status !== TatuadorSolicitud::STATUS_PENDING) {
            $this->dispatch('toast', message: 'La solicitud ya fue procesada.', type: 'warning');

            return;
        }

        $email = mb_strtolower((string) $solicitud->email);
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => (string) $solicitud->name,
                'email' => $email,
                'password' => Hash::make(Str::random(64)),
                'role' => 'artist',
            ]);
        } else {
            $user->forceFill(['role' => 'artist'])->save();
        }

        $solicitud->forceFill([
            'status' => TatuadorSolicitud::STATUS_APPROVED,
            'approved_at' => now(),
            'user_id' => $user->id,
        ])->save();

        $token = Password::broker()->createToken($user);
        Mail::to($user->email)->send(new TatuadorApprovedMail($user, $token));

        activity('account')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->event('tatuador_approved')
            ->withProperties(['solicitud_id' => $solicitud->id])
            ->log('Tatuador aprobado');

        $this->dispatch('toast', message: 'Solicitud aprobada y email enviado.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.admin.tatuadores');
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'fromSolicitudId', 'studioName', 'artistName', 'city',
            'address', 'phone', 'email', 'mapsUrl', 'lat', 'lng', 'isActive', 'sortOrder',
        ]);
    }
}
