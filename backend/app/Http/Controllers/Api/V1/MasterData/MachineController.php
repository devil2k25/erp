<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $machines = Machine::with(['productionLine'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->production_line_id, fn($q, $l) => $q->where('production_line_id', $l))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $machines->items(), 'meta' => [
            'current_page' => $machines->currentPage(), 'last_page' => $machines->lastPage(),
            'per_page' => $machines->perPage(), 'total' => $machines->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'required|string|max:50|unique:machines,code',
            'production_line_id' => 'nullable|uuid|exists:production_lines,id',
            'machine_type'       => 'nullable|string|max:100',
            'manufacturer'       => 'nullable|string|max:255',
            'model_number'       => 'nullable|string|max:100',
            'serial_number'      => 'nullable|string|max:100|unique:machines,serial_number',
            'purchase_date'      => 'nullable|date',
            'warranty_expiry'    => 'nullable|date',
            'status'             => 'nullable|in:running,idle,under_maintenance,breakdown,retired',
        ]);

        $machine = Machine::create($validated);
        return response()->json(['success' => true, 'data' => $machine->load('productionLine'), 'message' => 'Machine created'], 201);
    }

    public function show(Machine $machine): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $machine->load('productionLine', 'logs', 'maintenanceSchedules')]);
    }

    public function update(Request $request, Machine $machine): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'code'               => 'sometimes|string|max:50|unique:machines,code,' . $machine->id,
            'production_line_id' => 'nullable|uuid|exists:production_lines,id',
            'machine_type'       => 'nullable|string|max:100',
            'manufacturer'       => 'nullable|string|max:255',
            'model_number'       => 'nullable|string|max:100',
            'serial_number'      => 'nullable|string|max:100|unique:machines,serial_number,' . $machine->id,
            'purchase_date'      => 'nullable|date',
            'warranty_expiry'    => 'nullable|date',
            'status'             => 'nullable|in:running,idle,under_maintenance,breakdown,retired',
            'last_maintenance_at'=> 'nullable|date',
            'next_maintenance_at'=> 'nullable|date',
        ]);

        $machine->update($validated);
        return response()->json(['success' => true, 'data' => $machine->fresh('productionLine'), 'message' => 'Machine updated']);
    }

    public function destroy(Machine $machine): JsonResponse
    {
        $machine->delete();
        return response()->json(['success' => true, 'message' => 'Machine deleted']);
    }
}
