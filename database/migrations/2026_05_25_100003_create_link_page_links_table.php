<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_page_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_page_id')->constrained()->cascadeOnDelete();
            $table->string('type');                 // instagram, tiktok, x, youtube, ..., custom
            $table->string('label')->nullable();    // visible
            $table->string('value');                // url, email, phone number, etc.
            $table->string('custom_icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();

            $table->index(['link_page_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_page_links');
    }
};
