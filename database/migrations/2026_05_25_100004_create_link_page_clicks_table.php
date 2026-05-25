<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_page_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_page_link_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['link_page_link_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_page_clicks');
    }
};
