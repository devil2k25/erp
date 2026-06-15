<?php

namespace App\Http\Controllers\Api\V1\Worker;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WorkerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workers = User::with(['department', 'shift', 'roles'])
            ->when($request->department_id, fn($q, $d) => $q->where('department_id', $d))
            ->when($request->search, fn($q, $s) => $q->where(fn($sq) => $sq->where('first_name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $workers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8',
            'phone'         => 'nullable|string',
            'department_id' => 'nullable|uuid|exists:departments,id',
            'shift_id'      => 'nullable|uuid|exists:shifts,id',
            'designation'   => 'nullable|string',
            'employee_code' => 'nullable|string|unique:users,employee_code',
            'role'          => 'nullable|string|exists:roles,name',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $role = $validated['role'] ?? 'worker';
        unset($validated['role']);

        $user = User::create($validated);
        $user->assignRole($role);

        return response()->json(['success' => true, 'data' => $user->load('department', 'shift', 'roles'), 'message' => 'Worker created'], 201);
    }

    public function show(User $worker): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $worker->load('department', 'shift', 'roles')]);
    }

    public function update(Request $request, User $worker): JsonResponse
    {
        $validated = $request->validate([
            'first_name'    => 'sometimes|string',
            'last_name'     => 'sometimes|string',
            'phone'         => 'nullable|string',
            'department_id' => 'nullable|uuid|exists:departments,id',
            'shift_id'      => 'nullable|uuid|exists:shifts,id',
            'designation'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        $worker->update($validated);
        return response()->json(['success' => true, 'data' => $worker->fresh('department', 'shift', 'roles')]);
    }

    public function destroy(User $worker): JsonResponse
    {
        $worker->delete();
        return response()->json(['success' => true, 'message' => 'Worker deactivated']);
    }
}
