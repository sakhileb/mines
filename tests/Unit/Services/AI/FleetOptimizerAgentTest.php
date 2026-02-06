<?php

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Machine;
use App\Models\MineArea;
use App\Models\MachineMetric;
use App\Services\AI\FleetOptimizerAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FleetOptimizerAgentTest extends TestCase
{
    use RefreshDatabase;

    protected FleetOptimizerAgent $agent;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team = Team::factory()->create();
        $this->agent = new FleetOptimizerAgent();
    }

    /** @test */
    public function it_detects_low_utilization_machines(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'active',
        ]);

        // Create low utilization metrics
        for ($i = 0; $i < 7; $i++) {
            MachineMetric::factory()->create([
                'machine_id' => $machine->id,
                'operating_hours' => 5, // Low utilization
                'recorded_at' => now()->subDays($i),
            ]);
        }

        $result = $this->agent->analyze($this->team);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('recommendations', $result);
        $this->assertArrayHasKey('insights', $result);
        
        // Should detect low utilization
        $lowUtilRecs = collect($result['recommendations'])->filter(function ($rec) {
            return str_contains(strtolower($rec['title']), 'low utilization');
        });
        
        $this->assertGreaterThan(0, $lowUtilRecs->count());
    }

    /** @test */
    public function it_detects_overutilization_risk(): void
    {
        $machine = Machine::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'active',
        ]);

        // Create high utilization metrics
        for ($i = 0; $i < 7; $i++) {
            MachineMetric::factory()->create([
                'machine_id' => $machine->id,
                'operating_hours' => 23, // Very high utilization
                'recorded_at' => now()->subDays($i),
            ]);
        }

        $result = $this->agent->analyze($this->team);

        $overutilRecs = collect($result['recommendations'])->filter(function ($rec) {
            return str_contains(strtolower($rec['title']), 'overutilization');
        });
        
        $this->assertGreaterThan(0, $overutilRecs->count());
    }

    /** @test */
    public function it_analyzes_area_allocation(): void
    {
        $area = MineArea::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Create machines with uneven distribution
        Machine::factory()->count(10)->create([
            'team_id' => $this->team->id,
            'mine_area_id' => $area->id,
        ]);

        $result = $this->agent->analyze($this->team);

        $this->assertIsArray($result['recommendations']);
    }

    /** @test */
    public function it_identifies_idle_fleet(): void
    {
        // Create 10 machines, 5 idle
        Machine::factory()->count(5)->create([
            'team_id' => $this->team->id,
            'status' => 'idle',
        ]);

        Machine::factory()->count(5)->create([
            'team_id' => $this->team->id,
            'status' => 'active',
        ]);

        $result = $this->agent->analyze($this->team);

        // Should detect high idle percentage (50%)
        $idleRecs = collect($result['recommendations'])->filter(function ($rec) {
            return str_contains(strtolower($rec['title']), 'idle');
        });
        
        $this->assertGreaterThan(0, $idleRecs->count());
    }

    /** @test */
    public function it_handles_empty_fleet(): void
    {
        $result = $this->agent->analyze($this->team);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('recommendations', $result);
        $this->assertArrayHasKey('insights', $result);
    }

    /** @test */
    public function it_includes_confidence_scores(): void
    {
        Machine::factory()->count(3)->create([
            'team_id' => $this->team->id,
            'status' => 'idle',
        ]);

        $result = $this->agent->analyze($this->team);

        foreach ($result['recommendations'] as $rec) {
            $this->assertArrayHasKey('confidence_score', $rec);
            $this->assertGreaterThanOrEqual(0, $rec['confidence_score']);
            $this->assertLessThanOrEqual(1, $rec['confidence_score']);
        }
    }
}
