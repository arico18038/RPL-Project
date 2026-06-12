<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'unit')) {
                $table->string('unit', 30)->default('Pcs')->after('stock');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->unsignedInteger('subtotal')->default(0)->after('status');
            }

            if (!Schema::hasColumn('orders', 'discount_type')) {
                $table->string('discount_type', 20)->default('persen')->after('subtotal');
            }

            if (!Schema::hasColumn('orders', 'discount_value')) {
                $table->unsignedInteger('discount_value')->default(0)->after('discount_type');
            }

            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->unsignedInteger('discount_amount')->default(0)->after('discount_value');
            }

            if (!Schema::hasColumn('orders', 'tax')) {
                $table->unsignedInteger('tax')->default(0)->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['tax', 'discount_amount', 'discount_value', 'discount_type', 'subtotal'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('menu_items', 'unit')) {
                $table->dropColumn('unit');
            }
        });
    }
};
