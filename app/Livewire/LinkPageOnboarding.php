<?php

namespace App\Livewire;

use App\Models\LinkPage;
use App\Models\LinkPageLink;
use App\Services\LinkCatalog;
use App\Services\LinkPageThemes;
use App\Support\CurrentUser;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.link-page-app')]
#[Title('Configura tu tarjeta de links · dtattoos')]
class LinkPageOnboarding extends Component
{
    public int $step = 1;

    // Paso 1: identidad
    public string $title = '';
    public string $slug  = '';
    public string $bio   = '';

    // Paso 2: redes seleccionadas (type => value)
    /** @var array<string,string> */
    public array $selected = [];

    // Paso 3: tema
    public string $theme = LinkPageThemes::DEFAULT_KEY;

    public function mount(): void
    {
        $user = CurrentUser::get();

        // Si ya completó el onboarding, lo mandamos al editor.
        $page = $user->linkPage;
        if ($page && $page->onboarding_completed) {
            $this->redirect(route('link-page.editor'));
            return;
        }

        $this->title = $user->name;
        $this->slug  = $page->slug ?? Str::slug($user->name) ?: 'mi-perfil';
        $this->bio   = $page->bio ?? '';
        $this->theme = $page->theme_key ?? LinkPageThemes::DEFAULT_KEY;
    }

    public function next(): void
    {
        $this->validateStep();
        $this->step = min(3, $this->step + 1);
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function toggleNetwork(string $type): void
    {
        if (array_key_exists($type, $this->selected)) {
            unset($this->selected[$type]);
        } else {
            $this->selected[$type] = '';
        }
    }

    public function pickTheme(string $key): void
    {
        if (in_array($key, LinkPageThemes::keys(), true)) {
            $this->theme = $key;
        }
    }

    public function finish(): void
    {
        $this->validateStep();

        $user = CurrentUser::get();

        $page = LinkPage::firstOrNew(['user_id' => $user->id]);
        $page->fill([
            'slug'                 => $this->slug,
            'title'                => $this->title,
            'bio'                  => $this->bio,
            'theme_key'            => $this->theme,
            'onboarding_completed' => true,
            'is_published'         => true,
        ]);
        $page->save();

        // Insertar/actualizar links seleccionados manteniendo el orden de selección.
        $position = $page->links()->max('position') ?? -1;
        foreach ($this->selected as $type => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            LinkPageLink::updateOrCreate(
                ['link_page_id' => $page->id, 'type' => $type],
                [
                    'value'     => $value,
                    'is_active' => true,
                    'position'  => ++$position,
                ]
            );
        }

        $this->redirect(route('link-page.editor'));
    }

    protected function validateStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'title' => ['required', 'string', 'max:120'],
                'slug'  => [
                    'required', 'string', 'min:3', 'max:60',
                    'regex:/^[a-z0-9](?:[a-z0-9\-_.]*[a-z0-9])?$/',
                    function ($attr, $value, $fail) {
                        $user = CurrentUser::get();
                        $exists = LinkPage::where('slug', $value)
                            ->where('user_id', '!=', $user->id)
                            ->exists();
                        if ($exists) {
                            $fail('Ese enlace ya está en uso. Prueba con otro.');
                        }
                    },
                ],
                'bio'   => ['nullable', 'string', 'max:280'],
            ], [
                'slug.regex' => 'Solo minúsculas, números, guiones, puntos y guiones bajos.',
            ]);
        }

        if ($this->step === 2) {
            $rules = [];
            foreach (array_keys($this->selected) as $type) {
                if (! in_array($type, LinkCatalog::types(), true)) {
                    continue;
                }
                $rules["selected.$type"] = LinkCatalog::rulesFor($type);
            }
            $this->validate($rules);
        }
    }

    public function render()
    {
        return view('livewire.link-page-onboarding', [
            'catalog' => LinkCatalog::all(),
            'themes'  => LinkPageThemes::all(),
        ]);
    }
}
