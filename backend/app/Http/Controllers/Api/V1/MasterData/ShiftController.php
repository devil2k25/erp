<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shifts = Shift::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $shifts->items(), 'meta' => [
            'current_page' => $shifts->currentPage(), 'last_page' => $shifts->lastPage(),
            'per_page' => $shifts->perPage(), 'total' => $shifts->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'code'                 => 'required|string|max:20|unique:shifts,code',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i',
            'break_duration_mins'  => 'nullable|integer|min:0',
            'is_active'            => 'boolean',
        ]);

        $shift = Shift::create($validated);
        return response()->json(['success' => true, 'data' => $shift, 'message' => 'Shift created'], 201);
    }

    public function show(Shift $shift): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $shift]);
    }

    public function update(Request $request, Shift $shift): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => 'sometimes|string|max:100',
            'code'                 => 'sometimes|string|max:20|unique:shifts,code,' . $shift->id,
            'start_time'           => 'sometimes|date_format:H:i',
            'end_time'             => 'sometimes|date_format:H:i',
            'break_duration_mins'  => 'nullable|integer|min:0',
            'is_active'            => 'boolean',
        ]);

        $shift->update($validated);
        return response()->json(['success' => true, 'data' => $shift->fresh(), 'message' => 'Shift updated']);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $shift->delete();
        return response()->json(['success' => true, 'message' => 'Shift deleted']);
    }
}
