<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class StorageUsage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterType = '';

    // -------------------------------------------------------------------------
    // KPI Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function totalFiles(): int
    {
        return Upload::query()->count();
    }

    #[Computed]
    public function totalUsedBytes(): int
    {
        return (int) Upload::query()->sum('bytes');
    }

    #[Computed]
    public function totalUsedFormatted(): string
    {
        return $this->formatBytes($this->totalUsedBytes);
    }

    #[Computed]
    public function usersWithUploads(): int
    {
        return Upload::query()->distinct('user_id')->count('user_id');
    }

    // -------------------------------------------------------------------------
    // Top Users Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function topUsers(): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email', 'plan_id', 'extra_storage_mb'])
            ->with('plan:id,name,storage_mb')
            ->withSum('uploads as used_bytes_sum', 'bytes')
            ->whereHas('uploads')
            ->orderByDesc('used_bytes_sum')
            ->limit(10)
            ->get();
    }

    // -------------------------------------------------------------------------
    // Uploads table Computed (paginated)
    // -------------------------------------------------------------------------

    #[Computed]
    public function uploads(): LengthAwarePaginator
    {
        return Upload::query()
            ->with('user:id,name,email')
            ->when($this->filterType !== '', fn ($q) => $q->where('type', $this->filterType))
            ->when($this->search !== '', fn ($q) => $q->whereHas('user', function ($q): void {
                $q->where('email', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(20);
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function deleteUpload(int $id): void
    {
        $upload = Upload::findOrFail($id);

        try {
            Storage::disk($upload->disk)->delete($upload->path);
        } catch (\Throwable) {
            // El archivo ya no existe en el disco; continuamos con el borrado del registro.
        }

        $upload->delete();

        $this->dispatch('toast', message: 'Archivo eliminado.', type: 'warning');
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2).' GB';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function render(): View
    {
        return view('livewire.admin.storage-usage');
    }
}
