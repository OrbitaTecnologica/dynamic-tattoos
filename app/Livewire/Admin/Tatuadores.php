<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Mail\TatuadorApprovedMail;
use App\Models\Tatuador;
use App\Models\TatuadorSolicitud;
use App\Models\User;
use App\Services\Referrals\ReferralService;
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
        return Tatuador::query()
            ->with('user:id,name,referral_code')
            ->orderBy('sort_order')
            ->orderBy('studio_name')
            ->get();
    }

    /** Base pública para construir el link de recomendación del tatuador. */
    public function shareBase(): string
    {
        return rtrim((string) (config('app.frontend_url') ?: 'https://www.dynamic-tattoos.com'), '/');
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

    /** Al pegar un enlace de Google Maps, extrae lat/lng automáticamente. */
    public function updatedMapsUrl(string $value): void
    {
        if ($this->lat !== '' || $this->lng !== '') {
            return;
        }

        // Formatos: .../@40.42,-3.70,15z · ?q=40.42,-3.70 · !3d40.42!4d-3.70
        if (preg_match('/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/', $value, $m)
            || preg_match('/[?&]q=(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/', $value, $m)
            || preg_match('/!3d(-?\d{1,2}\.\d+)!4d(-?\d{1,3}\.\d+)/', $value, $m)) {
            $this->lat = $m[1];
            $this->lng = $m[2];
        }
    }

    public function save(): void
    {
        // Sin coordenadas se puede guardar la ficha, pero no publicarla en el mapa.
        $this->validate([
            'studioName' => ['required', 'string', 'max:150'],
            'artistName' => ['nullable', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'mapsUrl' => ['nullable', 'string', 'max:1000'],
            'lat' => [$this->isActive ? 'required' : 'nullable', 'numeric'],
            'lng' => [$this->isActive ? 'required' : 'nullable', 'numeric'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
        ], [
            'lat.required' => 'Para publicar en el mapa necesita coordenadas (pega el enlace de Google Maps).',
            'lng.required' => 'Para publicar en el mapa necesita coordenadas (pega el enlace de Google Maps).',
        ]);

        $data = [
            'studio_name' => $this->studioName,
            'artist_name' => $this->artistName !== '' ? $this->artistName : null,
            'city' => $this->city,
            'address' => $this->address !== '' ? $this->address : null,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'email' => $this->email !== '' ? $this->email : null,
            'maps_url' => $this->mapsUrl !== '' ? $this->mapsUrl : null,
            'lat' => $this->lat !== '' ? (float) $this->lat : null,
            'lng' => $this->lng !== '' ? (float) $this->lng : null,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId !== null) {
            Tatuador::findOrFail($this->editingId)->update($data);
            $message = 'Tatuador actualizado.';
        } else {
            $solicitud = $this->fromSolicitudId !== null
                ? TatuadorSolicitud::find($this->fromSolicitudId)
                : null;

            Tatuador::create($data + ['user_id' => $solicitud?->user_id]);
            $message = $this->isActive
                ? 'Tatuador certificado y añadido al mapa.'
                : 'Tatuador guardado (pendiente de coordenadas para el mapa).';

            if ($solicitud !== null && $solicitud->status === TatuadorSolicitud::STATUS_PENDING) {
                $solicitud->forceFill([
                    'status' => TatuadorSolicitud::STATUS_APPROVED,
                    'approved_at' => now(),
                ])->save();
            }
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $t = Tatuador::findOrFail($id);

        if (! $t->is_active && ! $t->hasCoordinates()) {
            $this->dispatch('toast', message: 'No se puede publicar sin coordenadas. Edita la ficha y pega el enlace de Google Maps.', type: 'error');

            return;
        }

        $t->update(['is_active' => ! $t->is_active]);
        $this->dispatch('toast', message: 'Estado del tatuador actualizado.', type: 'success');
    }

    public function deleteTatuador(int $id): void
    {
        Tatuador::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Tatuador eliminado.', type: 'warning');
    }

    /** Genera el código de recomendación del usuario vinculado al tatuador. */
    public function generateLink(int $id, ReferralService $referrals): void
    {
        $tatuador = Tatuador::findOrFail($id);

        if ($tatuador->user === null) {
            $this->dispatch('toast', message: 'Este tatuador no tiene cuenta vinculada. Aprueba su solicitud o vincúlalo desde la edición.', type: 'error');

            return;
        }

        $referrals->ensureCode($tatuador->user);

        unset($this->tatuadores);
        $this->dispatch('toast', message: 'Link de recomendación generado.', type: 'success');
    }

    public function rejectSolicitud(int $id): void
    {
        TatuadorSolicitud::whereKey($id)->update(['status' => TatuadorSolicitud::STATUS_REJECTED]);
        $this->dispatch('toast', message: 'Solicitud rechazada.', type: 'warning');
    }

    /**
     * Aprueba la solicitud en un solo flujo: crea (o promueve) el User con
     * role=artist, crea la ficha del mapa (inactiva hasta tener coordenadas),
     * envía email con link de reset password y marca la solicitud aprobada.
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

        // Ficha del mapa en el mismo flujo: nace inactiva (sin coordenadas aún);
        // el admin la publica al editarla y pegar el enlace de Google Maps.
        Tatuador::firstOrCreate(
            ['user_id' => $user->id],
            [
                'studio_name' => (string) $solicitud->studio_name,
                'artist_name' => (string) $solicitud->name,
                'city' => $solicitud->city ?? 'Sin ciudad',
                'phone' => $solicitud->phone,
                'email' => $email,
                'is_active' => false,
            ],
        );

        $token = Password::broker()->createToken($user);
        Mail::to($user->email)->send(new TatuadorApprovedMail($user, $token));

        activity('account')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->event('tatuador_approved')
            ->withProperties(['solicitud_id' => $solicitud->id])
            ->log('Tatuador aprobado');

        $this->dispatch('toast', message: 'Aprobado: cuenta creada, email enviado y ficha del mapa lista para publicar.', type: 'success');
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
