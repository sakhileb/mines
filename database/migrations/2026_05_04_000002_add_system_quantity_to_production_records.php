<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            // System-recorded quantity (e.g. from machine telemetry / automated counting).
            // quantity_produced retains the operator-reported value.
            $table->decimal('system_quantity', 12, 2)
                ->nullable()
                ->after('quantity_produced')
                ->comment('System-recorded load quantity (telemetry/automated); operator-reported is quantity_produced');
        });
    }

    public function down(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->dropColumn('system_quantity');
        });
    }
};
