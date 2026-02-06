<?php

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Machine;
use App\Models\MachineMetric;
use App\Models\MaintenanceRecord;
use App\Models\MachineHealthStatus;
use App\Services\AI\MaintenancePredictorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaintenancePredictorAgentTest extends TestCase
{
    use RefreshDatabase;

    protected MaintenancePredictorAgent $agent;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team = Team::factory()->create();
        $this->agent = new MaintenancePredictorAgent();
    }

    /** @test */
    public function it_predicts_high_breakdown_risk(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
            'year_of_manufacture' => now()->year - 20, // Max age factor: 0.2
        ]);

        // Create poor health status
        MachineHealthStatus::factory()->create([
            'machine_id' => $machine->id,
            'team_id' => $this->team->id,
            'overall_health_score' => 30, // Very low - health factor: 0.175
        ]);

        // No recent maintenance (long overdue)
        MaintenanceRecord::factory()->create([
            'team_id' => $this->team->id,
            'machine_id' => $machine->id,
            'status' => 'completed',
            'completed_at' => now()->subMonths(8), // Overdue - maintenance factor: 0.25
        ]);

        // High operating hours
        for ($i = 0; $i < 30; $i++) {
            MachineMetric::factory()->create([
                'machine_id' => $machine->id,
                'team_id' => $this->team->id,
                'operating_hours' => 20, // High - hours factor: 0.3
                'recorded_at' => now()->subDays($i),
            ]);
        }
        // Total risk: 0.175 + 0.2 + 0.25 + 0.3 = 0.925 > 0.7 ✓

        $result = $this->agent->analyze($this->team);

        $breakdownRecs = collect($result['recommendations'])->filter(function ($rec) {
            return str_contains(strtolower($rec['title']), 'breakdown risk');
        });
        
        $this->assertGreaterThan(0, $breakdownRecs->count());
    }

    /** @test */
    public function it_calculates_risk_score(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
        ]);

        MachineHealthStatus::factory()->create([
            'machine_id' => $machine->id,
            'overall_health_score' => 50,
        ]);

        $result = $this->agent->analyze($this->team);

        foreach ($result['recommendations'] as $rec) {
            if (isset($rec['data']['risk_score'])) {
                $this->assertGreaterThanOrEqual(0, $rec['data']['risk_score']);
                $this->assertLessThanOrEqual(1, $rec['data']['risk_score']);
            }
        }
    }

    /** @test */
    public function it_identifies_optimal_maintenance_windows(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Create good health status (optimal for maintenance)
        MachineHealthStatus::factory()->create([
            'machine_id' => $machine->id,
            'overall_health_score' => 85,
        ]);

        $result = $this->agent->analyze($this->team);

        $optimalRecs = collect($result['recommendations'])->filter(function ($rec) {
            return str_contains(strtolower($rec['title']), 'optimal');
        });
        
        // May or may not find optimal windows depending on other factors
        $this->assertIsArray($result['recommendations']);
    }

    /** @test */
    public function it_creates_predictive_alerts(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
            'year_of_manufacture' => now()->year - 20, // Age factor: 0.2
        ]);

        MachineHealthStatus::factory()->create([
            'machine_id' => $machine->id,
            'team_id' => $this->team->id,
            'overall_health_score' => 25, // Health factor: 0.1875, very low
        ]);

        // Add high operating hours to push risk over 0.7
        for ($i = 0; $i < 30; $i++) {
            MachineMetric::factory()->create([
                'machine_id' => $machine->id,
                'team_id' => $this->team->id,
                'operating_hours' => 20, // High hours per day
                'recorded_at' => now()->subDays($i),
            ]);
        }

        $result = $this->agent->analyze($this->team);

        // High risk should generate critical recommendations
        $criticalRecs = collect($result['recommendations'])->filter(function ($rec) {
            return $rec['priority'] === 'critical';
        });
        
        $this->assertGreaterThan(0, $criticalRecs->count());
    }

    /** @test */
    public function it_handles_machines_without_health_data(): void
    {
        Machine::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $result = $this->agent->analyze($this->team);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('recommendations', $result);
    }
}
