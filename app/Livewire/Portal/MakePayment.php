<?php

namespace App\Livewire\Portal;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class MakePayment extends Component
{
    public ?int $selectedInvoice = null;
    public string $amount = '';
    public string $cardNumber = '';
    public string $cardExpiry = '';
    public string $cardCvv = '';
    public string $cardName = '';

    public string $step = 'select'; // select, confirm, result
    public bool $paymentSuccess = false;
    public ?string $transactionId = null;
    public ?string $errorMessage = null;

    public function mount(?int $invoice = null)
    {
        if ($invoice) {
            $this->selectedInvoice = $invoice;
            $inv = Invoice::find($invoice);
            if ($inv) {
                $this->amount = (string) $inv->balance;
            }
        }
    }

    public function selectInvoice()
    {
        $this->validate([
            'selectedInvoice' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $invoice = Invoice::find($this->selectedInvoice);
        if ($invoice && (float) $this->amount > (float) $invoice->balance) {
            $this->addError('amount', 'Amount cannot exceed the invoice balance of PGK ' . number_format($invoice->balance, 2));
            return;
        }

        $this->step = 'confirm';
    }

    public function processPayment()
    {
        $this->validate([
            'cardNumber' => 'required|string|min:16',
            'cardExpiry' => 'required|string|min:5',
            'cardCvv' => 'required|string|min:3',
            'cardName' => 'required|string|min:2',
        ]);

        $invoice = Invoice::find($this->selectedInvoice);
        if (!$invoice) {
            $this->errorMessage = 'Invoice not found.';
            $this->paymentSuccess = false;
            $this->step = 'result';
            return;
        }

        // Simulate gateway processing - 90% success rate
        $this->paymentSuccess = rand(1, 10) <= 9;
        $this->transactionId = 'GW-' . strtoupper(Str::random(12));

        if ($this->paymentSuccess) {
            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'tenancy_id' => $invoice->tenancy_id,
                'amount' => (float) $this->amount,
                'payment_method' => 'online_gateway',
                'gateway_transaction_id' => $this->transactionId,
                'gateway_response' => [
                    'status' => 'approved',
                    'card_last_four' => substr(preg_replace('/\D/', '', $this->cardNumber), -4),
                    'timestamp' => now()->toISOString(),
                ],
                'reference_number' => $this->transactionId,
                'status' => 'completed',
                'paid_at' => now(),
                'recorded_by' => Auth::id(),
            ]);

            // Update invoice balance
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
            $invoice->update([
                'amount_paid' => $totalPaid,
                'balance' => max(0, $invoice->amount_due - $totalPaid),
                'status' => $totalPaid >= $invoice->amount_due ? 'paid' : 'partially_paid',
            ]);
            $this->dispatch('toast', type: 'success', message: 'Payment of PGK ' . number_format((float) $this->amount, 2) . ' processed successfully!');
        } else {
            $this->errorMessage = 'Payment declined by the gateway. Please try again or use a different card.';
            $this->dispatch('toast', type: 'error', message: 'Payment was declined. Please try again.');
        }

        $this->step = 'result';
    }

    public function resetPayment()
    {
        $this->reset(['selectedInvoice', 'amount', 'cardNumber', 'cardExpiry', 'cardCvv', 'cardName', 'step', 'paymentSuccess', 'transactionId', 'errorMessage']);
        $this->step = 'select';
    }

    public function render()
    {
        $user = Auth::user();
        $tenancyIds = Tenancy::where('tenant_id', $user->id)->pluck('id');

        $unpaidInvoices = Invoice::whereIn('tenancy_id', $tenancyIds)
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->where('balance', '>', 0)
            ->orderBy('due_date')
            ->get();

        $selectedInvoiceData = $this->selectedInvoice ? Invoice::find($this->selectedInvoice) : null;

        return view('livewire.portal.make-payment', [
            'unpaidInvoices' => $unpaidInvoices,
            'selectedInvoiceData' => $selectedInvoiceData,
        ])->layout('layouts.portal');
    }
}
