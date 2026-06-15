<?php

namespace App\Http\Controllers\Api\V1\Production;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateOeeJob;
use App\Models\Tenant\ProductionEntry;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionEntryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): JsonResponse
    {
        $entries = ProductionEntry::with(['plan.product', 'shift', 'machine', 'operator'])
            ->when($request->plan_id, fn($q, $p) => $q->where('production_plan_id', $p))
            ->when($request->date, fn($q, $d) => $q->whereDate('entry_date', $d))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $entries]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_plan_id' => 'required|uuid|exists:production_plans,id',
            'shift_id'           => 'required|uuid|exists:shifts,id',
            'machine_id'         => 'nullable|uuid|exists:machines,id',
            'entry_date'         => 'required|date',
            'start_time'         => 'required|date',
            'end_time'           => 'nullable|date|after:start_time',
            'planned_quantity'   => 'required|numeric|min:0',
            'produced_quantity'  => 'required|numeric|min:0',
            'rejected_quantity'  => 'nullable|numeric|min:0',
            'rework_quantity'    => 'nullable|numeric|min:0',
            'downtime_minutes'   => 'nullable|integer|min:0',
            'downtime_reason'    => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        $validated['operator_id'] = $request->user()->id;

        $entry = DB::connection('tenant')->transaction(function () use ($validated) {
            return ProductionEntry::create($validated);
        });

        $orgSlug = app('currentTenant')->slug;
        RecalculateOeeJob::dispatch($entry->id, $orgSlug)->onQueue('default');

        return response()->json(['success' => true, 'data' => $entry->load('plan.product', 'shift'), 'message' => 'Production entry recorded'], 201);
    }

    public function show(ProductionEntry $entry): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $entry->load('plan.product', 'shift', 'machine', 'operator')]);
    }

    public function update(Request $request, ProductionEntry $entry): JsonResponse
    {
        $entry->update($request->only(['produced_quantity', 'rejected_quantity', 'rework_quantity', 'downtime_minutes', 'downtime_reason', 'end_time', 'notes']));
        RecalculateOeeJob::dispatch($entry->id, app('currentTenant')->slug)->onQueue('default');
        return response()->json(['success' => true, 'data' => $entry->fresh(), 'message' => 'Entry updated']);
    }

    public function destroy(ProductionEntry $entry): JsonResponse
    {
        $entry->delete();
        return response()->json(['success' => true, 'message' => 'Entry deleted']);
    }

    public function oeeSummary(Request $request): JsonResponse
    {
        $date = $request->date ?? today()->toDateString();
        $summary = ProductionEntry::whereDate('entry_date', $date)
            ->selectRaw('AVG(oee_score) as avg_oee, AVG(oee_availability) as avg_availability, AVG(oee_performance) as avg_performance, AVG(oee_quality) as avg_quality, SUM(produced_quantity) as total_produced, SUM(rejected_quantity) as total_rejected, SUM(downtime_minutes) as total_downtime')
            ->first();

        return response()->json(['success' => true, 'data' => $summary]);
    }
}
