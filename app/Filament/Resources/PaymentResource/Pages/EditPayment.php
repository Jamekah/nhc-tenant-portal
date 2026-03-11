<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $payment = $this->record;

        if ($payment->invoice) {
            $invoice = $payment->invoice;

            // Recalculate total paid from all completed payments
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');

            $invoice->update([
                'amount_paid' => $totalPaid,
                'balance' => max(0, $invoice->amount_due - $totalPaid),
                'status' => match (true) {
                    $totalPaid >= $invoice->amount_due => 'paid',
                    $totalPaid > 0 => 'partially_paid',
                    default => $invoice->status,
                },
            ]);
        }
    }
}
