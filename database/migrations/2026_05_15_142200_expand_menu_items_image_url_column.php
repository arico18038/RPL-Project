<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // image_url is already TEXT in the table-creation migration.
    }

    public function down(): void
    {
        // Keep this migration portable across SQLite and MySQL.
    }
};
