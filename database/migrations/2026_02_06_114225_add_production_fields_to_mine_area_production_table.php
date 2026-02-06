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
        Schema::table('mine_area_production', function (Blueprint $table) {
            $table->integer('loads')->default(0)->after('volume_cubic_m');
            $table->integer('cycles')->default(0)->after('loads');
            $table->decimal('bcm', 15, 2)->nullable()->after('cycles'); // Bank Cubic Meters
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mine_area_production', function (Blueprint $table) {
            $table->dropColumn(['loads', 'cycles', 'bcm']);
        });
    }
};
