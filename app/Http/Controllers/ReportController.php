<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MineArea;
use App\Models\Geofence;
use App\Models\Machine;

class ReportController extends Controller
{
    public function view2(Request $request)
    {
        $user = Auth::user();

        if ($user && isset($user->currentTeam->id)) {
            $teamId = $user->currentTeam->id;
            $mineAreas = MineArea::where('team_id', $teamId)->get();
            $geofences = Geofence::whereIn('mine_area_id', $mineAreas->pluck('id'))->get();
            $machines = Machine::where('team_id', $teamId)->get();
        } else {
            $mineAreas = MineArea::all();
            $geofences = Geofence::all();
            $machines = Machine::all();
        }

        return view('reports.view-2', compact('mineAreas','geofences','machines'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'mine_area_id' => 'nullable|exists:mine_areas,id',
            'geofence_id' => 'nullable|exists:geofences,id',
            'machine_id' => 'nullable|exists:machines,id'
        ]);

        // Placeholder: implement actual report generation logic here.
        // For now, redirect back with a flash message and the selected scope.
        return back()->with('status', 'Report generation requested')->with('report_scope', $data);
    }
}
