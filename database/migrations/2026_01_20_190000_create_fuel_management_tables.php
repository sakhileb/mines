<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fuel Tanks - Track fuel storage locations
        Schema::create('fuel_tanks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('mine_area_id')->nullable()->constrained('mine_areas')->cascadeOnDelete();
            $table->string('name');
            $table->string('tank_number')->nullable();
            $table->string('location_description')->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->decimal('capacity_liters', 12, 2); // Maximum capacity
            $table->decimal('current_level_liters', 12, 2)->default(0); // Current fuel level
            $table->decimal('minimum_level_liters', 12, 2)->default(0); // Alert threshold
            $table->string('fuel_type'); // diesel, petrol, biodiesel, etc.
            $table->enum('status', ['active', 'maintenance', 'inactive', 'decommissioned'])->default('active');
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('mine_area_id');
            $table->index('status');
            $table->index('fuel_type');
        });

        // Fuel Transactions - Track all fuel movements
        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('fuel_tank_id')->nullable()->constrained('fuel_tanks')->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Who recorded it
            $table->enum('transaction_type', ['refill', 'dispensing', 'delivery', 'transfer', 'adjustment', 'theft', 'spillage']);
            $table->decimal('quantity_liters', 10, 2);
            $table->decimal('unit_price', 10, 2)->nullable(); // Price per liter
            $table->decimal('total_cost', 12, 2)->nullable(); // Total transaction cost
            $table->string('fuel_type');
            $table->timestamp('transaction_date');
            $table->decimal('odometer_reading', 12, 2)->nullable(); // Machine odometer at time of refueling
            $table->decimal('machine_hours', 10, 2)->nullable(); // Machine hours at time of refueling
            $table->string('supplier')->nullable(); // Fuel supplier name
            $table->string('invoice_number')->nullable();
            $table->string('receipt_file_path')->nullable(); // Receipt/invoice document
            $table->foreignId('from_tank_id')->nullable()->constrained('fuel_tanks')->nullOnDelete(); // For transfers
            $table->foreignId('to_tank_id')->nullable()->constrained('fuel_tanks')->nullOnDelete(); // For transfers
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('fuel_tank_id');
            $table->index('machine_id');
            $table->index('user_id');
            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index('fuel_type');
        });

        // Fuel Consumption Metrics - Calculated consumption data
        Schema::create('fuel_consumption_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->date('date'); // Day of the metric
            $table->decimal('fuel_consumed_liters', 10, 2)->default(0);
            $table->decimal('distance_traveled_km', 10, 2)->nullable();
            $table->decimal('operating_hours', 10, 2)->nullable();
            $table->decimal('fuel_efficiency_lph', 10, 4)->nullable(); // Liters per hour
            $table->decimal('fuel_efficiency_lpkm', 10, 4)->nullable(); // Liters per km (if applicable)
            $table->decimal('idle_time_hours', 10, 2)->nullable();
            $table->decimal('idle_fuel_consumed', 10, 2)->nullable();
            $table->decimal('average_load_percentage', 5, 2)->nullable(); // Average load during operation
            $table->string('shift')->nullable(); // morning, afternoon, night
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();

            $table->unique(['machine_id', 'date']);
            $table->index('team_id');
            $table->index('machine_id');
            $table->index('date');
        });

        // Fuel Alerts - Track fuel-related alerts
        Schema::create('fuel_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('fuel_tank_id')->nullable()->constrained('fuel_tanks')->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->cascadeOnDelete();
            $table->enum('alert_type', ['low_fuel', 'critical_fuel', 'tank_low', 'tank_critical', 'high_consumption', 'unusual_pattern', 'overdue_refill', 'leak_detected']);
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
            $table->index('fuel_tank_id');
            $table->index('machine_id');
            $table->index('alert_type');
            $table->index('severity');
            $table->index('status');
            $table->index('triggered_at');
        });

        // Fuel Budgets - Track fuel budgets and spending
        Schema::create('fuel_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('mine_area_id')->nullable()->constrained('mine_areas')->cascadeOnDelete();
            $table->string('period_type'); // monthly, quarterly, annual
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budgeted_amount', 12, 2); // Money
            $table->decimal('budgeted_liters', 12, 2)->nullable(); // Volume
            $table->decimal('actual_spent', 12, 2)->default(0);
            $table->decimal('actual_liters', 12, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'exceeded'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('mine_area_id');
            $table->index('period_type');
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_budgets');
        Schema::dropIfExists('fuel_alerts');
        Schema::dropIfExists('fuel_consumption_metrics');
        Schema::dropIfExists('fuel_transactions');
        Schema::dropIfExists('fuel_tanks');
    }
};
