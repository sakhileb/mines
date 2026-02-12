<?php

namespace App\Services;

use App\Models\ProductionRecord;
use App\Models\ProductionTarget;
use App\Models\ProductionForecast;
use Carbon\Carbon;

class ProductionService
{
    public function getProductionByTeam($teamId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        return ProductionRecord::forTeam($teamId)
            ->betweenDates($startDate, $endDate)
            ->orderByDesc('record_date')
            ->paginate(15);
    }

    public function getTodayProduction($teamId)
    {
        return ProductionRecord::forTeam($teamId)
            ->where('record_date', Carbon::today())
            ->get();
    }

    public function getProductionStatistics($teamId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $records = ProductionRecord::forTeam($teamId)
            ->betweenDates($startDate, $endDate)
            ->get();

        $totalProduced = $records->sum('quantity_produced');
        $totalTarget = $records->sum('target_quantity');
        $recordCount = $records->count();
        $avgProduction = $recordCount > 0 ? $totalProduced / $recordCount : 0;
        $completedCount = $records->where('status', 'completed')->count();

        return [
            'total_produced' => $totalProduced,
            'total_target' => $totalTarget,
            'achievement_rate' => $totalTarget > 0 ? ($totalProduced / $totalTarget) * 100 : 0,
            'average_daily_production' => $avgProduction,
            'total_records' => $recordCount,
            'completed_records' => $completedCount,
            'pending_records' => $recordCount - $completedCount,
            'above_target_count' => $records->where('is_above_target', true)->count(),
            'below_target_count' => $records->where('is_above_target', false)->count(),
        ];
    }

    public function createProductionRecord($teamId, $data)
    {
        return ProductionRecord::create([
            'team_id' => $teamId,
            'mine_area_id' => $data['mine_area_id'] ?? null,
            'machine_id' => $data['machine_id'] ?? null,
            'record_date' => $data['record_date'],
            'shift' => $data['shift'] ?? 'day',
            'quantity_produced' => $data['quantity_produced'],
            'unit' => $data['unit'] ?? 'tonnes',
            'target_quantity' => $data['target_quantity'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'completed',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function updateProductionRecord(ProductionRecord $record, $data)
    {
        $record->update($data);
        return $record;
    }

    public function deleteProductionRecord(ProductionRecord $record)
    {
        return $record->delete();
    }

    public function getActiveTargets($teamId)
    {
        return ProductionTarget::forTeam($teamId)
            ->active()
            ->where('end_date', '>=', Carbon::today())
            ->get();
    }

    public function createTarget($teamId, $data)
    {
        return ProductionTarget::create([
            'team_id' => $teamId,
            'mine_area_id' => $data['mine_area_id'] ?? null,
            'period_type' => $data['period_type'] ?? 'daily',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'target_quantity' => $data['target_quantity'],
            'unit' => $data['unit'] ?? 'tonnes',
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function getProductionTrend($teamId, $days = 30)
    {
        $records = ProductionRecord::forTeam($teamId)
            ->where('record_date', '>=', Carbon::now()->subDays($days))
            ->orderBy('record_date')
            ->get()
            ->groupBy('record_date');

        return $records->map(function ($dayRecords) {
            return [
                'date' => $dayRecords->first()->record_date->format('Y-m-d'),
                'produced' => $dayRecords->sum('quantity_produced'),
                'target' => $dayRecords->sum('target_quantity'),
                'count' => $dayRecords->count(),
            ];
        });
    }

    public function getProductionByMineArea($teamId)
    {
        $records = ProductionRecord::forTeam($teamId)
            ->where('record_date', '>=', Carbon::now()->subDays(30))
            ->with('mineArea')
            ->get();

        return $records->groupBy('mine_area_id')->map(function ($areaRecords) {
            $area = $areaRecords->first()?->mineArea;
            return [
                'mine_area_id' => $area?->id,
                'mine_area_name' => $area?->name ?? 'Unknown',
                'total_produced' => $areaRecords->sum('quantity_produced'),
                'total_target' => $areaRecords->sum('target_quantity'),
                'record_count' => $areaRecords->count(),
            ];
        });
    }

    public function getRecentForecasts($teamId, $days = 7)
    {
        return ProductionForecast::forTeam($teamId)
            ->where('forecast_date', '>=', Carbon::now())
            ->where('forecast_date', '<=', Carbon::now()->addDays($days))
            ->orderBy('forecast_date')
            ->get();
    }
}
