<?php

namespace App\Livewire;

use App\Models\LinkPage;
use App\Models\LinkPageLink;
use App\Services\LinkCatalog;
use App\Services\LinkPageThemes;
use App\Support\CurrentUser;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.link-page-app')]
#[Title('Tu tarjeta de links · dtattoos')]
class LinkPageEditor extends Component
{
    use WithFileUploads;

    public LinkPage $page;

    // Identidad
    public string $title = '';
    public string $slug  = '';
    public string $bio   = '';

    // Tema
    public string $themeKey = LinkPageThemes::DEFAULT_KEY;
    /** @var array<string,string> */
    public array $themeOverrides = [];

    // Subidas
    public $avatarUpload;
    public $coverUpload;

    // Añadir link nuevo
    public string $newType  = 'instagram';
    public string $newLabel = '';
    public string $newValue = '';

    // Estado UI
    public string $tab = 'content'; // content | design | analytics

    public function mount(): void
    {
        $user = CurrentUser::get();

        $page = $user->linkPage;
        if (! $page || ! $page->onboarding_completed) {
            $this->redirect(route('link-page.onboarding'));
            return;
        }

        $this->page           = $page->load('links');
        $this->title          = (string) $page->title;
        $this->slug           = (string) $page->slug;
        $this->bio            = (string) ($page->bio ?? '');
        $this->themeKey       = $page->theme_key ?: LinkPageThemes::DEFAULT_KEY;
        $this->themeOverrides = $page->theme_overrides ?? [];
    }

    /* ---------- Identidad / persistencia ---------- */

    public function saveIdentity(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug'  => [
                'required', 'string', 'min:3', 'max:60',
                'regex:/^[a-z0-9](?:[a-z0-9\-_.]*[a-z0-9])?$/',
                function ($attr, $value, $fail) {
                    $exists = LinkPage::where('slug', $value)
                        ->where('id', '!=', $this->page->id)
                        ->exists();
                    if ($exists) {
                        $fail('Ese enlace ya está en uso.');
                    }
                },
            ],
            'bio'   => ['nullable', 'string', 'max:280'],
        ], [
            'slug.regex' => 'Solo minúsculas, números, guiones, puntos y guiones bajos.',
        ]);

        $this->page->update([
            'title' => $this->title,
            'slug'  => $this->slug,
            'bio'   => $this->bio,
        ]);

        $this->dispatch('lp:saved', message: 'Identidad guardada');
    }

    public function uploadAvatar(): void
    {
        $this->validate(['avatarUpload' => ['image', 'max:2048']]);

        $path = $this->avatarUpload->store('link-pages/avatars', 'public');
        $this->page->update(['avatar_path' => $path]);
        $this->avatarUpload = null;
        $this->page->refresh();

        $this->dispatch('lp:saved', message: 'Avatar actualizado');
    }

    public function updatedAvatarUpload(): void
    {
        if ($this->avatarUpload) {
            $this->uploadAvatar();
        }
    }

    public function uploadCover(): void
    {
        $this->validate(['coverUpload' => ['image', 'max:5120']]);

        $path = $this->coverUpload->store('link-pages/covers', 'public');
        $this->page->update(['cover_path' => $path]);
        $this->coverUpload = null;
        $this->page->refresh();

        $this->dispatch('lp:saved', message: 'Cabecera actualizada');
    }

    public function updatedCoverUpload(): void
    {
        if ($this->coverUpload) {
            $this->uploadCover();
        }
    }

    public function removeAvatar(): void
    {
        $this->page->update(['avatar_path' => null]);
        $this->page->refresh();
    }

    public function removeCover(): void
    {
        $this->page->update(['cover_path' => null]);
        $this->page->refresh();
    }

    /* ---------- Tema ---------- */

    public function pickTheme(string $key): void
    {
        if (! in_array($key, LinkPageThemes::keys(), true)) {
            return;
        }
        $this->themeKey = $key;
        $this->page->update(['theme_key' => $key]);
        $this->page->refresh();
        $this->dispatch('lp:saved', message: 'Tema aplicado');
    }

    public function saveTheme(): void
    {
        $this->validate([
            'themeOverrides.text_color'    => ['nullable', 'string', 'max:120'],
            'themeOverrides.button_bg'     => ['nullable', 'string', 'max:255'],
            'themeOverrides.button_text'   => ['nullable', 'string', 'max:120'],
            'themeOverrides.button_radius' => ['nullable', 'string', 'max:20'],
            'themeOverrides.font'          => ['nullable', 'string', 'max:255'],
        ]);

        $this->page->update([
            'theme_key'       => $this->themeKey,
            'theme_overrides' => array_filter(
                $this->themeOverrides,
                fn ($v) => $v !== null && $v !== ''
            ),
        ]);
        $this->page->refresh();

        $this->dispatch('lp:saved', message: 'Diseño guardado');
    }

    public function resetThemeOverrides(): void
    {
        $this->themeOverrides = [];
        $this->page->update(['theme_overrides' => null]);
        $this->page->refresh();
    }

    /* ---------- Links ---------- */

    public function addLink(): void
    {
        $this->validate([
            'newType'  => ['required', 'string', 'in:' . implode(',', LinkCatalog::types())],
            'newValue' => LinkCatalog::rulesFor($this->newType),
            'newLabel' => ['nullable', 'string', 'max:80'],
        ]);

        $position = (int) $this->page->links()->max('position') + 1;

        LinkPageLink::create([
            'link_page_id' => $this->page->id,
            'type'         => $this->newType,
            'label'        => $this->newLabel ?: null,
            'value'        => $this->newValue,
            'is_active'    => true,
            'position'     => $position,
        ]);

        $this->reset(['newLabel', 'newValue']);
        $this->page->load('links');

        $this->dispatch('lp:saved', message: 'Enlace añadido');
    }

    public function updateLink(int $id, string $field, $value): void
    {
        $link = $this->page->links()->whereKey($id)->firstOrFail();

        $allowed = ['label', 'value', 'is_active'];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        if ($field === 'value') {
            $this->validate(
                [$field => LinkCatalog::rulesFor($link->type)],
                [],
                [$field => 'enlace']
            );
        }

        $link->update([$field => $value]);
        $this->page->load('links');
    }

    public function toggleLink(int $id): void
    {
        $link = $this->page->links()->whereKey($id)->firstOrFail();
        $link->update(['is_active' => ! $link->is_active]);
        $this->page->load('links');
    }

    public function deleteLink(int $id): void
    {
        $this->page->links()->whereKey($id)->delete();
        $this->page->load('links');
        $this->dispatch('lp:saved', message: 'Enlace eliminado');
    }

    /** @param array<int,int> $ids  Lista ordenada de IDs (front -> back). */
    public function reorderLinks(array $ids): void
    {
        foreach ($ids as $position => $id) {
            LinkPageLink::where('id', $id)
                ->where('link_page_id', $this->page->id)
                ->update(['position' => $position]);
        }
        $this->page->load('links');
    }

    /* ---------- Vista ---------- */

    #[Computed]
    public function theme(): array
    {
        return LinkPageThemes::resolve($this->themeKey, $this->themeOverrides);
    }

    public function render()
    {
        return view('livewire.link-page-editor', [
            'catalog'       => LinkCatalog::all(),
            'themes'        => LinkPageThemes::all(),
            'fonts'         => LinkPageThemes::fonts(),
            'resolvedTheme' => $this->theme,
            'totalClicks'   => $this->page->links()->sum('clicks_count'),
        ]);
    }
}
