<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendors = Vendor::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $vendors->items(), 'meta' => [
            'current_page' => $vendors->currentPage(), 'last_page' => $vendors->lastPage(),
            'per_page' => $vendors->perPage(), 'total' => $vendors->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'nullable|string|max:50|unique:vendors,code',
            'contact_person'     => 'nullable|string|max:255',
            'email'              => 'nullable|email|unique:vendors,email',
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'gst_number'         => 'nullable|string|max:20',
            'payment_terms_days' => 'nullable|integer|min:0',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'is_active'          => 'boolean',
        ]);

        $vendor = Vendor::create($validated);
        return response()->json(['success' => true, 'data' => $vendor, 'message' => 'Vendor created'], 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $vendor]);
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'code'               => 'nullable|string|max:50|unique:vendors,code,' . $vendor->id,
            'contact_person'     => 'nullable|string|max:255',
            'email'              => 'nullable|email|unique:vendors,email,' . $vendor->id,
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'gst_number'         => 'nullable|string|max:20',
            'payment_terms_days' => 'nullable|integer|min:0',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'is_active'          => 'boolean',
        ]);

        $vendor->update($validated);
        return response()->json(['success' => true, 'data' => $vendor->fresh(), 'message' => 'Vendor updated']);
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $vendor->delete();
        return response()->json(['success' => true, 'message' => 'Vendor deleted']);
    }
}
