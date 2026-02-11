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
        Schema::create('operator_fatigue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->nullable()->constrained()->onDelete('set null');
            $table->date('shift_date');
            $table->string('shift_type'); // morning, afternoon, night
            $table->time('shift_start');
            $table->time('shift_end');
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->decimal('consecutive_days', 5, 1)->default(0);
            $table->integer('fatigue_score')->default(0); // 0-100, 100 being most fatigued
            $table->enum('alert_level', ['none', 'low', 'medium', 'high', 'critical'])->default('none');
            $table->decimal('break_time_minutes', 5, 2)->default(0);
            $table->integer('incidents_count')->default(0);
            $table->boolean('is_rested')->default(true);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();

            // Indexes
            $table->index(['team_id', 'shift_date']);
            $table->index(['user_id', 'shift_date']);
            $table->index('alert_level');
            $table->index('fatigue_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_fatigue');
    }
};
