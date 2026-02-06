<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Machine Health Status - Real-time health monitoring
        Schema::create('machine_health_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->integer('overall_health_score')->default(100); // 0-100
            $table->enum('health_status', ['excellent', 'good', 'fair', 'poor', 'critical'])->default('good');
            $table->json('component_scores')->nullable(); // Individual component health scores
            $table->integer('engine_health')->nullable();
            $table->integer('transmission_health')->nullable();
            $table->integer('hydraulics_health')->nullable();
            $table->integer('electrical_health')->nullable();
            $table->integer('brakes_health')->nullable();
            $table->integer('cooling_system_health')->nullable();
            $table->timestamp('last_diagnostic_scan')->nullable();
            $table->json('active_fault_codes')->nullable();
            $table->integer('fault_code_count')->default(0);
            $table->text('recommendations')->nullable();
            $table->timestamps();

            $table->unique('machine_id');
            $table->index('team_id');
            $table->index('health_status');
            $table->index('overall_health_score');
        });

        // Maintenance Schedules - Planned maintenance
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->string('maintenance_type'); // preventive, predictive, corrective, routine
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('schedule_type', ['hours', 'kilometers', 'calendar', 'condition']); // Trigger type
            $table->integer('interval_hours')->nullable(); // Every X hours
            $table->integer('interval_km')->nullable(); // Every X km
            $table->integer('interval_days')->nullable(); // Every X days
            $table->integer('last_service_hours')->nullable();
            $table->integer('last_service_km')->nullable();
            $table->date('last_service_date')->nullable();
            $table->integer('next_service_hours')->nullable();
            $table->integer('next_service_km')->nullable();
            $table->date('next_service_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['active', 'due', 'overdue', 'completed', 'paused'])->default('active');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->integer('estimated_duration_hours')->nullable();
            $table->json('required_parts')->nullable();
            $table->json('required_tools')->nullable();
            $table->boolean('auto_generate_work_order')->default(true);
            $table->timestamps();

            $table->index('team_id');
            $table->index('machine_id');
            $table->index('status');
            $table->index('next_service_date');
        });

        // Maintenance Records - Historical maintenance
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->foreignId('maintenance_schedule_id')->nullable()->constrained('maintenance_schedules')->nullOnDelete();
            $table->string('work_order_number')->unique();
            $table->string('maintenance_type'); // preventive, predictive, corrective, emergency, routine
            $table->string('title');
            $table->text('description');
            $table->text('work_performed')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->timestamp('scheduled_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Technician
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('labor_hours', 10, 2)->nullable();
            $table->decimal('labor_cost', 10, 2)->nullable();
            $table->decimal('parts_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->json('parts_used')->nullable(); // Array of parts with quantities
            $table->json('fault_codes_cleared')->nullable();
            $table->integer('odometer_reading')->nullable();
            $table->integer('hour_meter_reading')->nullable();
            $table->text('technician_notes')->nullable();
            $table->json('attachments')->nullable(); // Photos, documents
            $table->boolean('machine_operational')->default(true); // Machine status after maintenance
            $table->timestamps();

            $table->index('team_id');
            $table->index('machine_id');
            $table->index('status');
            $table->index('scheduled_date');
            $table->index('assigned_to');
            $table->index('work_order_number');
        });

        // Health Metrics - Sensor data and diagnostic readings
        Schema::create('health_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->timestamp('recorded_at');
            $table->string('metric_type'); // temperature, pressure, vibration, voltage, etc.
            $table->string('component'); // engine, transmission, hydraulics, etc.
            $table->decimal('value', 10, 4);
            $table->string('unit');
            $table->decimal('normal_min', 10, 4)->nullable();
            $table->decimal('normal_max', 10, 4)->nullable();
            $table->boolean('is_normal')->default(true);
            $table->enum('severity', ['normal', 'warning', 'critical'])->default('normal');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('machine_id');
            $table->index('recorded_at');
            $table->index('metric_type');
            $table->index('severity');
        });

        // Component Replacement Tracking
        Schema::create('component_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->foreignId('maintenance_record_id')->nullable()->constrained('maintenance_records')->nullOnDelete();
            $table->string('component_name');
            $table->string('part_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('replacement_date');
            $table->integer('machine_hours_at_replacement')->nullable();
            $table->integer('machine_km_at_replacement')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('expected_lifespan_hours')->nullable();
            $table->integer('expected_lifespan_km')->nullable();
            $table->text('replacement_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('machine_id');
            $table->index('replacement_date');
            $table->index('component_name');
        });

        // Maintenance Alerts
        Schema::create('maintenance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->foreignId('maintenance_schedule_id')->nullable()->constrained('maintenance_schedules')->nullOnDelete();
            $table->enum('alert_type', ['service_due', 'service_overdue', 'health_warning', 'health_critical', 'fault_code', 'component_warning']);
            $table->string('title');
            $table->text('message');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('machine_id');
            $table->index('status');
            $table->index('severity');
            $table->index('triggered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_alerts');
        Schema::dropIfExists('component_replacements');
        Schema::dropIfExists('health_metrics');
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('machine_health_status');
    }
};
