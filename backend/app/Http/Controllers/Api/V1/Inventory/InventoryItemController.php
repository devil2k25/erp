<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Tenant\InventoryItem;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): JsonResponse
    {
        $items = InventoryItem::with(['product.unit', 'warehouse'])
            ->when($request->warehouse_id, fn($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->product_id, fn($q, $p) => $q->where('product_id', $p))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function show(InventoryItem $item): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $item->load('product.unit', 'warehouse')]);
    }

    public function stockSummary(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|uuid']);
        return response()->json(['success' => true, 'data' => $this->inventoryService->getStockSummary($request->product_id)]);
    }

    public function lowStockAlerts(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->inventoryService->getLowStockProducts()]);
    }
}
