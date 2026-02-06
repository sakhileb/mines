<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Team;
use App\Models\MineArea;
use App\Services\MineAreaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MineAreaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $team;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(MineAreaService::class);
        $this->team = Team::factory()->create();
    }

    public function test_can_create_mine_area_with_service()
    {
        $data = [
            'name' => 'Test Area',
            'type' => 'pit',
            'description' => 'Test',
            'coordinates' => [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]],
        ];

        $area = $this->service->create($this->team, $data);

        $this->assertInstanceOf(MineArea::class, $area);
        $this->assertEquals('Test Area', $area->name);
        $this->assertEquals($this->team->id, $area->team_id);
    }

    public function test_service_calculates_area()
    {
        $data = [
            'name' => 'Test',
            'type' => 'pit',
            'coordinates' => [[0, 0], [10, 0], [10, 10], [0, 10], [0, 0]],
        ];

        $area = $this->service->create($this->team, $data);

        $this->assertGreaterThan(0, $area->area_sqm);
    }

    public function test_service_calculates_perimeter()
    {
        $data = [
            'name' => 'Test',
            'type' => 'pit',
            'coordinates' => [[0, 0], [10, 0], [10, 10], [0, 10], [0, 0]],
        ];

        $area = $this->service->create($this->team, $data);

        $this->assertGreaterThan(0, $area->perimeter_m);
    }

    public function test_can_update_mine_area()
    {
        $area = MineArea::factory()->create(['team_id' => $this->team->id]);

        $updated = $this->service->update($area, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_can_delete_mine_area()
    {
        $area = MineArea::factory()->create(['team_id' => $this->team->id]);
        $id = $area->id;

        $this->service->delete($area);

        $this->assertNull(MineArea::find($id));
    }

    public function test_can_get_statistics()
    {
        $area = MineArea::factory()->create(['team_id' => $this->team->id]);

        $stats = $this->service->getStatistics($area);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_machines', $stats);
        $this->assertArrayHasKey('active_machines', $stats);
    }

    public function test_can_export_to_geojson()
    {
        $area = MineArea::factory()->create(['team_id' => $this->team->id]);

        $geojson = $this->service->exportGeoJson($area);

        $this->assertEquals('FeatureCollection', $geojson['type']);
        $this->assertIsArray($geojson['features']);
    }
}
