<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->enum('type', ['sensor_reading', 'maintenance_alert', 'compliance_violation', 'production_anomaly', 'sensor_status_changed', 'custom']);
            $table->string('title');
            $table->text('message');
            $table->enum('alert_level', ['critical', 'high', 'warning', 'info'])->default('info');
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('type');
            $table->index('alert_level');
            $table->index('created_at');
        });

        Schema::create('notification_read', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at')->useCurrent();

            $table->unique(['notification_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_read');
        Schema::dropIfExists('notifications');
    }
};
