<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Tattoo;
use App\Models\TattooContent;
use App\Services\UploadManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class TattooList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterType = '';

    public bool $showHistoryModal = false;

    public ?int $historyTattooId = null;

    public bool $showEditModal = false;

    public ?int $editTattooId = null;

    public string $editName = '';

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function tattoos(): LengthAwarePaginator
    {
        return Tattoo::query()
            ->with(['activeContent', 'user:id,name,email'])
            ->withCount('contents')
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($q): void {
                    $q->where('short_code', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType !== '', function ($q): void {
                $q->whereHas('activeContent', fn ($q) => $q->where('type', $this->filterType));
            })
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function historyItems(): Collection
    {
        if ($this->historyTattooId === null) {
            return collect();
        }

        return TattooContent::query()
            ->where('tattoo_id', $this->historyTattooId)
            ->orderByDesc('updated_at')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openHistory(int $tattooId): void
    {
        $this->historyTattooId = $tattooId;
        $this->showHistoryModal = true;
    }

    public function closeHistory(): void
    {
        $this->showHistoryModal = false;
        $this->historyTattooId = null;
    }

    public function openEditName(int $tattooId): void
    {
        $tattoo = Tattoo::findOrFail($tattooId);
        $this->editTattooId = $tattoo->id;
        $this->editName = $tattoo->name;
        $this->showEditModal = true;
    }

    public function saveName(): void
    {
        $this->validate(['editName' => ['required', 'string', 'max:150']]);

        Tattoo::findOrFail($this->editTattooId)->update(['name' => $this->editName]);

        $this->showEditModal = false;
        $this->dispatch('toast', message: 'Tatuaje actualizado.', type: 'success');
    }

    public function toggleActive(int $tattooId): void
    {
        $tattoo = Tattoo::findOrFail($tattooId);
        $tattoo->update(['is_active' => ! $tattoo->is_active]);
        Cache::forget("tattoo_content_{$tattoo->short_code}");

        $this->dispatch('toast', message: 'Estado actualizado.', type: 'success');
    }

    /** Activa una versión de contenido concreta (desactiva las demás del tatuaje). */
    public function activateContent(int $contentId): void
    {
        $content = TattooContent::findOrFail($contentId);

        // En transacción: entre ambos updates el tatuaje quedaría sin versión activa.
        DB::transaction(function () use ($content): void {
            TattooContent::query()
                ->where('tattoo_id', $content->tattoo_id)
                ->update(['is_active' => false]);
            $content->update(['is_active' => true]);
        });

        $tattoo = Tattoo::find($content->tattoo_id);
        if ($tattoo !== null) {
            Cache::forget("tattoo_content_{$tattoo->short_code}");
        }

        $this->dispatch('toast', message: 'Versión activada.', type: 'success');
    }

    public function delete(int $tattooId): void
    {
        $tattoo = Tattoo::findOrFail($tattooId);
        Cache::forget("tattoo_content_{$tattoo->short_code}");

        // El cascade de la FK solo limpia filas; los archivos y el ledger de
        // cuota (`uploads`, sin FK a tattoos) hay que purgarlos aparte.
        app(UploadManager::class)->purgeByType("tattoo:{$tattoo->id}");
        Storage::disk('public')->deleteDirectory("tattoos/{$tattoo->id}");

        $tattoo->delete();

        if ($this->historyTattooId === $tattooId) {
            $this->closeHistory();
        }

        if (($this->paginators['page'] ?? 1) > 1 && $this->tattoos->isEmpty()) {
            $this->resetPage();
            unset($this->tattoos);
        }

        $this->dispatch('toast', message: 'Tatuaje eliminado.', type: 'warning');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.tattoo-list');
    }
}
