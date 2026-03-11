<?php

namespace App\Livewire\Portal;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        $tenancy = Tenancy::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->with('property')
            ->first();

        $totalBalance = 0;
        $nextDueDate = null;
        $tenantStatus = 'in_good_standing';
        $recentActivity = collect();
        $propertyName = null;

        if ($tenancy) {
            $tenantStatus = $tenancy->tenant_status;
            $propertyName = $tenancy->property->title ?? $tenancy->property->property_code;

            // Calculate outstanding balance
            $totalBalance = Invoice::where('tenancy_id', $tenancy->id)
                ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
                ->sum('balance');

            // Get next due date
            $nextInvoice = Invoice::where('tenancy_id', $tenancy->id)
                ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
                ->orderBy('due_date')
                ->first();

            $nextDueDate = $nextInvoice?->due_date;

            // Recent activity: last 5 payments and invoices combined
            $recentPayments = Payment::where('tenancy_id', $tenancy->id)
                ->where('status', 'completed')
                ->latest('paid_at')
                ->take(5)
                ->get()
                ->map(fn ($p) => [
                    'type' => 'payment',
                    'description' => 'Payment of PGK ' . number_format($p->amount, 2),
                    'method' => str($p->payment_method)->replace('_', ' ')->title()->toString(),
                    'date' => $p->paid_at,
                ]);

            $recentInvoices = Invoice::where('tenancy_id', $tenancy->id)
                ->whereNotNull('issued_at')
                ->latest('issued_at')
                ->take(5)
                ->get()
                ->map(fn ($i) => [
                    'type' => 'invoice',
                    'description' => "Invoice {$i->invoice_number} — PGK " . number_format($i->amount_due, 2),
                    'method' => $i->status,
                    'date' => $i->issued_at,
                ]);

            $recentActivity = $recentPayments->merge($recentInvoices)
                ->sortByDesc('date')
                ->take(5)
                ->values();
        }

        return view('livewire.portal.dashboard', [
            'totalBalance' => $totalBalance,
            'nextDueDate' => $nextDueDate,
            'tenantStatus' => $tenantStatus,
            'recentActivity' => $recentActivity,
            'propertyName' => $propertyName,
            'tenancy' => $tenancy,
        ])->layout('layouts.portal');
    }
}
