<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('menu_items', 'stock')) {
            DB::statement('ALTER TABLE menu_items ADD stock INT UNSIGNED NOT NULL DEFAULT 100 AFTER price');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'stock')) {
            DB::statement('ALTER TABLE menu_items DROP COLUMN stock');
        }
    }
};
