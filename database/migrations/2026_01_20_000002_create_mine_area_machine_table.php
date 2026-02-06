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
        Schema::create('mine_area_machine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mine_area_id')->constrained('mine_areas')->onDelete('cascade');
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            
            // Assignment tracking
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('unassigned_at')->nullable();
            
            // Additional metadata
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Ensure unique assignments
            $table->unique(['mine_area_id', 'machine_id']);
            
            // Indexes
            $table->index('mine_area_id');
            $table->index('machine_id');
            $table->index('assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_area_machine');
    }
};
