<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tattoo_contents', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tattoo_id')->constrained()->cascadeOnDelete();

            // Allowed content types; extending is a simple enum alteration
            $table->enum('type', ['link', 'gallery', 'video']);

            /**
             * JSON payload – structure varies by type:
             *
             * link:    { "url": string, "label": string|null, "open_in_new_tab": bool }
             * gallery: { "title": string|null, "images": string[] }   // paths on Storage::disk('public')
             * video:   { "url": string, "platform": "youtube"|"vimeo"|"tiktok",
             *            "autoplay": bool, "title": string|null }
             *
             * MySQL 8.4 stores this as a proper JSON column (native JSON type, not LONGTEXT).
             */
            $table->json('payload');

            // Only one content row per tattoo should be active at a time.
            // Enforced at the application layer (transaction in ManageTattoo::save).
            $table->boolean('is_active')->default(false);

            // Allows future multi-content ordering without schema changes
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['tattoo_id', 'is_active'], 'tattoo_contents_tattoo_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tattoo_contents');
    }
};
