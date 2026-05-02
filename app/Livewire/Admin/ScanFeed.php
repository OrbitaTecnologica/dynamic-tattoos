<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\TattooScan;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ScanFeed extends Component
{
    public string $filterDevice = '';

    // -------------------------------------------------------------------------
    // Computed (refreshed every poll cycle)
    // -------------------------------------------------------------------------

    #[Computed]
    public function scans(): \Illuminate\Database\Eloquent\Collection
    {
        return TattooScan::query()
            ->with('tattoo:id,short_code,name')
            ->when($this->filterDevice !== '', fn ($q) => $q->where('device_type', $this->filterDevice))
            ->latest('scanned_at')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function deviceCounts(): array
    {
        return TattooScan::query()
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->pluck('total', 'device_type')
            ->toArray();
    }

    public function render(): View
    {
        return view('livewire.admin.scan-feed');
    }
}
