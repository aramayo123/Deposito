<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('alert_type', 32);
            $table->unsignedInteger('current_quantity');
            $table->unsignedInteger('minimum_stock');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
