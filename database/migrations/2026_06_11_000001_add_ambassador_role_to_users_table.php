<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'artist', 'user', 'ambassador') NOT NULL DEFAULT 'user'");
        }
        // SQLite/Postgres: la columna ya es TEXT/VARCHAR sin CHECK estricto; la
        // validación de valores la garantiza la capa de aplicación (FormRequest
        // + helpers `isAmbassador()`/`isArtist()`).
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET role = 'user' WHERE role = 'ambassador'");
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'artist', 'user') NOT NULL DEFAULT 'user'");
        }
    }
};
