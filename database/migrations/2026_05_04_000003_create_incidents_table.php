<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mine_area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            // What kind of incident
            $table->string('category'); // safety, mechanical, delay, environmental, equipment_damage, near_miss, other
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('title');
            $table->text('description');

            // When it actually happened (vs created_at which is when it was logged)
            $table->timestamp('occurred_at');

            // Workflow
            $table->string('status')->default('open'); // open, investigating, resolved, closed
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['team_id', 'status']);
            $table->index(['team_id', 'category']);
            $table->index(['team_id', 'severity']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
