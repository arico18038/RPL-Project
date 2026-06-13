<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->enum('area', ['indoor', 'outdoor'])->default('indoor');
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamps();
        });

        DB::table('tables')->insert([
            ['number' => '1', 'area' => 'indoor', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['number' => '2', 'area' => 'indoor', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['number' => '3', 'area' => 'indoor', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['number' => '4', 'area' => 'outdoor', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['number' => '5', 'area' => 'outdoor', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};