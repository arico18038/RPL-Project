<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@cashdig.test'],
            [
                'name' => 'Admin CashDig',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // Keep the admin account and application data intact on rollback.
    }
};
