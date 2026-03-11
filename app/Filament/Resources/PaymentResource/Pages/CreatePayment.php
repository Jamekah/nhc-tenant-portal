<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $payment = $this->record;

        if ($payment->status === 'completed' && $payment->invoice) {
            $invoice = $payment->invoice;

            // Recalculate total paid from all completed payments
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');

            $invoice->update([
                'amount_paid' => $totalPaid,
                'balance' => max(0, $invoice->amount_due - $totalPaid),
                'status' => $totalPaid >= $invoice->amount_due ? 'paid' : 'partially_paid',
            ]);
        }
    }
}
