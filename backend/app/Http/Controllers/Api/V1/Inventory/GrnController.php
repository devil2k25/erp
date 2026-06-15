<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Tenant\GrnHeader;
use App\Services\Inventory\GrnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrnController extends Controller
{
    public function __construct(private GrnService $grnService) {}

    public function index(Request $request): JsonResponse
    {
        $grns = GrnHeader::with(['vendor', 'warehouse'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $grns]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id'          => 'required|uuid|exists:vendors,id',
            'warehouse_id'       => 'required|uuid|exists:warehouses,id',
            'purchase_order_ref' => 'nullable|string',
            'received_date'      => 'nullable|date',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id'        => 'required|uuid|exists:products,id',
            'items.*.quantity_ordered'  => 'required|numeric|min:0.01',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.unit_id'           => 'required|uuid|exists:units,id',
            'items.*.unit_price'        => 'nullable|numeric|min:0',
            'items.*.batch_number'      => 'nullable|string',
            'items.*.expiry_date'       => 'nullable|date',
        ]);

        $grn = $this->grnService->createGrn($validated, $request->user()->id);
        return response()->json(['success' => true, 'data' => $grn, 'message' => 'GRN created'], 201);
    }

    public function show(GrnHeader $grn): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $grn->load('items.product.unit', 'vendor', 'warehouse', 'receiver')]);
    }

    public function update(Request $request, GrnHeader $grn): JsonResponse
    {
        if (!in_array($grn->status, ['draft', 'received'])) {
            return response()->json(['success' => false, 'error' => 'Cannot update approved/rejected GRN'], 422);
        }
        $grn->update($request->only(['notes', 'purchase_order_ref']));
        return response()->json(['success' => true, 'data' => $grn->fresh()]);
    }

    public function destroy(GrnHeader $grn): JsonResponse
    {
        if ($grn->status === 'approved') {
            return response()->json(['success' => false, 'error' => 'Cannot delete approved GRN'], 422);
        }
        $grn->delete();
        return response()->json(['success' => true, 'message' => 'GRN deleted']);
    }

    public function approve(Request $request, GrnHeader $grn): JsonResponse
    {
        try {
            $grn = $this->grnService->approveGrn($grn, $request->user()->id);
            return response()->json(['success' => true, 'data' => $grn, 'message' => 'GRN approved, inventory updated']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
