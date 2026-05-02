<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

final class LandingMockup extends Component
{
    public string $scene = 'photo';

    public function cycleScene(): void
    {
        $this->scene = match ($this->scene) {
            'photo' => 'video',
            default => 'photo',
        };
    }

    public function render(): View
    {
        return view('livewire.landing-mockup');
    }
}
