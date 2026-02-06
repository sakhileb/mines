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
        Schema::table('machines', function (Blueprint $table) {
            $table->foreignId('excavator_id')->nullable()->after('integration_id')->constrained('machines')->onDelete('set null');
            $table->timestamp('assigned_to_excavator_at')->nullable()->after('excavator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropForeign(['excavator_id']);
            $table->dropColumn(['excavator_id', 'assigned_to_excavator_at']);
        });
    }
};
