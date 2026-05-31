<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class Companies extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterProfessional = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    // Modal form fields
    public string $name = '';

    public string $category = '';

    public string $email = '';

    public string $website = '';

    public string $address = '';

    public string $taxId = '';

    public string $vat = '';

    public bool $isProfessional = false;

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    #[Computed]
    public function companies(): LengthAwarePaginator
    {
        return Company::query()
            ->with('user:id,name,email')
            ->when($this->search !== '', fn ($q) => $q->where(function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('tax_id', 'like', "%{$this->search}%")
                    ->orWhere('vat', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterProfessional !== '', fn ($q) => $q->where('is_professional', (bool) $this->filterProfessional))
            ->latest()
            ->paginate(20);
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function openEdit(int $id): void
    {
        $company = Company::findOrFail($id);

        $this->editingId = $company->id;
        $this->name = $company->name ?? '';
        $this->category = $company->category ?? '';
        $this->email = $company->email ?? '';
        $this->website = $company->website ?? '';
        $this->address = $company->address ?? '';
        $this->taxId = $company->tax_id ?? '';
        $this->vat = $company->vat ?? '';
        $this->isProfessional = (bool) $company->is_professional;
        $this->showModal = true;
    }

    public function saveCompany(): void
    {
        $this->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'taxId' => ['nullable', 'string', 'max:50'],
            'vat' => ['nullable', 'string', 'max:50'],
            'isProfessional' => ['boolean'],
        ]);

        Company::findOrFail($this->editingId)->update([
            'name' => $this->name ?: null,
            'category' => $this->category ?: null,
            'email' => $this->email ?: null,
            'website' => $this->website ?: null,
            'address' => $this->address ?: null,
            'tax_id' => $this->taxId ?: null,
            'vat' => $this->vat ?: null,
            'is_professional' => $this->isProfessional,
        ]);

        $this->showModal = false;
        $this->dispatch('toast', message: 'Empresa actualizada.', type: 'success');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterProfessional(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.companies');
    }
}
