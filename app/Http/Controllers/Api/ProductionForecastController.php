<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MineArea;
use App\Models\ProductionForecast;
use App\Services\ProductionForecastService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductionForecastController extends Controller
{
    protected ProductionForecastService $service;

    public function __construct(ProductionForecastService $service)
    {
        $this->service = $service;
    }

    /**
     * Generate forecasts
     */
    public function generate(Request $request, MineArea $mineArea)
    {
        $this->authorize('update', $mineArea);

        $validated = $request->validate([
            'days_ahead' => 'nullable|integer|min:1|max:90',
        ]);

        $forecasts = $this->service->generateForecast($mineArea, $validated['days_ahead'] ?? 7);

        return response()->json([
            'message' => sprintf('%d forecasts generated', $forecasts->count()),
            'forecasts' => $forecasts,
        ]);
    }

    /**
     * Get forecasts for area
     */
    public function index(Request $request, MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $forecasts = ProductionForecast::where('mine_area_id', $mineArea->id)
            ->when($request->has('material'), fn($q) => $q->where('material_name', $request->material))
            ->when($request->has('from_date'), fn($q) => $q->whereDate('forecast_date', '>=', $request->from_date))
            ->when($request->has('to_date'), fn($q) => $q->whereDate('forecast_date', '<=', $request->to_date))
            ->orderBy('forecast_date')
            ->paginate($request->get('per_page', 15));

        return response()->json($forecasts);
    }

    /**
     * Get upcoming forecasts
     */
    public function upcoming(Request $request, MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $days = $request->get('days', 7);
        $forecasts = $this->service->getUpcomingForecasts($mineArea, $days);

        return response()->json($forecasts);
    }

    /**
     * Get forecast accuracy metrics
     */
    public function accuracy(Request $request, MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $days = $request->get('days', 30);
        $metrics = $this->service->getAccuracyMetrics($mineArea, $days);

        return response()->json($metrics);
    }

    /**
     * Get single forecast
     */
    public function show(ProductionForecast $forecast)
    {
        return response()->json($forecast);
    }

    /**
     * Get forecasts by material
     */
    public function byMaterial(Request $request, MineArea $mineArea, string $material)
    {
        $this->authorize('view', $mineArea);

        $forecasts = ProductionForecast::where('mine_area_id', $mineArea->id)
            ->where('material_name', $material)
            ->orderBy('forecast_date')
            ->paginate($request->get('per_page', 20));

        return response()->json($forecasts);
    }

    /**
     * Compare forecast vs actual
     */
    public function comparison(Request $request, MineArea $mineArea)
    {
        $this->authorize('view', $mineArea);

        $days = $request->get('days', 30);
        $material = $request->get('material');

        $forecasts = ProductionForecast::where('mine_area_id', $mineArea->id)
            ->when($material, fn($q) => $q->where('material_name', $material))
            ->whereDate('forecast_date', '>=', now()->subDays($days))
            ->orderBy('forecast_date')
            ->get();

        $comparison = $forecasts->map(function ($forecast) {
            $actual = $forecast->mineArea->production()
                ->where('material_name', $forecast->material_name)
                ->whereDate('date', $forecast->forecast_date)
                ->first();

            return [
                'forecast_date' => $forecast->forecast_date,
                'material' => $forecast->material_name,
                'predicted' => $forecast->predicted_tonnage,
                'actual' => $actual?->material_tonnage ?? null,
                'confidence' => $forecast->confidence_score,
                'error' => $actual ? abs($forecast->predicted_tonnage - $actual->material_tonnage) : null,
            ];
        });

        return response()->json($comparison);
    }
}
