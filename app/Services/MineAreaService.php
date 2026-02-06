<?php

namespace App\Services;

use App\Models\MineArea;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MineAreaService
{
    /**
     * Create a new mine area.
     */
    public function create(Team $team, array $data): MineArea
    {
        try {
            // Validate coordinates
            if (empty($data['coordinates']) || count($data['coordinates']) < 3) {
                throw new \InvalidArgumentException('Mine area requires at least 3 coordinate points');
            }

            // Normalize coordinates: convert ['lat'=>x, 'lon'=>y] to [x, y] if needed
            $normalized = array_map(function ($coord) {
                if (is_array($coord) && isset($coord['lat']) && isset($coord['lon'])) {
                    return [floatval($coord['lat']), floatval($coord['lon'])];
                }
                return $coord;
            }, $data['coordinates']);

            // Calculate area, perimeter, and center
            $area = MineArea::calculateArea($normalized);
            $perimeter = MineArea::calculatePerimeter($normalized);
            $center = MineArea::calculateCenter($normalized);

            // Create the mine area
            $mineArea = $team->mineAreas()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? 'pit',
                'coordinates' => $data['coordinates'], // store as originally provided
                'center_latitude' => $center['latitude'],
                'center_longitude' => $center['longitude'],
                'area_sqm' => $area,
                'perimeter_m' => $perimeter,
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            Log::info('Created mine area', [
                'mine_area_id' => $mineArea->id,
                'team_id' => $team->id,
                'name' => $mineArea->name,
            ]);

            return $mineArea;

        } catch (\Exception $e) {
            Log::error('Failed to create mine area', [
                'team_id' => $team->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing mine area.
     */
    public function update(MineArea $mineArea, array $data): MineArea
    {
        try {
            // If coordinates changed, recalculate
            if (isset($data['coordinates']) && $data['coordinates'] !== $mineArea->coordinates) {
                $area = MineArea::calculateArea($data['coordinates']);
                $perimeter = MineArea::calculatePerimeter($data['coordinates']);
                $center = MineArea::calculateCenter($data['coordinates']);

                $data['area_sqm'] = $area;
                $data['perimeter_m'] = $perimeter;
                $data['center_latitude'] = $center['latitude'];
                $data['center_longitude'] = $center['longitude'];
            }

            $mineArea->update($data);

            Log::info('Updated mine area', [
                'mine_area_id' => $mineArea->id,
                'team_id' => $mineArea->team_id,
            ]);

            return $mineArea;

        } catch (\Exception $e) {
            Log::error('Failed to update mine area', [
                'mine_area_id' => $mineArea->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a mine area.
     */
    public function delete(MineArea $mineArea): bool
    {
        try {
            $mineAreaId = $mineArea->id;
            $teamId = $mineArea->team_id;

            $mineArea->delete();

            Log::info('Deleted mine area', [
                'mine_area_id' => $mineAreaId,
                'team_id' => $teamId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete mine area', [
                'mine_area_id' => $mineArea->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Assign machines to a mine area.
     */
    public function assignMachines(MineArea $mineArea, array $machineIds, ?string $notes = null): Collection
    {
        try {
            $machines = collect();

            foreach ($machineIds as $machineId) {
                // Only sync if not already assigned
                if (!$mineArea->machines()->where('machine_id', $machineId)->exists()) {
                    $mineArea->machines()->attach($machineId, [
                        'assigned_at' => now(),
                        'notes' => $notes,
                    ]);

                    $machine = $mineArea->team->machines()->find($machineId);
                    if ($machine) {
                        $machines->push($machine);
                    }
                }
            }

            Log::info('Assigned machines to mine area', [
                'mine_area_id' => $mineArea->id,
                'machine_count' => count($machineIds),
            ]);

            return $machines;

        } catch (\Exception $e) {
            Log::error('Failed to assign machines', [
                'mine_area_id' => $mineArea->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Unassign machines from a mine area.
     */
    public function unassignMachines(MineArea $mineArea, array $machineIds): bool
    {
        try {
            $mineArea->machines()->detach($machineIds);

            Log::info('Unassigned machines from mine area', [
                'mine_area_id' => $mineArea->id,
                'machine_count' => count($machineIds),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to unassign machines', [
                'mine_area_id' => $mineArea->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Find machines within a mine area based on their current location.
     */
    public function findMachinesInArea(MineArea $mineArea): Collection
    {
        return $mineArea->team->machines()
            ->where('status', '!=', 'offline')
            ->whereNotNull('last_location_latitude')
            ->whereNotNull('last_location_longitude')
            ->get()
            ->filter(function ($machine) use ($mineArea) {
                return $mineArea->containsPoint(
                    $machine->last_location_latitude,
                    $machine->last_location_longitude
                );
            });
    }

    /**
     * Get statistics for a mine area.
     */
    public function getStatistics(MineArea $mineArea): array
    {
        $assignedMachines = $mineArea->machines;
        
        return [
            'total_area_sqm' => $mineArea->area_sqm,
            'total_perimeter_m' => $mineArea->perimeter_m,
            'total_machines' => $assignedMachines->count(),
            'assigned_machines' => $assignedMachines->count(),
            'active_machines' => $assignedMachines->where('status', 'active')->count(),
            'machines_in_area' => $this->findMachinesInArea($mineArea)->count(),
            'total_production' => $mineArea->production()->sum('tonnage') ?? 0,
            'latest_production' => $mineArea->production()->latest('recorded_date')->first(),
            'total_plans' => $mineArea->plans()->count(),
            'current_plan' => $mineArea->plans()->where('is_current', true)->first(),
        ];
    }

    /**
     * Export mine area as GeoJSON.
     */
    public function exportGeoJSON(MineArea $mineArea): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [$mineArea->coordinates],
                    ],
                    'properties' => [
                        'id' => $mineArea->id,
                        'name' => $mineArea->name,
                        'description' => $mineArea->description,
                        'type' => $mineArea->type,
                        'area_sqm' => $mineArea->area_sqm,
                        'perimeter_m' => $mineArea->perimeter_m,
                        'center' => [
                            'latitude' => $mineArea->center_latitude,
                            'longitude' => $mineArea->center_longitude,
                        ],
                    ],
                ],
            ],
        ];
    }
}
