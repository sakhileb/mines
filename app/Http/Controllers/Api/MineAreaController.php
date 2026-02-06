<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MineArea;
use App\Models\MinePlan;
use App\Services\MineAreaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MineAreaController extends Controller
{
    protected MineAreaService $service;

    public function __construct(MineAreaService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all mine areas with statistics
     */
    public function index(Request $request)
    {
        $team = auth()->user()->currentTeam;
        $query = $team->mineAreas();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        $mineAreas = $query->with('machines', 'plans', 'production')
            ->paginate($request->get('per_page', 15));

        // Add statistics to each area
        $mineAreas->getCollection()->transform(function ($area) {
            return [
                'id' => $area->id,
                'name' => $area->name,
                'type' => $area->type,
                'description' => $area->description,
                'status' => $area->status,
                'coordinates' => $area->coordinates,
                'area_sqm' => $area->area_sqm,
                'perimeter_m' => $area->perimeter_m,
                'statistics' => [
                    'total_machines' => $area->machines->count(),
                    'active_machines' => $area->machines->where('status', 'online')->count(),
                    'production_today' => $area->production->filter(fn($p) => $p->date->isToday())->sum('material_tonnage') ?? 0,
                    'average_production' => $area->production->avg('material_tonnage') ?? 0,
                ],
                'created_at' => $area->created_at,
                'updated_at' => $area->updated_at,
            ];
        });

        return response()->json($mineAreas);
    }

    /**
     * Get single mine area with full details
     */
    public function show(MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $area = $mineArea->load('machines', 'plans', 'production');

        return response()->json([
            'id' => $area->id,
            'name' => $area->name,
            'type' => $area->type,
            'description' => $area->description,
            'status' => $area->status,
            'coordinates' => $area->coordinates,
            'area_sqm' => $area->area_sqm,
            'perimeter_m' => $area->perimeter_m,
            'created_by' => $area->created_by,
            'created_at' => $area->created_at,
            'updated_at' => $area->updated_at,
            'machines' => $area->machines->map(function ($machine) {
                return [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'model' => $machine->model,
                    'status' => $machine->status,
                    'assigned_at' => $machine->pivot->assigned_at,
                    'notes' => $machine->pivot->notes,
                ];
            }),
            'plans' => $area->plans->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'filename' => $plan->filename,
                    'file_type' => $plan->file_type,
                    'is_current' => $plan->is_current,
                    'version' => $plan->version,
                    'uploaded_at' => $plan->created_at,
                ];
            }),
            'production' => $area->production->map(function ($prod) {
                return [
                    'date' => $prod->date,
                    'material_name' => $prod->material_name,
                    'material_tonnage' => $prod->material_tonnage,
                ];
            }),
            'statistics' => [
                'total_machines' => $area->machines->count(),
                'active_machines' => $area->machines->where('status', 'online')->count(),
                'total_production' => $area->production->sum('material_tonnage') ?? 0,
                'average_production' => $area->production->avg('material_tonnage') ?? 0,
            ],
        ]);
    }

    /**
     * Create new mine area
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pit,stockpile,dump,processing,facility',
            'description' => 'nullable|string',
            'coordinates' => 'required|array',
            'status' => 'in:active,inactive,archived',
        ]);

        $mineArea = $this->service->create(auth()->user()->currentTeam, $validated);

        return response()->json($mineArea, Response::HTTP_CREATED);
    }

    /**
     * Update mine area
     */
    public function update(Request $request, MineArea $mineArea)
    {
        $this->authorize('update', $mineArea);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'in:pit,stockpile,dump,processing,facility',
            'description' => 'nullable|string',
            'coordinates' => 'array',
            'status' => 'in:active,inactive,archived',
        ]);

        $mineArea = $this->service->update($mineArea, $validated);

        return response()->json($mineArea);
    }

    /**
     * Delete mine area
     */
    public function destroy(MineArea $mineArea)
    {
        $this->authorize('delete', $mineArea);

        $this->service->delete($mineArea);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get mine area statistics
     */
    public function statistics(MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $stats = $this->service->getStatistics($mineArea);

        return response()->json($stats);
    }

    /**
     * Assign machines to area
     */
    public function assignMachines(Request $request, MineArea $mineArea)
    {
        $this->authorize('update', $mineArea);

        $validated = $request->validate([
            'machine_ids' => 'required|array',
            'machine_ids.*' => 'exists:machines,id',
            'notes' => 'nullable|string',
        ]);

        $this->service->assignMachines($mineArea, $validated['machine_ids'], $validated['notes'] ?? null);

        return response()->json(['message' => 'Machines assigned successfully']);
    }

    /**
     * Unassign machines from area
     */
    public function unassignMachines(Request $request, MineArea $mineArea)
    {
        $this->authorize('update', $mineArea);

        $validated = $request->validate([
            'machine_ids' => 'required|array',
            'machine_ids.*' => 'exists:machines,id',
        ]);

        $this->service->unassignMachines($mineArea, $validated['machine_ids']);

        return response()->json(['message' => 'Machines unassigned successfully']);
    }

    /**
     * Export mine area as GeoJSON
     */
    public function exportGeoJson(MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $geojson = $this->service->exportGeoJson($mineArea);

        return response()->json($geojson);
    }

    /**
     * Export mine area as CSV
     */
    public function exportCsv(MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $csv = "Name,Type,Status,Area (m²),Perimeter (m),Machines,Active Machines,Total Production\n";
        $stats = $this->service->getStatistics($mineArea);

        $csv .= sprintf(
            "%s,%s,%s,%s,%s,%s,%s,%s\n",
            $mineArea->name,
            $mineArea->type,
            $mineArea->status,
            $mineArea->area_sqm ?? 0,
            $mineArea->perimeter_m ?? 0,
            $stats['total_machines'],
            $stats['active_machines'],
            $stats['total_production']
        );

        return response($csv, Response::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"mine-area-{$mineArea->id}.csv\"",
        ]);
    }

    /**
     * Bulk import mine areas
     */
    public function bulkImport(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:json,csv',
        ]);

        $file = $request->file('file');
        $imported = [];

        if ($file->getClientOriginalExtension() === 'json') {
            $data = json_decode($file->get(), true);
            
            foreach ($data as $area) {
                $created = $this->service->create(auth()->user()->currentTeam, $area);
                $imported[] = $created->id;
            }
        }

        return response()->json([
            'message' => sprintf('%d mine areas imported successfully', count($imported)),
            'imported_ids' => $imported,
        ]);
    }
}
