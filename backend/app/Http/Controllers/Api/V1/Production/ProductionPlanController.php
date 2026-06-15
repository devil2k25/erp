<?php

namespace App\Http\Controllers\Api\V1\Production;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ProductionPlan;
use App\Services\Production\ProductionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionPlanController extends Controller
{
    public function __construct(private ProductionPlanService $planService) {}

    public function index(Request $request): JsonResponse
    {
        $plans = ProductionPlan::with(['product', 'productionLine', 'creator'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->from_date, fn($q, $d) => $q->whereDate('planned_start_date', '>=', $d))
            ->when($request->to_date, fn($q, $d) => $q->whereDate('planned_end_date', '<=', $d))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $plans]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'         => 'required|uuid|exists:products,id',
            'production_line_id' => 'required|uuid|exists:production_lines,id',
            'planned_quantity'   => 'required|numeric|min:0.01',
            'planned_start_date' => 'required|date',
            'planned_end_date'   => 'required|date|after:planned_start_date',
            'priority'           => 'nullable|in:low,medium,high,urgent',
            'batch_number'       => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        $plan = $this->planService->create($validated, $request->user()->id);
        return response()->json(['success' => true, 'data' => $plan->load('product', 'productionLine'), 'message' => 'Production plan created'], 201);
    }

    public function show(ProductionPlan $plan): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $plan->load('product.bomItems.component', 'productionLine', 'entries', 'creator', 'approver')]);
    }

    public function update(Request $request, ProductionPlan $plan): JsonResponse
    {
        if (!in_array($plan->status, ['draft'])) {
            return response()->json(['success' => false, 'error' => 'Only draft plans can be edited'], 422);
        }
        $plan->update($request->only(['planned_quantity', 'planned_start_date', 'planned_end_date', 'priority', 'notes']));
        return response()->json(['success' => true, 'data' => $plan->fresh()]);
    }

    public function destroy(ProductionPlan $plan): JsonResponse
    {
        if (!in_array($plan->status, ['draft', 'cancelled'])) {
            return response()->json(['success' => false, 'error' => 'Cannot delete active plans'], 422);
        }
        $plan->delete();
        return response()->json(['success' => true, 'message' => 'Plan deleted']);
    }

    public function approve(Request $request, ProductionPlan $plan): JsonResponse
    {
        try {
            $plan = $this->planService->approve($plan, $request->user()->id);
            return response()->json(['success' => true, 'data' => $plan, 'message' => 'Plan approved']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function start(Request $request, ProductionPlan $plan): JsonResponse
    {
        if ($plan->status !== 'approved') {
            return response()->json(['success' => false, 'error' => 'Plan must be approved to start'], 422);
        }
        $plan->update(['status' => 'in_progress', 'actual_start_date' => now()]);
        return response()->json(['success' => true, 'data' => $plan->fresh(), 'message' => 'Production started']);
    }
}
