<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $warehouses = Warehouse::with(['manager', 'capacityUnit'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $warehouses->items(), 'meta' => [
            'current_page' => $warehouses->currentPage(), 'last_page' => $warehouses->lastPage(),
            'per_page' => $warehouses->perPage(), 'total' => $warehouses->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:50|unique:warehouses,code',
            'type'             => 'nullable|string|max:50',
            'address'          => 'nullable|string',
            'capacity'         => 'nullable|numeric|min:0',
            'capacity_unit_id' => 'nullable|uuid|exists:units,id',
            'manager_id'       => 'nullable|uuid|exists:users,id',
            'is_active'        => 'boolean',
        ]);

        $warehouse = Warehouse::create($validated);
        return response()->json(['success' => true, 'data' => $warehouse->load('manager', 'capacityUnit'), 'message' => 'Warehouse created'], 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $warehouse->load('manager', 'capacityUnit', 'inventoryItems.product')]);
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'code'             => 'sometimes|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'type'             => 'nullable|string|max:50',
            'address'          => 'nullable|string',
            'capacity'         => 'nullable|numeric|min:0',
            'capacity_unit_id' => 'nullable|uuid|exists:units,id',
            'manager_id'       => 'nullable|uuid|exists:users,id',
            'is_active'        => 'boolean',
        ]);

        $warehouse->update($validated);
        return response()->json(['success' => true, 'data' => $warehouse->fresh('manager', 'capacityUnit'), 'message' => 'Warehouse updated']);
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();
        return response()->json(['success' => true, 'message' => 'Warehouse deleted']);
    }
}
