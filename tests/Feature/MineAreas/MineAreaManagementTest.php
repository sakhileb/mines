<?php

namespace Tests\Feature\MineAreas;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\MineArea;
use App\Models\Machine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MineAreaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;
    protected $mineArea;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
        
        $this->mineArea = MineArea::factory()->create([
            'team_id' => $this->team->id,
            'coordinates' => [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]],
        ]);
    }

    public function test_can_create_mine_area()
    {
        $response = $this->actingAs($this->user)
            ->post('/api/mine-areas', [
                'name' => 'Test Mine Area',
                'type' => 'pit',
                'description' => 'Test Description',
                'coordinates' => [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]],
                'status' => 'active',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('mine_areas', ['name' => 'Test Mine Area']);
    }

    public function test_can_retrieve_mine_area()
    {
        $response = $this->actingAs($this->user)
            ->get("/api/mine-areas/{$this->mineArea->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('id', $this->mineArea->id);
        $response->assertJsonPath('name', $this->mineArea->name);
    }

    public function test_can_update_mine_area()
    {
        $response = $this->actingAs($this->user)
            ->put("/api/mine-areas/{$this->mineArea->id}", [
                'name' => 'Updated Name',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('mine_areas', ['id' => $this->mineArea->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_mine_area()
    {
        $response = $this->actingAs($this->user)
            ->delete("/api/mine-areas/{$this->mineArea->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('mine_areas', ['id' => $this->mineArea->id]);
    }

    public function test_can_list_mine_areas()
    {
        MineArea::factory()->count(5)->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)
            ->get('/api/mine-areas');

        $response->assertStatus(200);
        $response->assertJsonCount(6, 'data');
    }

    public function test_can_filter_mine_areas_by_type()
    {
        MineArea::factory()->create(['team_id' => $this->team->id, 'type' => 'pit']);
        MineArea::factory()->create(['team_id' => $this->team->id, 'type' => 'stockpile']);

        $response = $this->actingAs($this->user)
            ->get('/api/mine-areas?type=pit');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->pluck('type')->every(fn($t) => $t === 'pit'));
    }

    public function test_can_search_mine_areas()
    {
        $response = $this->actingAs($this->user)
            ->get("/api/mine-areas?search={$this->mineArea->name}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_cannot_access_other_team_mine_areas()
    {
        $otherTeam = Team::factory()->create();
        $otherArea = MineArea::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->get("/api/mine-areas/{$otherArea->id}");

        // Returns 404 because global scope filters out other team's areas
        $response->assertStatus(404);
    }

    public function test_can_get_mine_area_statistics()
    {
        Machine::factory()->count(3)->create(['team_id' => $this->team->id])
            ->each(function ($machine) {
                $machine->mineAreas()->attach($this->mineArea);
            });

        $response = $this->actingAs($this->user)
            ->get("/api/mine-areas/{$this->mineArea->id}/statistics");

        $response->assertStatus(200);
        $response->assertJsonPath('total_machines', 3);
    }

    public function test_can_export_as_geojson()
    {
        $response = $this->actingAs($this->user)
            ->get("/api/mine-areas/{$this->mineArea->id}/export/geojson");

        $response->assertStatus(200);
        $response->assertJsonPath('type', 'FeatureCollection');
        $response->assertJsonPath('features.0.geometry.type', 'Polygon');
    }

    public function test_can_export_as_csv()
    {
        $response = $this->actingAs($this->user)
            ->get("/api/mine-areas/{$this->mineArea->id}/export/csv");

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('Content-Type'), 'text/csv'),
            'Expected Content-Type to contain text/csv'
        );
    }
}
