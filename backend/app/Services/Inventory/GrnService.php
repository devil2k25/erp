<?php

namespace App\Services\Inventory;

use App\Models\Tenant\GrnHeader;
use App\Models\Tenant\GrnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrnService
{
    public function __construct(private InventoryService $inventoryService) {}

    public function createGrn(array $data, string $userId): GrnHeader
    {
        return DB::connection('tenant')->transaction(function () use ($data, $userId) {
            $grn = GrnHeader::create([
                'grn_number'   => 'GRN-' . strtoupper(Str::random(8)),
                'vendor_id'    => $data['vendor_id'],
                'received_by'  => $userId,
                'warehouse_id' => $data['warehouse_id'],
                'received_date' => $data['received_date'] ?? today(),
                'purchase_order_ref' => $data['purchase_order_ref'] ?? null,
                'notes'        => $data['notes'] ?? null,
                'status'       => 'received',
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                GrnItem::create(array_merge($item, ['grn_header_id' => $grn->id]));
                $total += ($item['quantity_received'] ?? 0) * ($item['unit_price'] ?? 0);
            }

            $grn->update(['total_amount' => $total]);

            return $grn->load('items.product', 'vendor');
        });
    }

    public function approveGrn(GrnHeader $grn, string $userId): GrnHeader
    {
        if ($grn->status !== 'received') {
            throw new \RuntimeException('GRN must be in received status to approve');
        }

        return DB::connection('tenant')->transaction(function () use ($grn, $userId) {
            foreach ($grn->items as $item) {
                if ($item->qc_status === 'passed' || $item->qc_status === 'pending') {
                    $this->inventoryService->updateStock(
                        productId: $item->product_id,
                        warehouseId: $grn->warehouse_id,
                        quantity: $item->quantity_received,
                        transactionType: 'grn',
                        performedBy: $userId,
                        options: [
                            'reference_type' => 'GrnHeader',
                            'reference_id'   => $grn->id,
                            'unit_id'        => $item->unit_id,
                            'unit_cost'      => $item->unit_price,
                            'batch_number'   => $item->batch_number,
                        ]
                    );
                }
            }

            $grn->update(['status' => 'approved']);
            return $grn->fresh();
        });
    }
}
