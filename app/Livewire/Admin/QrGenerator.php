<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Tattoo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class QrGenerator extends Component
{
    public int    $quantity   = 1;
    public ?int   $assignToUserId = null;
    public string $prefix     = '';

    /** @var array<int, array{short_code: string, qr_svg: string, url: string}> */
    public array $generatedCodes = [];

    public bool $generating = false;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function generate(): void
    {
        $this->validate([
            'quantity'       => ['required', 'integer', 'min:1', 'max:50'],
            'assignToUserId' => ['nullable', 'integer', 'exists:users,id'],
            'prefix'         => ['nullable', 'string', 'max:4', 'regex:/^[a-zA-Z0-9]*$/'],
        ]);

        $userId = $this->assignToUserId ?? auth()->id();

        $this->generatedCodes = DB::transaction(function () use ($userId): array {
            $result = [];

            for ($i = 0; $i < $this->quantity; $i++) {
                $shortCode = Tattoo::generateUniqueShortCode(8);
                $name      = trim($this->prefix . ' QR-' . strtoupper(substr($shortCode, 0, 4)));

                $tattoo = Tattoo::create([
                    'user_id'    => $userId,
                    'short_code' => $shortCode,
                    'name'       => $name,
                    'is_active'  => true,
                ]);

                $url    = route('tattoo.show', $shortCode);
                $qrSvg  = QrCode::format('svg')
                    ->errorCorrection('H')
                    ->size(200)
                    ->generate($url);

                $result[] = [
                    'short_code' => $shortCode,
                    'qr_svg'     => $qrSvg,
                    'url'        => $url,
                    'tattoo_id'  => $tattoo->id,
                ];
            }

            return $result;
        });

        $this->dispatch('toast', message: "{$this->quantity} QR(s) generados correctamente.", type: 'success');
    }

    public function clearResults(): void
    {
        $this->generatedCodes = [];
    }

    public function render(): View
    {
        return view('livewire.admin.qr-generator');
    }
}
