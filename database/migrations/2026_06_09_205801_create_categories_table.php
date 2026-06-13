<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        DB::table('categories')->insert([
            ['name' => 'Makanan', 'slug' => 'makanan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Minuman', 'slug' => 'minuman', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lainnya', 'slug' => 'lainnya', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }
};