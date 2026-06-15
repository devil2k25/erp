<?php

namespace App\Services\Production;

use App\Models\Tenant\ProductionPlan;
use App\Models\Tenant\InventoryItem;
use Illuminate\Support\Str;

class ProductionPlanService
{
    public function create(array $data, string $userId): ProductionPlan
    {
        $data['plan_number'] = 'PLAN-' . strtoupper(Str::random(8));
        $data['created_by'] = $userId;
        $data['status'] = 'draft';

        return ProductionPlan::create($data);
    }

    public function approve(ProductionPlan $plan, string $userId): ProductionPlan
    {
        if ($plan->status !== 'draft') {
            throw new \RuntimeException('Only draft plans can be approved');
        }

        $rmRequirements = $this->calculateRmRequirements($plan);
        $shortages = $this->checkInventoryAvailability($rmRequirements);

        if (!empty($shortages)) {
            throw new \RuntimeException('Insufficient raw material: ' . implode(', ', array_keys($shortages)));
        }

        $plan->update(['status' => 'approved', 'approved_by' => $userId]);
        return $plan->fresh();
    }

    public function calculateRmRequirements(ProductionPlan $plan): array
    {
        $requirements = [];
        $bomItems = $plan->product->bomItems()->with('component')->get();

        foreach ($bomItems as $bom) {
            $required = $bom->quantity * $plan->planned_quantity * (1 + $bom->scrap_percentage / 100);
            $requirements[$bom->component_product_id] = [
                'product'  => $bom->component,
                'required' => $required,
                'unit_id'  => $bom->unit_id,
            ];
        }

        return $requirements;
    }

    private function checkInventoryAvailability(array $requirements): array
    {
        $shortages = [];
        foreach ($requirements as $productId => $req) {
            $available = InventoryItem::where('product_id', $productId)->sum('quantity_on_hand');
            if ($available < $req['required']) {
                $shortages[$req['product']->name] = [
                    'required'  => $req['required'],
                    'available' => $available,
                    'shortage'  => $req['required'] - $available,
                ];
            }
        }
        return $shortages;
    }
}
