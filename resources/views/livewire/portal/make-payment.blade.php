<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Make a Payment</h2>
        <p class="mt-1 text-sm text-gray-500">Pay your invoices securely online</p>
    </div>

    <div class="mx-auto max-w-2xl">

        {{-- Step 1: Select Invoice --}}
        @if($step === 'select')
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Step 1: Select Invoice</h3>
                </div>
                <div class="p-6">
                    @if($unpaidInvoices->count() > 0)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Select Invoice to Pay</label>
                                <select wire:model.live="selectedInvoice" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Choose an invoice --</option>
                                    @foreach($unpaidInvoices as $inv)
                                        <option value="{{ $inv->id }}">
                                            {{ $inv->invoice_number }} — {{ $inv->billing_period_start->format('M Y') }} — Balance: PGK {{ number_format($inv->balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedInvoice') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            @if($selectedInvoiceData)
                                <div class="rounded-lg bg-blue-50 p-4">
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <p class="text-gray-600">Invoice:</p>
                                        <p class="font-medium">{{ $selectedInvoiceData->invoice_number }}</p>
                                        <p class="text-gray-600">Amount Due:</p>
                                        <p class="font-medium">PGK {{ number_format($selectedInvoiceData->amount_due, 2) }}</p>
                                        <p class="text-gray-600">Already Paid:</p>
                                        <p class="font-medium text-green-600">PGK {{ number_format($selectedInvoiceData->amount_paid, 2) }}</p>
                                        <p class="text-gray-600">Balance:</p>
                                        <p class="font-bold text-red-600">PGK {{ number_format($selectedInvoiceData->balance, 2) }}</p>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Amount (PGK)</label>
                                <input type="number" wire:model="amount" step="0.01" min="0.01"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="0.00">
                                @error('amount') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                                <p class="mt-1 text-xs text-gray-500">You can make a partial or full payment</p>
                            </div>

                            <button wire:click="selectInvoice"
                                    wire:loading.attr="disabled"
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                                <svg wire:loading wire:target="selectInvoice" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Continue to Payment
                            </button>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-3 text-sm font-medium text-gray-900">All invoices are paid!</p>
                            <p class="mt-1 text-sm text-gray-500">You have no outstanding invoices.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Step 2: Card Details --}}
        @if($step === 'confirm')
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Step 2: Enter Card Details</h3>
                    <p class="text-xs text-amber-600 mt-1">* This is a simulated payment gateway for demo purposes</p>
                </div>
                <div class="p-6">
                    <div class="mb-4 rounded-lg bg-blue-50 p-4">
                        <p class="text-sm text-gray-700">
                            Paying <span class="font-bold">PGK {{ number_format((float)$amount, 2) }}</span>
                            for invoice <span class="font-bold">{{ $selectedInvoiceData?->invoice_number }}</span>
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name on Card</label>
                            <input type="text" wire:model="cardName"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="John Doe">
                            @error('cardName') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Card Number</label>
                            <input type="text" wire:model="cardNumber" maxlength="19"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="4111 1111 1111 1111">
                            @error('cardNumber') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expiry</label>
                                <input type="text" wire:model="cardExpiry" maxlength="5"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="MM/YY">
                                @error('cardExpiry') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">CVV</label>
                                <input type="text" wire:model="cardCvv" maxlength="4"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="123">
                                @error('cardCvv') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex space-x-3 pt-2">
                            <button wire:click="$set('step', 'select')"
                                    class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                Back
                            </button>
                            <button wire:click="processPayment"
                                    wire:loading.attr="disabled"
                                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50">
                                <svg wire:loading wire:target="processPayment" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span wire:loading.remove wire:target="processPayment">Pay PGK {{ number_format((float)$amount, 2) }}</span>
                                <span wire:loading wire:target="processPayment">Processing Payment...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 3: Result --}}
        @if($step === 'result')
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="p-8 text-center">
                    @if($paymentSuccess)
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-gray-900">Payment Successful!</h3>
                        <p class="mt-2 text-sm text-gray-500">Your payment has been processed successfully.</p>

                        <div class="mt-6 rounded-lg bg-gray-50 p-4 text-left">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <p class="text-gray-600">Transaction ID:</p>
                                <p class="font-mono font-medium">{{ $transactionId }}</p>
                                <p class="text-gray-600">Amount Paid:</p>
                                <p class="font-bold text-green-600">PGK {{ number_format((float)$amount, 2) }}</p>
                                <p class="text-gray-600">Payment Method:</p>
                                <p class="font-medium">Online Gateway</p>
                                <p class="text-gray-600">Date:</p>
                                <p class="font-medium">{{ now()->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    @else
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-gray-900">Payment Failed</h3>
                        <p class="mt-2 text-sm text-red-600">{{ $errorMessage }}</p>
                    @endif

                    <div class="mt-6 flex justify-center space-x-3">
                        <a href="{{ route('portal.dashboard') }}"
                           class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            Back to Dashboard
                        </a>
                        @if(!$paymentSuccess)
                            <button wire:click="resetPayment"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                                Try Again
                            </button>
                        @else
                            <a href="{{ route('portal.payments') }}"
                               class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                                View Payment History
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
