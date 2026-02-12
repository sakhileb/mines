<?php

namespace App\Services;

use App\Models\MineArea;
use Illuminate\Pagination\Paginator;

class MineAreaService
{
    /**
     * Get all mine areas for a team
     */
    public function getAllForTeam($teamId, $perPage = 15)
    {
        return MineArea::forTeam($teamId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active mine areas for a team
     */
    public function getActiveForTeam($teamId)
    {
        return MineArea::forTeam($teamId)
            ->byStatus('active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new mine area
     */
    public function create($teamId, array $data): MineArea
    {
        $data['team_id'] = $teamId;
        return MineArea::create($data);
    }

    /**
     * Update an existing mine area
     */
    public function update(MineArea $mineArea, array $data): MineArea
    {
        $mineArea->update($data);
        return $mineArea->fresh();
    }

    /**
     * Delete a mine area
     */
    public function delete(MineArea $mineArea): bool
    {
        return $mineArea->delete();
    }

    /**
     * Get mine area by ID with authorization check
     */
    public function getById($id, $teamId)
    {
        return MineArea::forTeam($teamId)->find($id);
    }

    /**
     * Get statistics for a team's mine areas
     */
    public function getTeamStatistics($teamId)
    {
        $areas = MineArea::forTeam($teamId)->get();

        return [
            'total_areas' => $areas->count(),
            'active_areas' => $areas->where('status', 'active')->count(),
            'total_area_hectares' => $areas->sum('area_size_hectares') ?? 0,
            'areas_with_manager' => $areas->whereNotNull('manager_name')->count(),
        ];
    }
}
