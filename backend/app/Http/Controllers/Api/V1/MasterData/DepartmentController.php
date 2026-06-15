<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $departments = Department::with(['manager'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $departments->items(), 'meta' => [
            'current_page' => $departments->currentPage(), 'last_page' => $departments->lastPage(),
            'per_page' => $departments->perPage(), 'total' => $departments->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|uuid|exists:users,id',
            'is_active'   => 'boolean',
        ]);

        $department = Department::create($validated);
        return response()->json(['success' => true, 'data' => $department->load('manager'), 'message' => 'Department created'], 201);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $department->load('manager', 'users')]);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'code'        => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|uuid|exists:users,id',
            'is_active'   => 'boolean',
        ]);

        $department->update($validated);
        return response()->json(['success' => true, 'data' => $department->fresh('manager'), 'message' => 'Department updated']);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();
        return response()->json(['success' => true, 'message' => 'Department deleted']);
    }
}
