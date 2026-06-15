<?php

namespace App\Services\Dashboard;

use App\Models\Tenant\ProductionEntry;
use App\Models\Tenant\ProductionPlan;
use App\Models\Tenant\WorkerAttendance;
use App\Models\Tenant\InventoryItem;
use App\Models\Tenant\GrnHeader;
use App\Models\Tenant\Machine;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    public function ownerMetrics(): array
    {
        $today = today();
        $monthStart = $today->copy()->startOfMonth();

        return [
            'production_summary' => [
                'today_planned'  => ProductionPlan::whereDate('planned_start_date', $today)->sum('planned_quantity'),
                'today_actual'   => ProductionEntry::whereDate('entry_date', $today)->sum('produced_quantity'),
                'month_planned'  => ProductionPlan::whereBetween('planned_start_date', [$monthStart, $today])->sum('planned_quantity'),
                'month_actual'   => ProductionEntry::whereBetween('entry_date', [$monthStart, $today])->sum('produced_quantity'),
            ],
            'oee_summary' => [
                'avg_oee'          => ProductionEntry::whereDate('entry_date', $today)->avg('oee_score') ?? 0,
                'avg_availability' => ProductionEntry::whereDate('entry_date', $today)->avg('oee_availability') ?? 0,
            ],
            'machine_status' => Machine::select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
            'inventory_value' => InventoryItem::join('products', 'inventory_items.product_id', '=', 'products.id')
                ->sum(DB::raw('inventory_items.quantity_on_hand * products.cost_price')),
            'worker_summary' => [
                'present_today' => WorkerAttendance::whereDate('attendance_date', $today)->where('status', 'present')->count(),
                'absent_today'  => WorkerAttendance::whereDate('attendance_date', $today)->where('status', 'absent')->count(),
            ],
        ];
    }

    public function productionMetrics(): array
    {
        $today = today();
        return [
            'active_plans'   => ProductionPlan::where('status', 'in_progress')->with('product', 'productionLine')->get(),
            'today_entries'  => ProductionEntry::whereDate('entry_date', $today)->with('plan.product', 'shift', 'machine')->get(),
            'oee_by_line'    => ProductionEntry::whereDate('entry_date', $today)
                ->join('production_plans', 'production_entries.production_plan_id', '=', 'production_plans.id')
                ->join('production_lines', 'production_plans.production_line_id', '=', 'production_lines.id')
                ->select('production_lines.name', DB::raw('AVG(oee_score) as avg_oee'))
                ->groupBy('production_lines.id', 'production_lines.name')
                ->get(),
            'downtime_reasons' => ProductionEntry::whereDate('entry_date', $today)
                ->whereNotNull('downtime_reason')
                ->select('downtime_reason', DB::raw('SUM(downtime_minutes) as total_minutes'))
                ->groupBy('downtime_reason')
                ->orderByDesc('total_minutes')
                ->limit(5)
                ->get(),
        ];
    }

    public function inventoryMetrics(): array
    {
        return [
            'low_stock_count' => \App\Models\Tenant\Product::whereColumn('reorder_point', '>=',
                DB::raw('(SELECT COALESCE(SUM(quantity_on_hand), 0) FROM inventory_items WHERE inventory_items.product_id = products.id)')
            )->count(),
            'pending_grns'    => GrnHeader::where('status', 'received')->count(),
            'recent_grns'     => GrnHeader::with('vendor')->latest()->limit(5)->get(),
            'stock_by_warehouse' => InventoryItem::join('warehouses', 'inventory_items.warehouse_id', '=', 'warehouses.id')
                ->select('warehouses.name', DB::raw('COUNT(DISTINCT product_id) as product_count, SUM(quantity_on_hand) as total_qty'))
                ->groupBy('warehouses.id', 'warehouses.name')
                ->get(),
        ];
    }
}
