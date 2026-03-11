<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-bold text-gray-900">My Invoices</h2>
        <div class="mt-3 sm:mt-0">
            <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="sent">Sent</option>
                <option value="partially_paid">Partially Paid</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount Due</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-blue-600">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $invoice->billing_period_start->format('M Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                PGK {{ number_format($invoice->amount_due, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-green-600">
                                PGK {{ number_format($invoice->amount_paid, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                PGK {{ number_format($invoice->balance, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $invoice->due_date->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ match($invoice->status) {
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'sent' => 'bg-blue-100 text-blue-800',
                                        'partially_paid' => 'bg-amber-100 text-amber-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                        'overdue' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-500',
                                        default => 'bg-gray-100 text-gray-800',
                                    } }}">
                                    {{ str($invoice->status)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @if(in_array($invoice->status, ['sent', 'partially_paid', 'overdue']))
                                        <a href="{{ route('portal.make-payment', ['invoice' => $invoice->id]) }}"
                                           class="font-medium text-blue-600 hover:text-blue-800">Pay</a>
                                        <span class="text-gray-300">|</span>
                                    @endif
                                    <a href="{{ route('portal.pdf.invoice', $invoice) }}"
                                       target="_blank"
                                       class="inline-flex items-center font-medium text-gray-500 hover:text-gray-700"
                                       title="Download PDF">
                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                No invoices found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="border-t border-gray-200 px-6 py-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
