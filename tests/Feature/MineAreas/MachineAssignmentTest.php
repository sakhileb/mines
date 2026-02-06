<?php

namespace Tests\Feature\MineAreas;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\MineArea;
use App\Models\Machine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MachineAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;
    protected $mineArea;
    protected $machines;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
        
        $this->mineArea = MineArea::factory()->create(['team_id' => $this->team->id]);
        $this->machines = Machine::factory()->count(5)->create(['team_id' => $this->team->id]);
    }

    public function test_can_assign_single_machine()
    {
        $response = $this->actingAs($this->user)
            ->post("/api/mine-areas/{$this->mineArea->id}/assign-machines", [
                'machine_ids' => [$this->machines[0]->id],
            ]);

        $response->assertStatus(200);
        $this->assertTrue($this->mineArea->machines()->where('machine_id', $this->machines[0]->id)->exists());
    }

    public function test_can_assign_multiple_machines()
    {
        $machineIds = [$this->machines[0]->id, $this->machines[1]->id, $this->machines[2]->id];

        $response = $this->actingAs($this->user)
            ->post("/api/mine-areas/{$this->mineArea->id}/assign-machines", [
                'machine_ids' => $machineIds,
                'notes' => 'Bulk assignment for Phase 1',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(3, $this->mineArea->machines()->count());
    }

    public function test_can_unassign_machine()
    {
        $this->mineArea->machines()->attach($this->machines[0]);

        $response = $this->actingAs($this->user)
            ->post("/api/mine-areas/{$this->mineArea->id}/unassign-machines", [
                'machine_ids' => [$this->machines[0]->id],
            ]);

        $response->assertStatus(200);
        $this->assertFalse($this->mineArea->machines()->where('machine_id', $this->machines[0]->id)->whereNull('unassigned_at')->exists());
    }

    public function test_can_get_available_machines()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/assignments/available');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_can_get_assignment_history()
    {
        $this->mineArea->machines()->attach($this->machines[0], [
            'assigned_at' => now()->subHours(2),
            'unassigned_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->user)
            ->get("/api/assignments/machines/{$this->machines[0]->id}/history");

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_can_get_current_assignments()
    {
        $this->mineArea->machines()->attach(
            $this->machines[0],
            ['assigned_at' => now()],
        );
        $this->mineArea->machines()->attach(
            $this->machines[1],
            ['assigned_at' => now()->subHours(2), 'unassigned_at' => now()->subHour()],
        );

        $response = $this->actingAs($this->user)
            ->get("/api/assignments/areas/{$this->mineArea->id}/current");

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_assignment_notes_are_stored()
    {
        $notes = 'Testing high precision area';

        $this->actingAs($this->user)
            ->post("/api/mine-areas/{$this->mineArea->id}/assign-machines", [
                'machine_ids' => [$this->machines[0]->id],
                'notes' => $notes,
            ]);

        $this->assertDatabaseHas('mine_area_machine', [
            'machine_id' => $this->machines[0]->id,
            'mine_area_id' => $this->mineArea->id,
            'notes' => $notes,
        ]);
    }
}
