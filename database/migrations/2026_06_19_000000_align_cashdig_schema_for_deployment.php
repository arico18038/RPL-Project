<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'description')) {
                $table->text('description')->nullable();
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'description')) {
                $table->string('description')->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'table_id')) {
                $table->unsignedBigInteger('table_id')->nullable()->index();
            }

            if (!Schema::hasColumn('orders', 'total_price')) {
                $table->unsignedInteger('total_price')->default(0);
            }

            if (!Schema::hasColumn('orders', 'note')) {
                $table->text('note')->nullable();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'subtotal')) {
                $table->unsignedInteger('subtotal')->default(0);
            }
        });

        if (Schema::hasColumn('orders', 'total')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
        }

        if (Schema::hasColumn('orders', 'total')) {
            DB::table('orders')
                ->where('total_price', 0)
                ->orderBy('id')
                ->chunkById(100, function ($orders) {
                    foreach ($orders as $order) {
                        DB::table('orders')
                            ->where('id', $order->id)
                            ->update(['total_price' => $order->total]);
                    }
                });
        }

        if (Schema::hasColumn('order_items', 'line_total')) {
            DB::table('order_items')
                ->where('subtotal', 0)
                ->orderBy('id')
                ->chunkById(100, function ($items) {
                    foreach ($items as $item) {
                        DB::table('order_items')
                            ->where('id', $item->id)
                            ->update(['subtotal' => $item->line_total]);
                    }
                });
        }
    }

    public function down(): void
    {
        // This compatibility migration intentionally keeps application data intact.
    }
};
