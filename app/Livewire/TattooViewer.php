<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\TattooContent;
use Illuminate\View\View;
use Livewire\Component;

/**
 * TattooViewer – Livewire 3 component
 *
 * Rendered inside tattoo/show.blade.php when the active content is NOT a
 * direct link redirect (i.e. Gallery or Video).
 *
 * Provides reactive image carousel navigation for Gallery content;
 * Video content is embedded via an <iframe> with no additional Livewire state.
 */
final class TattooViewer extends Component
{
    public TattooContent $content;

    public string $shortCode;

    /** Zero-based index of the currently displayed gallery image. */
    public int $currentImageIndex = 0;

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function mount(TattooContent $content, string $shortCode): void
    {
        $this->content   = $content;
        $this->shortCode = $shortCode;
    }

    // -------------------------------------------------------------------------
    // Actions (Gallery carousel)
    // -------------------------------------------------------------------------

    public function nextImage(): void
    {
        $images = $this->content->payload['images'] ?? [];
        $max    = count($images) - 1;

        if ($this->currentImageIndex < $max) {
            $this->currentImageIndex++;
        }
    }

    public function previousImage(): void
    {
        if ($this->currentImageIndex > 0) {
            $this->currentImageIndex--;
        }
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render(): View
    {
        return view('livewire.tattoo-viewer');
    }
}
