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
        // Monthly Fuel Allocations - Track monthly liter availability
        Schema::create('fuel_monthly_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('allocated_liters', 12, 2); // Monthly allocation in liters
            $table->decimal('fuel_price_per_liter', 10, 2); // ZAR per liter
            $table->decimal('total_budget_zar', 12, 2); // Total budget in ZAR
            $table->decimal('consumed_liters', 12, 2)->default(0); // Actual consumption
            $table->decimal('remaining_liters', 12, 2)->default(0); // Remaining allocation
            $table->decimal('spent_zar', 12, 2)->default(0); // Actual spent
            $table->decimal('remaining_budget_zar', 12, 2)->default(0); // Remaining budget
            $table->enum('status', ['planned', 'active', 'completed', 'exceeded'])->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'year', 'month']);
            $table->index('team_id');
            $table->index(['year', 'month']);
            $table->index('status');
        });

        // Add fuel price tracking to transactions
        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->string('currency', 3)->default('ZAR')->after('total_cost');
            $table->foreignId('monthly_allocation_id')->nullable()->after('team_id')->constrained('fuel_monthly_allocations')->nullOnDelete();
        });

        // Add current fuel price to tanks for easy reference
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->decimal('current_price_per_liter', 10, 2)->nullable()->after('fuel_type');
            $table->string('currency', 3)->default('ZAR')->after('current_price_per_liter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropColumn(['current_price_per_liter', 'currency']);
        });

        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->dropForeign(['monthly_allocation_id']);
            $table->dropColumn(['currency', 'monthly_allocation_id']);
        });

        Schema::dropIfExists('fuel_monthly_allocations');
    }
};
