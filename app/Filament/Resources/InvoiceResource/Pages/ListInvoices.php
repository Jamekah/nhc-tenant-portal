<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Tenancy;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('bulkGenerate')
                ->label('Generate Monthly Invoices')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Generate Monthly Invoices')
                ->modalDescription('This will generate invoices for all active tenancies for the current billing period. Existing invoices for this period will be skipped.')
                ->action(function () {
                    $now = Carbon::now();
                    $periodStart = $now->copy()->startOfMonth();
                    $periodEnd = $now->copy()->endOfMonth();
                    $dueDate = $periodStart->copy()->addDays(14);

                    $tenancies = Tenancy::where('status', 'active')->with('property')->get();

                    $generated = 0;
                    $skipped = 0;

                    foreach ($tenancies as $tenancy) {
                        // Check if invoice already exists for this period
                        $exists = Invoice::where('tenancy_id', $tenancy->id)
                            ->where('billing_period_start', $periodStart)
                            ->exists();

                        if ($exists) {
                            $skipped++;
                            continue;
                        }

                        $invoiceNumber = 'INV-' . $now->format('Y') . '-' . str_pad(
                            Invoice::count() + $generated + 1,
                            6,
                            '0',
                            STR_PAD_LEFT
                        );

                        Invoice::create([
                            'tenancy_id' => $tenancy->id,
                            'invoice_number' => $invoiceNumber,
                            'billing_period_start' => $periodStart,
                            'billing_period_end' => $periodEnd,
                            'amount_due' => $tenancy->agreed_rent,
                            'amount_paid' => 0,
                            'balance' => $tenancy->agreed_rent,
                            'due_date' => $dueDate,
                            'status' => 'sent',
                            'issued_at' => now(),
                        ]);

                        $generated++;
                    }

                    Notification::make()
                        ->title("Invoices generated: {$generated}, Skipped: {$skipped}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
