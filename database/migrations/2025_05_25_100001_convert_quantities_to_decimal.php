<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('available_quantity', 10, 3)->default(0)->change();
            $table->decimal('damaged_quantity', 10, 3)->default(0)->change();
            $table->decimal('minimum_stock', 10, 3)->default(5)->change();
        });

        Schema::table('product_entry_items', function (Blueprint $table) {
            $table->decimal('quantity_received', 10, 3)->change();
            $table->decimal('quantity_damaged', 10, 3)->default(0)->change();
        });

        Schema::table('product_exit_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->change();
        });

        Schema::table('product_histories', function (Blueprint $table) {
            $table->decimal('quantity_change', 10, 3)->nullable()->change();
            $table->decimal('quantity_before', 10, 3)->nullable()->change();
            $table->decimal('quantity_after', 10, 3)->nullable()->change();
        });

        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->decimal('current_quantity', 10, 3)->change();
            $table->decimal('minimum_stock', 10, 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('available_quantity')->default(0)->change();
            $table->unsignedInteger('damaged_quantity')->default(0)->change();
            $table->unsignedInteger('minimum_stock')->default(5)->change();
        });

        Schema::table('product_entry_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity_received')->change();
            $table->unsignedInteger('quantity_damaged')->default(0)->change();
        });

        Schema::table('product_exit_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->change();
        });

        Schema::table('product_histories', function (Blueprint $table) {
            $table->integer('quantity_change')->nullable()->change();
            $table->integer('quantity_before')->nullable()->change();
            $table->integer('quantity_after')->nullable()->change();
        });

        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->unsignedInteger('current_quantity')->change();
            $table->unsignedInteger('minimum_stock')->change();
        });
    }
};
