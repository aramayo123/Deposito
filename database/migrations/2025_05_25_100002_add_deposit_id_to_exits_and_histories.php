<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_exits', function (Blueprint $table) {
            $table->foreignId('deposit_id')->nullable()->after('technician_name')->constrained('deposits')->nullOnDelete();
            $table->dropIndex(['license_plate']);
            $table->dropColumn('license_plate');
        });

        Schema::table('product_histories', function (Blueprint $table) {
            $table->foreignId('deposit_id')->nullable()->after('license_plate')->constrained('deposits')->nullOnDelete();
            $table->index('deposit_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_exits', function (Blueprint $table) {
            if (Schema::hasColumn('product_exits', 'deposit_id')) {
                $table->dropForeign(['deposit_id']);
                $table->dropColumn('deposit_id');
            }
            $table->string('license_plate', 20)->nullable()->after('technician_name');
            $table->index('license_plate');
        });

        Schema::table('product_histories', function (Blueprint $table) {
            if (Schema::hasColumn('product_histories', 'deposit_id')) {
                $table->dropForeign(['deposit_id']);
                $table->dropIndex(['deposit_id']);
                $table->dropColumn('deposit_id');
            }
        });
    }
};
