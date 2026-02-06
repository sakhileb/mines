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
        Schema::create('mine_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mine_area_id')->constrained('mine_areas')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('set null')->nullable();
            
            // File information
            $table->string('file_name');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->enum('file_type', ['pdf', 'dwg', 'dxf', 'png', 'jpg']);
            
            // Version tracking
            $table->integer('version')->default(1);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Georeferencing
            $table->decimal('scale', 10, 4)->nullable();
            $table->decimal('reference_point_lat', 10, 8)->nullable();
            $table->decimal('reference_point_lon', 11, 8)->nullable();
            $table->decimal('rotation_degrees', 5, 2)->default(0);
            
            // Status
            $table->boolean('is_current')->default(true);
            $table->enum('status', ['active', 'archived'])->default('active');
            
            // Metadata for custom fields
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('mine_area_id');
            $table->index('uploaded_by');
            $table->index('file_type');
            $table->index('is_current');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_plans');
    }
};
