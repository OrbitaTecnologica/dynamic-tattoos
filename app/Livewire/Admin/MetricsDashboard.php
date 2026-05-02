<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Tattoo;
use App\Models\TattooContent;
use App\Models\TattooScan;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class MetricsDashboard extends Component
{
    // -------------------------------------------------------------------------
    // Computed properties (refreshed on every Livewire poll)
    // -------------------------------------------------------------------------

    #[Computed]
    public function totalActiveQrs(): int
    {
        return Tattoo::query()->where('is_active', true)->count();
    }

    #[Computed]
    public function mutationsLast24h(): int
    {
        return TattooContent::query()
            ->where('updated_at', '>=', now()->subHours(24))
            ->count();
    }

    #[Computed]
    public function totalScans(): int
    {
        return TattooScan::query()->count();
    }

    #[Computed]
    public function scansLast24h(): int
    {
        return TattooScan::query()->recent(24)->count();
    }

    #[Computed]
    public function totalUsers(): int
    {
        return User::query()->count();
    }

    #[Computed]
    public function scansByDevice(): array
    {
        return TattooScan::query()
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->pluck('total', 'device_type')
            ->toArray();
    }

    #[Computed]
    public function recentActivity(): \Illuminate\Database\Eloquent\Collection
    {
        return TattooScan::query()
            ->with('tattoo:id,short_code,name')
            ->latest('scanned_at')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.metrics-dashboard');
    }
}
