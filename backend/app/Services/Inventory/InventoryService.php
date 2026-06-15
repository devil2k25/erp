<?php

namespace App\Services\Inventory;

use App\Models\Tenant\InventoryItem;
use App\Models\Tenant\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    public function updateStock(
        string $productId,
        string $warehouseId,
        float $quantity,
        string $transactionType,
        string $performedBy,
        array $options = []
    ): InventoryTransaction {
        return DB::connection('tenant')->transaction(function () use ($productId, $warehouseId, $quantity, $transactionType, $performedBy, $options) {
            $inventoryItem = InventoryItem::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId, 'batch_number' => $options['batch_number'] ?? null],
                ['quantity_on_hand' => 0, 'quantity_reserved' => 0]
            );

            $inventoryItem->increment('quantity_on_hand', $quantity);

            return InventoryTransaction::create([
                'transaction_number' => 'TXN-' . strtoupper(Str::random(10)),
                'product_id'         => $productId,
                'warehouse_id'       => $warehouseId,
                'transaction_type'   => $transactionType,
                'reference_type'     => $options['reference_type'] ?? null,
                'reference_id'       => $options['reference_id'] ?? null,
                'quantity'           => $quantity,
                'unit_id'            => $options['unit_id'] ?? null,
                'unit_cost'          => $options['unit_cost'] ?? null,
                'batch_number'       => $options['batch_number'] ?? null,
                'performed_by'       => $performedBy,
                'transaction_date'   => now(),
                'notes'              => $options['notes'] ?? null,
                'created_at'         => now(),
            ]);
        });
    }

    public function getLowStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Tenant\Product::whereColumn('min_stock_level', '>', DB::raw('(
            SELECT COALESCE(SUM(quantity_on_hand), 0) FROM inventory_items
            WHERE inventory_items.product_id = products.id
        )'))->with(['unit', 'category'])->get();
    }

    public function getStockSummary(string $productId): array
    {
        $items = InventoryItem::where('product_id', $productId)->with('warehouse')->get();
        return [
            'total_on_hand'  => $items->sum('quantity_on_hand'),
            'total_reserved' => $items->sum('quantity_reserved'),
            'total_available' => $items->sum(fn($i) => max(0, $i->quantity_on_hand - $i->quantity_reserved)),
            'by_warehouse'   => $items->map(fn($i) => [
                'warehouse' => $i->warehouse->name,
                'on_hand'   => $i->quantity_on_hand,
                'reserved'  => $i->quantity_reserved,
                'batch'     => $i->batch_number,
            ]),
        ];
    }
}
