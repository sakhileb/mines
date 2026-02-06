<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mine_areas', function (Blueprint $table) {
            // Shift configuration (JSON array of shift objects)
            $table->json('shifts')->nullable()->after('metadata');
            
            // Material types allocated to this mine area (JSON array)
            $table->json('material_types')->nullable()->after('shifts');
            
            // Mining targets (JSON object with daily/weekly/monthly/yearly)
            $table->json('mining_targets')->nullable()->after('material_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mine_areas', function (Blueprint $table) {
            $table->dropColumn(['shifts', 'material_types', 'mining_targets']);
        });
    }
};
