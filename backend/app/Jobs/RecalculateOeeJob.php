<?php

namespace App\Jobs;

use App\Models\Master\TenantDatabase;
use App\Models\Tenant\ProductionEntry;
use App\Services\Production\OeeCalculatorService;
use App\Services\TenantService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateOeeJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $entryId,
        public string $organizationSlug
    ) {}

    public function handle(OeeCalculatorService $oeeCalculator, TenantService $tenantService): void
    {
        $tenantDb = TenantDatabase::whereHas('organization', fn($q) => $q->where('slug', $this->organizationSlug))->first();

        if (!$tenantDb) return;

        $tenantService->connectToTenant($tenantDb);

        $entry = ProductionEntry::find($this->entryId);
        if ($entry) {
            $oeeCalculator->updateEntry($entry);
        }
    }
}
