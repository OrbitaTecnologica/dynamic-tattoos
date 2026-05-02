<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Tattoo;
use App\Models\TattooContent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * ManageTattoo – Livewire 3 component
 *
 * Allows an authenticated owner to:
 *   1. Switch between content types (Link / Gallery / Video) via a tab selector.
 *   2. Edit the JSON payload reactively (wire:model.live) without page reloads.
 *   3. See a "Live Mobile Preview" that mirrors the editor state in real-time.
 *   4. Save changes (persisted as a TattooContent row) and bust the cache key
 *      used by TattooRedirectController on every QR scan.
 */
final class ManageTattoo extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    // -------------------------------------------------------------------------
    // Public state (Livewire serialises & restores these on every request)
    // -------------------------------------------------------------------------

    public Tattoo $tattoo;

    public string $activeTab = TattooContent::TYPE_LINK;

    /** ID of the TattooContent row currently being edited (null = new). */
    public ?int $activeContentId = null;

    // -- Link payload fields --------------------------------------------------
    public string $linkUrl         = '';
    public string $linkLabel       = '';
    public bool   $linkOpenInNewTab = true;

    // -- Gallery payload fields -----------------------------------------------
    public string $galleryTitle  = '';
    /** @var string[] Stored paths on Storage::disk('public') */
    public array $galleryImages = [];
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $newImages = [];

    // -- Video payload fields -------------------------------------------------
    public string $videoUrl      = '';
    public string $videoPlatform = 'youtube';
    public bool   $videoAutoplay = false;
    public string $videoTitle    = '';

    // -- UI state -------------------------------------------------------------
    public bool $showPreview = false;
    public bool $saved       = false;

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function mount(Tattoo $tattoo): void
    {
        $this->authorize('update', $tattoo);

        $this->tattoo = $tattoo;

        $activeContent = $tattoo->activeContent;

        if ($activeContent !== null) {
            $this->activeContentId = $activeContent->id;
            $this->activeTab       = $activeContent->type;
            $this->hydratePayload($activeContent);
        }
    }

    // -------------------------------------------------------------------------
    // Computed properties (Livewire 3 – cached per request lifecycle)
    // -------------------------------------------------------------------------

    /**
     * Returns all content rows for the current tattoo.
     *
     * @return Collection<int, TattooContent>
     */
    #[Computed]
    public function contents(): Collection
    {
        return $this->tattoo->contents()->get();
    }

    /**
     * Assembles the payload array from the individual reactive properties so
     * the Blade partial can render the live preview without saving first.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function previewPayload(): array
    {
        return match ($this->activeTab) {
            TattooContent::TYPE_LINK => [
                'url'             => $this->linkUrl,
                'label'           => $this->linkLabel,
                'open_in_new_tab' => $this->linkOpenInNewTab,
            ],
            TattooContent::TYPE_GALLERY => [
                'title'  => $this->galleryTitle,
                'images' => $this->galleryImages,
            ],
            TattooContent::TYPE_VIDEO => [
                'url'      => $this->videoUrl,
                'platform' => $this->videoPlatform,
                'autoplay' => $this->videoAutoplay,
                'title'    => $this->videoTitle,
            ],
            default => [],
        };
    }

    // -------------------------------------------------------------------------
    // Lifecycle hooks
    // -------------------------------------------------------------------------

    /** Reset validation errors whenever the user switches content type. */
    public function updatedActiveTab(): void
    {
        $this->saved = false;
        $this->resetValidation();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    /**
     * Persists the current payload to the database inside a transaction and
     * invalidates the cache key consumed by TattooRedirectController.
     */
    public function save(): void
    {
        $this->validate($this->validationRules());

        $payload = $this->previewPayload;
        $type    = $this->activeTab;

        DB::transaction(function () use ($payload, $type): void {
            // Deactivate every content row that belongs to this tattoo
            $this->tattoo->contents()->update(['is_active' => false]);

            if ($this->activeContentId !== null) {
                $content = TattooContent::query()->findOrFail($this->activeContentId);

                // Guard: ensure the row belongs to the current tattoo
                abort_if($content->tattoo_id !== $this->tattoo->id, 403);

                $content->update([
                    'type'      => $type,
                    'payload'   => $payload,
                    'is_active' => true,
                ]);
            } else {
                $content = $this->tattoo->contents()->create([
                    'type'      => $type,
                    'payload'   => $payload,
                    'is_active' => true,
                    'order'     => 0,
                ]);

                $this->activeContentId = $content->id;
            }
        });

        // Bust the cache so the next QR scan sees fresh content immediately
        Cache::forget("tattoo_content_{$this->tattoo->short_code}");

        $this->saved = true;
        $this->dispatch('content-saved');
    }

    /**
     * Load an existing content row into the editor for modification.
     */
    public function setActiveContent(int $contentId): void
    {
        $content = $this->tattoo->contents()->findOrFail($contentId);

        $this->activeContentId = $content->id;
        $this->activeTab       = $content->type;
        $this->saved           = false;

        $this->hydratePayload($content);
    }

    /** Reset the editor to a blank slate for creating a new content row. */
    public function addNewContent(): void
    {
        $this->activeContentId = null;
        $this->resetPayloadFields();
        $this->saved = false;
    }

    /**
     * Validate and store the uploaded images in Storage::disk('public'),
     * appending their paths to $galleryImages.
     */
    public function uploadGalleryImages(): void
    {
        $this->validate([
            'newImages'   => ['required', 'array', 'min:1'],
            'newImages.*' => ['image', 'max:2048', 'mimes:jpeg,png,webp,gif'],
        ]);

        foreach ($this->newImages as $image) {
            $path = $image->store(
                'gallery/' . $this->tattoo->short_code,
                'public'   // maps to storage/app/public – symlinked by php artisan storage:link
            );

            if ($path !== false) {
                $this->galleryImages[] = $path;
            }
        }

        $this->newImages = [];
        $this->dispatch('images-uploaded');
    }

    /**
     * Remove an image from the gallery by its array index and delete the file
     * from the public disk.
     */
    public function removeGalleryImage(int $index): void
    {
        if (! isset($this->galleryImages[$index])) {
            return;
        }

        Storage::disk('public')->delete($this->galleryImages[$index]);
        array_splice($this->galleryImages, $index, 1);
    }

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function hydratePayload(TattooContent $content): void
    {
        $payload = $content->payload ?? [];

        match ($content->type) {
            TattooContent::TYPE_LINK    => $this->hydrateLinkPayload($payload),
            TattooContent::TYPE_GALLERY => $this->hydrateGalleryPayload($payload),
            TattooContent::TYPE_VIDEO   => $this->hydrateVideoPayload($payload),
            default                     => null,
        };
    }

    /** @param array<string, mixed> $payload */
    private function hydrateLinkPayload(array $payload): void
    {
        $this->linkUrl          = (string) ($payload['url']             ?? '');
        $this->linkLabel        = (string) ($payload['label']           ?? '');
        $this->linkOpenInNewTab = (bool)   ($payload['open_in_new_tab'] ?? true);
    }

    /** @param array<string, mixed> $payload */
    private function hydrateGalleryPayload(array $payload): void
    {
        $this->galleryTitle  = (string) ($payload['title']  ?? '');
        $this->galleryImages = (array)  ($payload['images'] ?? []);
    }

    /** @param array<string, mixed> $payload */
    private function hydrateVideoPayload(array $payload): void
    {
        $this->videoUrl      = (string) ($payload['url']      ?? '');
        $this->videoPlatform = (string) ($payload['platform'] ?? 'youtube');
        $this->videoAutoplay = (bool)   ($payload['autoplay'] ?? false);
        $this->videoTitle    = (string) ($payload['title']    ?? '');
    }

    private function resetPayloadFields(): void
    {
        $this->linkUrl          = '';
        $this->linkLabel        = '';
        $this->linkOpenInNewTab = true;

        $this->galleryTitle  = '';
        $this->galleryImages = [];
        $this->newImages     = [];

        $this->videoUrl      = '';
        $this->videoPlatform = 'youtube';
        $this->videoAutoplay = false;
        $this->videoTitle    = '';
    }

    /**
     * Validation rules differ per active content type.
     *
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        return match ($this->activeTab) {
            TattooContent::TYPE_LINK => [
                'linkUrl'   => ['required', 'url', 'max:2048'],
                'linkLabel' => ['nullable', 'string', 'max:255'],
            ],
            TattooContent::TYPE_GALLERY => [
                'galleryTitle'  => ['nullable', 'string', 'max:255'],
                'galleryImages' => ['required', 'array', 'min:1'],
            ],
            TattooContent::TYPE_VIDEO => [
                'videoUrl'      => ['required', 'url', 'max:2048'],
                'videoPlatform' => ['required', Rule::in(['youtube', 'vimeo', 'tiktok'])],
                'videoTitle'    => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render(): View
    {
        return view('livewire.manage-tattoo');
    }
}
