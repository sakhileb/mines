<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MinePlan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MinePlanController extends Controller
{
    /**
     * Get all plans for a mine area
     */
    public function index(Request $request)
    {
        $mineAreaId = $request->route('mineArea');
        
        $plans = MinePlan::where('mine_area_id', $mineAreaId)
            ->with('uploader')
            ->orderBy('version', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($plans);
    }

    /**
     * Get single plan
     */
    public function show(MinePlan $plan)
    {
        return response()->json([
            'id' => $plan->id,
            'mine_area_id' => $plan->mine_area_id,
            'filename' => $plan->filename,
            'file_type' => $plan->file_type,
            'file_size' => $plan->file_size,
            'version' => $plan->version,
            'is_current' => $plan->is_current,
            'is_archived' => $plan->is_archived,
            'georeferencing' => $plan->georeferencing,
            'uploaded_by' => $plan->uploader->name,
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at,
        ]);
    }

    /**
     * Upload new plan version
     */
    public function store(Request $request)
    {
        $mineAreaId = $request->route('mineArea');
        $mineArea = \App\Models\MineArea::findOrFail($mineAreaId);
        $this->authorize('update', $mineArea);
        
        $validated = $request->validate([
            'file' => 'required|file|max:102400|mimes:pdf,dwg,dxf,png,jpg',
            'description' => 'nullable|string',
            'georeferencing' => 'nullable|json',
        ]);
        $file = $request->file('file');

        // Store file
        $path = Storage::disk('private')->putFile("mine-plans/{$mineAreaId}", $file);

        // Create plan record
        $plan = MinePlan::create([
            'mine_area_id' => $mineAreaId,
            'filename' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'file_path' => $path,
            'version' => MinePlan::where('mine_area_id', $mineAreaId)->max('version') + 1,
            'is_current' => true,
            'georeferencing' => $validated['georeferencing'] ?? null,
            'uploaded_by' => auth()->id(),
            'description' => $validated['description'] ?? null,
        ]);

        // Unset previous current version
        MinePlan::where('mine_area_id', $mineAreaId)
            ->where('id', '!=', $plan->id)
            ->update(['is_current' => false]);

        return response()->json($plan, Response::HTTP_CREATED);
    }

    /**
     * Delete plan
     */
    public function destroy(MinePlan $plan)
    {
        // Move to archive instead of delete
        $plan->update(['is_archived' => true]);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Download plan
     */
    public function download(MinePlan $plan)
    {
        return Storage::disk('private')->download($plan->file_path, $plan->filename);
    }

    /**
     * Set as current version
     */
    public function setAsCurrent(MinePlan $plan)
    {
        MinePlan::where('mine_area_id', $plan->mine_area_id)
            ->update(['is_current' => false]);

        $plan->update(['is_current' => true]);

        return response()->json(['message' => 'Plan set as current']);
    }
}
