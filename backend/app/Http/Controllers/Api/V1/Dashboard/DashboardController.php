<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardMetricsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metricsService) {}

    public function owner(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->metricsService->ownerMetrics()]);
    }

    public function production(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->metricsService->productionMetrics()]);
    }

    public function inventory(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->metricsService->inventoryMetrics()]);
    }
}
