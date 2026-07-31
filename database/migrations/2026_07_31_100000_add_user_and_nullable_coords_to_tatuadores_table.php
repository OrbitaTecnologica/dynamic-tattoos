<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tatuadores', function (Blueprint $table): void {
            if (! Schema::hasColumn('tatuadores', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        // El alta desde una solicitud aprobada llega sin coordenadas; el pin
        // solo puede activarse (salir en el mapa) cuando las tenga.
        Schema::table('tatuadores', function (Blueprint $table): void {
            $table->decimal('lat', 10, 7)->nullable()->change();
            $table->decimal('lng', 10, 7)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tatuadores', function (Blueprint $table): void {
            if (Schema::hasColumn('tatuadores', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
