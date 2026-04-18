<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->unsignedSmallInteger('cycle_time_minutes')->nullable()->after('capacity')->comment('Full haul cycle time in minutes');
            $table->unsignedSmallInteger('queue_time_minutes')->nullable()->after('cycle_time_minutes')->comment('Queue / wait time in minutes');
            $table->unsignedSmallInteger('loading_time_minutes')->nullable()->after('queue_time_minutes')->comment('Loading time in minutes');
        });
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn(['cycle_time_minutes', 'queue_time_minutes', 'loading_time_minutes']);
        });
    }
};
