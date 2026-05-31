<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\LinkPage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class LinkPages extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterPublished = '';

    public bool $showLinksModal = false;

    public ?int $detailPageId = null;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function pages(): LengthAwarePaginator
    {
        return LinkPage::query()
            ->with('user:id,name,email')
            ->withCount('links')
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($q): void {
                    $q->where('slug', 'like', "%{$this->search}%")
                        ->orWhere('title', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn ($q) => $q->where('email', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->filterPublished !== '', fn ($q) => $q->where('is_published', (bool) $this->filterPublished))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function pageLinks(): Collection
    {
        if ($this->detailPageId === null) {
            return collect();
        }

        return LinkPage::findOrFail($this->detailPageId)
            ->links()
            ->orderBy('position')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function togglePublished(int $id): void
    {
        $page = LinkPage::findOrFail($id);
        $page->update(['is_published' => ! $page->is_published]);

        $estado = $page->is_published ? 'publicada' : 'despublicada';
        $this->dispatch('toast', message: "Página {$estado}.", type: 'success');
    }

    public function openLinks(int $id): void
    {
        $this->detailPageId = $id;
        $this->showLinksModal = true;
    }

    public function closeLinks(): void
    {
        $this->showLinksModal = false;
        $this->detailPageId = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPublished(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.link-pages');
    }
}
