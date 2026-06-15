<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $units = Unit::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('symbol', 'like', "%{$s}%"))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $units->items(), 'meta' => [
            'current_page' => $units->currentPage(), 'last_page' => $units->lastPage(),
            'per_page' => $units->perPage(), 'total' => $units->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'symbol'    => 'required|string|max:20|unique:units,symbol',
            'type'      => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $unit = Unit::create($validated);
        return response()->json(['success' => true, 'data' => $unit, 'message' => 'Unit created'], 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $unit]);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:100',
            'symbol'    => 'sometimes|string|max:20|unique:units,symbol,' . $unit->id,
            'type'      => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $unit->update($validated);
        return response()->json(['success' => true, 'data' => $unit->fresh(), 'message' => 'Unit updated']);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $unit->delete();
        return response()->json(['success' => true, 'message' => 'Unit deleted']);
    }
}
