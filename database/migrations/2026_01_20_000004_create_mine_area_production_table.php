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
        Schema::create('mine_area_production', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mine_area_id')->constrained('mine_areas')->onDelete('cascade');
            
            // Production data
            $table->date('recorded_date');
            $table->string('material_type')->nullable();
            $table->decimal('tonnage', 15, 2)->nullable();
            $table->decimal('volume_cubic_m', 15, 2)->nullable();
            
            // Equipment used
            $table->json('machines_used')->nullable();
            
            // Notes and metadata
            $table->text('operator_notes')->nullable();
            $table->enum('status', ['recorded', 'verified', 'archived'])->default('recorded');
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('mine_area_id');
            $table->index('recorded_date');
            $table->index('material_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_area_production');
    }
};
