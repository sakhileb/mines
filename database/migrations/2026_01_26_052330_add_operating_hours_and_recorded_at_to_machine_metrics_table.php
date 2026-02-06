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
        Schema::table('machine_metrics', function (Blueprint $table) {
            $table->float('operating_hours')->nullable()->after('idle_hours');
            $table->timestamp('recorded_at')->nullable()->after('raw_data');
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_metrics', function (Blueprint $table) {
            $table->dropIndex(['recorded_at']);
            $table->dropColumn(['operating_hours', 'recorded_at']);
        });
    }
};
