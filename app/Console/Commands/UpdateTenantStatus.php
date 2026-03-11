<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Tenancy;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateTenantStatus extends Command
{
    protected $signature = 'tenants:update-status';

    protected $description = 'Check overdue invoices and update tenant statuses on active tenancies';

    public function handle(): int
    {
        $overdueThreshold = (int) Setting::get('overdue_threshold_days', 14);
        $arrearsThreshold = (int) Setting::get('arrears_threshold_days', 30);
        $now = Carbon::now();

        // Mark invoices as overdue
        $overdueInvoices = Invoice::whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '<', $now)
            ->update(['status' => 'overdue']);

        $this->info("Marked {$overdueInvoices} invoices as overdue.");

        // Update tenant status on active tenancies
        $tenancies = Tenancy::where('status', 'active')->with('invoices')->get();

        $updated = 0;
        foreach ($tenancies as $tenancy) {
            $overdueInvoices = $tenancy->invoices()
                ->where('status', 'overdue')
                ->get();

            if ($overdueInvoices->isEmpty()) {
                $newStatus = 'in_good_standing';
            } else {
                // Check the oldest overdue invoice
                $oldestOverdue = $overdueInvoices->sortBy('due_date')->first();
                $daysOverdue = $now->diffInDays($oldestOverdue->due_date);

                if ($daysOverdue >= $arrearsThreshold) {
                    $newStatus = 'in_arrears';
                } else {
                    $newStatus = 'overdue';
                }
            }

            if ($tenancy->tenant_status !== $newStatus) {
                $tenancy->update(['tenant_status' => $newStatus]);
                $updated++;
            }
        }

        $this->info("Updated {$updated} tenancy statuses.");

        return Command::SUCCESS;
    }
}
