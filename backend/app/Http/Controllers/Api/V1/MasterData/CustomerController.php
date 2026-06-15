<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $customers->items(), 'meta' => [
            'current_page' => $customers->currentPage(), 'last_page' => $customers->lastPage(),
            'per_page' => $customers->perPage(), 'total' => $customers->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'nullable|string|max:50|unique:customers,code',
            'contact_person'     => 'nullable|string|max:255',
            'email'              => 'nullable|email|unique:customers,email',
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'gst_number'         => 'nullable|string|max:20',
            'credit_limit'       => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'is_active'          => 'boolean',
        ]);

        $customer = Customer::create($validated);
        return response()->json(['success' => true, 'data' => $customer, 'message' => 'Customer created'], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $customer]);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'code'               => 'nullable|string|max:50|unique:customers,code,' . $customer->id,
            'contact_person'     => 'nullable|string|max:255',
            'email'              => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'gst_number'         => 'nullable|string|max:20',
            'credit_limit'       => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'is_active'          => 'boolean',
        ]);

        $customer->update($validated);
        return response()->json(['success' => true, 'data' => $customer->fresh(), 'message' => 'Customer updated']);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Customer deleted']);
    }
}
