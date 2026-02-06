<?php

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Machine;
use App\Models\FuelTransaction;
use App\Models\FuelTank;
use App\Services\AI\FuelPredictorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FuelPredictorAgentTest extends TestCase
{
    use RefreshDatabase;

    protected FuelPredictorAgent $agent;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team = Team::factory()->create();
        $this->agent = new FuelPredictorAgent();
    }

    /** @test */
    public function it_detects_high_fuel_consumption(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
            'machine_type' => 'excavator',
        ]);

        // Create high consumption transactions over 30 days
        // Need avg > 240 liters/day (200 * 1.2) to trigger alert
        // 30 days * 250 liters/day = 7500 liters total
        for ($i = 0; $i < 30; $i++) {
            FuelTransaction::factory()->create([
                'team_id' => $this->team->id,
                'machine_id' => $machine->id,
                'transaction_type' => 'dispensing',
                'quantity_liters' => 250, // Very high for excavator (expected: 200/day)
                'transaction_date' => now()->subDays($i),
            ]);
        }

        $result = $this->agent->analyze($this->team);

        $highConsumptionRecs = collect($result['recommendations'])->filter(function ($rec) {
            return str_contains(strtolower($rec['title']), 'high fuel consumption');
        });
        
        $this->assertGreaterThan(0, $highConsumptionRecs->count());
    }

    /** @test */
    public function it_predicts_fuel_needs(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Create consistent consumption history
        for ($i = 0; $i < 30; $i++) {
            FuelTransaction::factory()->create([
                'team_id' => $this->team->id,
                'machine_id' => $machine->id,
                'transaction_type' => 'dispensing',
                'quantity_liters' => 100,
                'transaction_date' => now()->subDays($i),
            ]);
        }

        $result = $this->agent->analyze($this->team);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('recommendations', $result);
    }

    /** @test */
    public function it_detects_low_tank_levels(): void
    {
        FuelTank::factory()->create([
            'team_id' => $this->team->id,
            'capacity_liters' => 10000,
            'current_level_liters' => 1000, // Only 10% full
        ]);

        $result = $this->agent->analyze($this->team);

        $lowTankRecs = collect($result['recommendations'])->filter(function ($rec) {
            return str_contains(strtolower($rec['title']), 'critical tank level');
        });
        
        $this->assertGreaterThan(0, $lowTankRecs->count());
    }

    /** @test */
    public function it_identifies_efficient_machines(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
            'machine_type' => 'excavator',
        ]);

        // Create low consumption transactions (efficient)
        for ($i = 0; $i < 10; $i++) {
            FuelTransaction::factory()->create([
                'team_id' => $this->team->id,
                'machine_id' => $machine->id,
                'transaction_type' => 'dispensing',
                'quantity_liters' => 150, // Below expected 200
                'transaction_date' => now()->subDays($i),
            ]);
        }

        $result = $this->agent->analyze($this->team);

        $efficiencyInsights = collect($result['insights'])->filter(function ($insight) {
            return str_contains(strtolower($insight['title']), 'efficiency');
        });
        
        $this->assertGreaterThan(0, $efficiencyInsights->count());
    }

    /** @test */
    public function it_handles_no_fuel_data(): void
    {
        $result = $this->agent->analyze($this->team);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('recommendations', $result);
        $this->assertArrayHasKey('insights', $result);
    }
}
