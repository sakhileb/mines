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
        Schema::create('ai_recommendation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('recommendation_hash')->index();
            $table->json('recommendation');
            $table->string('status')->default('pending'); // pending, implemented, rejected
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->json('performance_impact')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendation_actions');
    }
};
