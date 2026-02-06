<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\MineArea;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MineAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_mine_area_has_team()
    {
        $team = Team::factory()->create();
        $area = MineArea::factory()->create(['team_id' => $team->id]);

        $this->assertNotNull($area->team);
        $this->assertEquals($team->id, $area->team->id);
    }

    public function test_mine_area_has_machines()
    {
        $area = MineArea::factory()->create();

        $this->assertTrue($area->machines()->exists() === false);
    }

    public function test_mine_area_has_plans()
    {
        $area = MineArea::factory()->create();

        $this->assertTrue($area->plans()->exists() === false);
    }

    public function test_mine_area_has_production()
    {
        $area = MineArea::factory()->create();

        $this->assertTrue($area->production()->exists() === false);
    }

    public function test_mine_area_coordinates_are_stored()
    {
        $coords = [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]];
        $area = MineArea::factory()->create(['coordinates' => $coords]);

        $this->assertEquals($coords, $area->coordinates);
    }

    public function test_mine_area_status_defaults_to_active()
    {
        $area = MineArea::factory()->create();

        $this->assertEquals('active', $area->status);
    }

    public function test_mine_area_type_validation()
    {
        $types = ['pit', 'stockpile', 'processing'];

        foreach ($types as $type) {
            $area = MineArea::factory()->create(['type' => $type]);
            $this->assertEquals($type, $area->type);
        }
    }
}
