<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ProductionLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionLineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lines = ProductionLine::with(['warehouse', 'department'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->warehouse_id, fn($q, $w) => $q->where('warehouse_id', $w))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $lines->items(), 'meta' => [
            'current_page' => $lines->currentPage(), 'last_page' => $lines->lastPage(),
            'per_page' => $lines->perPage(), 'total' => $lines->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'required|string|max:50|unique:production_lines,code',
            'warehouse_id'       => 'nullable|uuid|exists:warehouses,id',
            'department_id'      => 'nullable|uuid|exists:departments,id',
            'capacity_per_hour'  => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:active,inactive,under_maintenance',
            'description'        => 'nullable|string',
        ]);

        $line = ProductionLine::create($validated);
        return response()->json(['success' => true, 'data' => $line->load('warehouse', 'department'), 'message' => 'Production line created'], 201);
    }

    public function show(ProductionLine $productionLine): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $productionLine->load('warehouse', 'department', 'machines')]);
    }

    public function update(Request $request, ProductionLine $productionLine): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'code'               => 'sometimes|string|max:50|unique:production_lines,code,' . $productionLine->id,
            'warehouse_id'       => 'nullable|uuid|exists:warehouses,id',
            'department_id'      => 'nullable|uuid|exists:departments,id',
            'capacity_per_hour'  => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:active,inactive,under_maintenance',
            'description'        => 'nullable|string',
        ]);

        $productionLine->update($validated);
        return response()->json(['success' => true, 'data' => $productionLine->fresh('warehouse', 'department'), 'message' => 'Production line updated']);
    }

    public function destroy(ProductionLine $productionLine): JsonResponse
    {
        $productionLine->delete();
        return response()->json(['success' => true, 'message' => 'Production line deleted']);
    }
}
