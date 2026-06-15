<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Tenant\InventoryTransaction;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryTransactionController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): JsonResponse
    {
        $txns = InventoryTransaction::with(['product', 'warehouse', 'performer'])
            ->when($request->product_id, fn($q, $p) => $q->where('product_id', $p))
            ->when($request->type, fn($q, $t) => $q->where('transaction_type', $t))
            ->orderBy('transaction_date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $txns]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'       => 'required|uuid|exists:products,id',
            'warehouse_id'     => 'required|uuid|exists:warehouses,id',
            'transaction_type' => 'required|in:adjustment,transfer_in,transfer_out,scrap,return',
            'quantity'         => 'required|numeric',
            'unit_id'          => 'required|uuid|exists:units,id',
            'notes'            => 'nullable|string',
        ]);

        $txn = $this->inventoryService->updateStock(
            productId: $validated['product_id'],
            warehouseId: $validated['warehouse_id'],
            quantity: $validated['quantity'],
            transactionType: $validated['transaction_type'],
            performedBy: $request->user()->id,
            options: ['unit_id' => $validated['unit_id'], 'notes' => $validated['notes'] ?? null]
        );

        return response()->json(['success' => true, 'data' => $txn, 'message' => 'Transaction recorded'], 201);
    }

    public function show(InventoryTransaction $transaction): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $transaction->load('product', 'warehouse', 'performer')]);
    }
}
