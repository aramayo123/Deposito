<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_exits', function (Blueprint $table) {
            $table->id();
            $table->string('exit_code')->unique();
            $table->date('exit_date');
            $table->time('exit_time');
            $table->string('technician_name')->nullable();
            $table->string('license_plate', 20)->nullable();
            $table->boolean('is_for_workshop')->default(false);
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('technician_name');
            $table->index('license_plate');
            $table->index('exit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_exits');
    }
};
