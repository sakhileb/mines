<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engine_hour_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            // null ignition_off_at means the engine is currently running
            $table->timestamp('ignition_on_at');
            $table->timestamp('ignition_off_at')->nullable();
            // Pre-computed on close to avoid recalculating every read
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'ignition_on_at']);
            $table->index(['team_id', 'ignition_on_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engine_hour_sessions');
    }
};
