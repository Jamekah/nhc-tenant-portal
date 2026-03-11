<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenancy;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    protected PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Download invoice PDF (portal - tenant only sees own invoices).
     */
    public function portalInvoice(Invoice $invoice)
    {
        $user = auth()->user();
        $tenancy = Tenancy::where('tenant_id', $user->id)->where('status', 'active')->first();

        if (!$tenancy || $invoice->tenancy_id !== $tenancy->id) {
            abort(403, 'Unauthorized');
        }

        $pdf = $this->pdfService->generateInvoice($invoice);
        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Download payment receipt PDF (portal - tenant only sees own payments).
     */
    public function portalReceipt(Payment $payment)
    {
        $user = auth()->user();
        $tenancy = Tenancy::where('tenant_id', $user->id)->where('status', 'active')->first();

        if (!$tenancy || $payment->tenancy_id !== $tenancy->id) {
            abort(403, 'Unauthorized');
        }

        $pdf = $this->pdfService->generateReceipt($payment);
        $ref = $payment->gateway_transaction_id ?? $payment->reference_number ?? $payment->id;
        return $pdf->download("Receipt-{$ref}.pdf");
    }

    /**
     * Download monthly statement PDF (portal).
     */
    public function portalStatement(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $user = auth()->user();
        $tenancy = Tenancy::where('tenant_id', $user->id)->where('status', 'active')->first();

        if (!$tenancy) {
            abort(403, 'No active tenancy');
        }

        $pdf = $this->pdfService->generateStatement($tenancy, $request->year, $request->month);
        return $pdf->stream("Statement-{$request->year}-{$request->month}.pdf");
    }

    /**
     * Download invoice PDF (admin - any invoice).
     */
    public function adminInvoice(Invoice $invoice)
    {
        $pdf = $this->pdfService->generateInvoice($invoice);
        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Download payment receipt PDF (admin - any payment).
     */
    public function adminReceipt(Payment $payment)
    {
        $pdf = $this->pdfService->generateReceipt($payment);
        $ref = $payment->gateway_transaction_id ?? $payment->reference_number ?? $payment->id;
        return $pdf->download("Receipt-{$ref}.pdf");
    }

    /**
     * Download arrears report PDF (admin).
     */
    public function arrearsReport()
    {
        $pdf = $this->pdfService->generateArrearsReport();
        return $pdf->download("Arrears-Report-" . now()->format('Y-m-d') . ".pdf");
    }

    /**
     * Download revenue collection report PDF (admin).
     */
    public function revenueReport(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $pdf = $this->pdfService->generateRevenueReport($request->year, $request->month);
        return $pdf->download("Revenue-Report-{$request->year}-{$request->month}.pdf");
    }
}
