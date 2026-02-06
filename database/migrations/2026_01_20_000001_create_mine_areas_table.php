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
        Schema::create('mine_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['pit', 'stockpile', 'dump', 'processing', 'facility'])->default('pit');
            
            // Polygon coordinates stored as JSON [[lat, lon], [lat, lon], ...]
            $table->json('coordinates');
            
            // Center point for easy access
            $table->decimal('center_latitude', 10, 8);
            $table->decimal('center_longitude', 11, 8);
            
            // Area and perimeter calculations
            $table->decimal('area_sqm', 15, 2)->nullable();
            $table->decimal('perimeter_m', 15, 2)->nullable();
            
            // Status tracking
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            
            // Metadata for custom fields
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('team_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_areas');
    }
};
