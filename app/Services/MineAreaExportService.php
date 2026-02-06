<?php

namespace App\Services;

use App\Models\MineArea;
use App\Models\MinePlan;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class MineAreaExportService
{
    /**
     * Export mine area as GeoJSON
     */
    public function exportGeoJson(MineArea $mineArea): array
    {
        $machines = $mineArea->machines()->get()->map(function ($machine) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        $machine->last_location_longitude ?? 0,
                        $machine->last_location_latitude ?? 0,
                    ],
                ],
                'properties' => [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'model' => $machine->model,
                    'status' => $machine->status,
                    'assigned_at' => $machine->pivot->assigned_at,
                ],
            ];
        });

        $coordinates = $mineArea->coordinates ?? [];
        
        return [
            'type' => 'FeatureCollection',
            'features' => array_merge([
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [$coordinates],
                    ],
                    'properties' => [
                        'id' => $mineArea->id,
                        'name' => $mineArea->name,
                        'type' => $mineArea->type,
                        'status' => $mineArea->status,
                        'area_sqm' => $mineArea->area_sqm,
                        'perimeter_m' => $mineArea->perimeter_m,
                    ],
                ],
            ], $machines->toArray()),
        ];
    }

    /**
     * Export mine area data as CSV
     */
    public function exportCsv(MineArea $mineArea): string
    {
        $csv = "Mine Area Report\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        // Area Information
        $csv .= "AREA INFORMATION\n";
        $csv .= "Name,Type,Status,Area (m²),Perimeter (m),Created\n";
        $csv .= sprintf(
            "%s,%s,%s,%s,%s,%s\n\n",
            $mineArea->name,
            ucfirst($mineArea->type),
            ucfirst($mineArea->status),
            $mineArea->area_sqm ?? 0,
            $mineArea->perimeter_m ?? 0,
            $mineArea->created_at->format('Y-m-d')
        );

        // Assigned Machines
        $csv .= "ASSIGNED MACHINES\n";
        $csv .= "Machine Name,Model,Status,Assigned Date,Notes\n";
        
        foreach ($mineArea->machines()->get() as $machine) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $machine->name,
                $machine->model,
                $machine->status,
                $machine->pivot->assigned_at->format('Y-m-d H:i'),
                $machine->pivot->notes ?? ''
            );
        }

        return $csv;
    }

    /**
     * Export production report as CSV
     */
    public function exportProductionReport(MineArea $mineArea, $startDate = null, $endDate = null): string
    {
        $query = $mineArea->production();

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $production = $query->orderBy('date', 'desc')->get();

        $csv = "Production Report - " . $mineArea->name . "\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        $csv .= "Date,Material,Tonnage,Entry Notes\n";

        foreach ($production as $entry) {
            $csv .= sprintf(
                "%s,%s,%s,%s\n",
                $entry->date->format('Y-m-d'),
                $entry->material_name,
                $entry->material_tonnage,
                $entry->notes ?? ''
            );
        }

        $csv .= "\nSummary\n";
        $csv .= "Total Tonnage," . number_format($production->sum('material_tonnage') ?? 0, 2) . "\n";
        $csv .= "Average Daily Tonnage," . number_format($production->avg('material_tonnage') ?? 0, 2) . "\n";

        return $csv;
    }

    /**
     * Export assignment history as CSV
     */
    public function exportAssignmentHistory(MineArea $mineArea): string
    {
        $machines = $mineArea->machines()
            ->withPivot('assigned_at', 'unassigned_at', 'notes')
            ->orderBy('mine_area_machine.assigned_at', 'desc')
            ->get();

        $csv = "Assignment History - " . $mineArea->name . "\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        $csv .= "Machine,Assigned Date,Unassigned Date,Duration (hours),Notes\n";

        foreach ($machines as $machine) {
            $duration = $machine->pivot->unassigned_at 
                ? $machine->pivot->assigned_at->diffInHours($machine->pivot->unassigned_at)
                : 'Active';

            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $machine->name,
                $machine->pivot->assigned_at->format('Y-m-d H:i'),
                $machine->pivot->unassigned_at ? $machine->pivot->unassigned_at->format('Y-m-d H:i') : 'N/A',
                $duration,
                $machine->pivot->notes ?? ''
            );
        }

        return $csv;
    }

    /**
     * Download file from storage
     */
    public function download(MinePlan $plan)
    {
        return Storage::disk('private')->download($plan->file_path, $plan->filename);
    }

    /**
     * Generate multi-area report
     */
    public function generateMultiAreaReport(array $mineAreaIds, $startDate = null, $endDate = null): string
    {
        $areas = MineArea::whereIn('id', $mineAreaIds)->get();

        $csv = "Multi-Area Report\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        $csv .= "Area,Type,Status,Total Machines,Active Machines,Area (m²),Production\n";

        $totalMachines = 0;
        $totalProduction = 0;

        foreach ($areas as $area) {
            $machines = $area->machines()->count();
            $activeMachines = $area->machines()->where('status', 'online')->count();
            $production = $area->production()->sum('material_tonnage') ?? 0;

            $totalMachines += $machines;
            $totalProduction += $production;

            $csv .= sprintf(
                "%s,%s,%s,%d,%d,%s,%s\n",
                $area->name,
                ucfirst($area->type),
                ucfirst($area->status),
                $machines,
                $activeMachines,
                $area->area_sqm ?? 0,
                number_format($production, 2)
            );
        }

        $csv .= "\nTotals\n";
        $csv .= "Total Areas," . $areas->count() . "\n";
        $csv .= "Total Machines," . $totalMachines . "\n";
        $csv .= "Total Production," . number_format($totalProduction, 2) . " T\n";

        return $csv;
    }
}
