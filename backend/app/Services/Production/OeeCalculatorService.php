<?php

namespace App\Services\Production;

use App\Models\Tenant\ProductionEntry;

class OeeCalculatorService
{
    public function calculate(ProductionEntry $entry): array
    {
        $plannedMinutes = $entry->start_time && $entry->end_time
            ? $entry->start_time->diffInMinutes($entry->end_time)
            : 480;

        $availableMinutes = max(0, $plannedMinutes - $entry->downtime_minutes);
        $availability = $plannedMinutes > 0 ? ($availableMinutes / $plannedMinutes) * 100 : 0;

        $theoreticalOutput = $entry->planned_quantity > 0 ? $entry->planned_quantity : 1;
        $totalOutput = $entry->produced_quantity + $entry->rejected_quantity;
        $performance = $theoreticalOutput > 0 ? min(100, ($totalOutput / $theoreticalOutput) * 100) : 0;

        $goodOutput = $entry->produced_quantity - $entry->rework_quantity;
        $quality = $totalOutput > 0 ? ($goodOutput / $totalOutput) * 100 : 0;

        $oee = ($availability / 100) * ($performance / 100) * ($quality / 100) * 100;

        return [
            'oee_availability' => round($availability, 2),
            'oee_performance'  => round($performance, 2),
            'oee_quality'      => round($quality, 2),
            'oee_score'        => round($oee, 2),
        ];
    }

    public function updateEntry(ProductionEntry $entry): void
    {
        $metrics = $this->calculate($entry);
        $entry->update($metrics);
    }
}
