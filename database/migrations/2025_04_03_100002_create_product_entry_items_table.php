<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_entry_id')->constrained('product_entries')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity_received');
            $table->unsignedInteger('quantity_damaged')->default(0);
            $table->text('damage_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_entry_items');
    }
};
