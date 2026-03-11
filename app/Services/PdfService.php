<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Tenancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class PdfService
{
    /**
     * Generate an Invoice PDF.
     */
    public function generateInvoice(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['tenancy.tenant', 'tenancy.property.region', 'payments' => function ($q) {
            $q->where('status', 'completed')->orderBy('paid_at');
        }]);

        $data = [
            'invoice' => $invoice,
            'tenancy' => $invoice->tenancy,
            'tenant' => $invoice->tenancy->tenant,
            'property' => $invoice->tenancy->property,
            'region' => $invoice->tenancy->property->region,
            'payments' => $invoice->payments,
            'orgName' => \App\Models\Setting::get('organization_name', 'National Housing Corporation'),
            'supportEmail' => \App\Models\Setting::get('support_email', 'info@nhc.gov.pg'),
            'supportPhone' => \App\Models\Setting::get('support_phone', '+675 321 7000'),
        ];

        return Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate a Payment Receipt PDF.
     */
    public function generateReceipt(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $payment->load(['invoice.tenancy.tenant', 'invoice.tenancy.property.region', 'tenancy.property.region', 'recorder']);

        $tenancy = $payment->invoice ? $payment->invoice->tenancy : $payment->tenancy;

        $data = [
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'tenancy' => $tenancy,
            'tenant' => $tenancy->tenant,
            'property' => $tenancy->property,
            'region' => $tenancy->property->region,
            'orgName' => \App\Models\Setting::get('organization_name', 'National Housing Corporation'),
            'supportEmail' => \App\Models\Setting::get('support_email', 'info@nhc.gov.pg'),
            'supportPhone' => \App\Models\Setting::get('support_phone', '+675 321 7000'),
        ];

        return Pdf::loadView('pdf.receipt', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate a Monthly Statement PDF.
     */
    public function generateStatement(Tenancy $tenancy, int $year, int $month): \Barryvdh\DomPDF\PDF
    {
        $tenancy->load(['tenant', 'property.region']);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $invoices = Invoice::where('tenancy_id', $tenancy->id)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('billing_period_start', [$periodStart, $periodEnd])
                    ->orWhereBetween('billing_period_end', [$periodStart, $periodEnd]);
            })
            ->orderBy('billing_period_start')
            ->get();

        $payments = Payment::where('tenancy_id', $tenancy->id)
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$periodStart, $periodEnd])
            ->orderBy('paid_at')
            ->get();

        // Calculate opening balance (all unpaid before this month)
        $openingBalance = Invoice::where('tenancy_id', $tenancy->id)
            ->where('billing_period_start', '<', $periodStart)
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->sum('balance');

        $totalCharges = $invoices->sum('amount_due');
        $totalPayments = $payments->sum('amount');
        $closingBalance = $openingBalance + $totalCharges - $totalPayments;

        $data = [
            'tenancy' => $tenancy,
            'tenant' => $tenancy->tenant,
            'property' => $tenancy->property,
            'region' => $tenancy->property->region,
            'invoices' => $invoices,
            'payments' => $payments,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'openingBalance' => $openingBalance,
            'totalCharges' => $totalCharges,
            'totalPayments' => $totalPayments,
            'closingBalance' => $closingBalance,
            'orgName' => \App\Models\Setting::get('organization_name', 'National Housing Corporation'),
            'supportEmail' => \App\Models\Setting::get('support_email', 'info@nhc.gov.pg'),
            'supportPhone' => \App\Models\Setting::get('support_phone', '+675 321 7000'),
        ];

        return Pdf::loadView('pdf.statement', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate Arrears Report PDF (admin).
     */
    public function generateArrearsReport(): \Barryvdh\DomPDF\PDF
    {
        $regions = Region::where('is_active', true)
            ->with(['properties.tenancies' => function ($q) {
                $q->where('status', 'active')
                    ->where('tenant_status', 'in_arrears')
                    ->with(['tenant', 'property', 'invoices' => function ($iq) {
                        $iq->whereIn('status', ['sent', 'partially_paid', 'overdue']);
                    }]);
            }])
            ->orderBy('name')
            ->get();

        $reportData = [];
        $grandTotal = 0;

        foreach ($regions as $region) {
            $regionTenants = [];
            $regionTotal = 0;

            foreach ($region->properties as $property) {
                foreach ($property->tenancies as $tenancy) {
                    $outstanding = $tenancy->invoices->sum('balance');
                    if ($outstanding > 0) {
                        $regionTenants[] = [
                            'tenant_name' => $tenancy->tenant->name,
                            'property_code' => $property->property_code,
                            'property_title' => $property->title,
                            'tenant_status' => $tenancy->tenant_status,
                            'outstanding' => $outstanding,
                        ];
                        $regionTotal += $outstanding;
                    }
                }
            }

            if (count($regionTenants) > 0) {
                $reportData[] = [
                    'region_name' => $region->name,
                    'region_code' => $region->code,
                    'tenants' => $regionTenants,
                    'region_total' => $regionTotal,
                ];
                $grandTotal += $regionTotal;
            }
        }

        $data = [
            'reportData' => $reportData,
            'grandTotal' => $grandTotal,
            'generatedAt' => now(),
            'orgName' => \App\Models\Setting::get('organization_name', 'National Housing Corporation'),
        ];

        return Pdf::loadView('pdf.arrears-report', $data)
            ->setPaper('a4', 'landscape');
    }

    /**
     * Generate Revenue Collection Report PDF (admin).
     */
    public function generateRevenueReport(int $year, int $month): \Barryvdh\DomPDF\PDF
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $regions = Region::where('is_active', true)->orderBy('name')->get();

        $reportData = [];
        $grandTotal = 0;
        $grandCount = 0;

        foreach ($regions as $region) {
            $payments = Payment::where('status', 'completed')
                ->whereBetween('paid_at', [$periodStart, $periodEnd])
                ->whereHas('tenancy.property', function ($q) use ($region) {
                    $q->where('region_id', $region->id);
                })
                ->get();

            if ($payments->isEmpty()) {
                continue;
            }

            $byMethod = $payments->groupBy('payment_method');
            $methods = [];
            $regionTotal = 0;
            $regionCount = 0;

            foreach ($byMethod as $method => $methodPayments) {
                $methodTotal = $methodPayments->sum('amount');
                $methodCount = $methodPayments->count();
                $methods[] = [
                    'method' => str($method)->replace('_', ' ')->title()->toString(),
                    'count' => $methodCount,
                    'total' => $methodTotal,
                ];
                $regionTotal += $methodTotal;
                $regionCount += $methodCount;
            }

            $reportData[] = [
                'region_name' => $region->name,
                'region_code' => $region->code,
                'methods' => $methods,
                'region_total' => $regionTotal,
                'region_count' => $regionCount,
            ];

            $grandTotal += $regionTotal;
            $grandCount += $regionCount;
        }

        $data = [
            'reportData' => $reportData,
            'grandTotal' => $grandTotal,
            'grandCount' => $grandCount,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'generatedAt' => now(),
            'orgName' => \App\Models\Setting::get('organization_name', 'National Housing Corporation'),
        ];

        return Pdf::loadView('pdf.revenue-report', $data)
            ->setPaper('a4', 'landscape');
    }
}
