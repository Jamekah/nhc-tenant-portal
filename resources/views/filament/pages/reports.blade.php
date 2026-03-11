<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2">

        {{-- Arrears Report --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Arrears Report</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        All tenants currently in arrears, grouped by region with total PGK outstanding.
                    </p>
                    <div class="mt-4">
                        <a href="{{ $this->getArrearsReportUrl() }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                            Download Arrears Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Collection Report --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <x-heroicon-o-banknotes class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Collection Report</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Monthly payments received, grouped by region and payment method.
                    </p>
                    <div class="mt-4 flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Year</label>
                            <select wire:model.live="revenueYear"
                                    class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @for($y = 2024; $y <= 2027; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Month</label>
                            <select wire:model.live="revenueMonth"
                                    class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a href="{{ $this->getRevenueReportUrl() }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                            Download Revenue Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice PDF --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <x-heroicon-o-document-text class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice PDFs</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Download individual invoice PDFs from the Invoices table using the PDF action button on each row.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('filament.admin.resources.invoices.index') }}"
                           class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            Go to Invoices &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Receipt PDF --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <x-heroicon-o-receipt-percent class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Receipts</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Download individual payment receipts from the Payments table using the Receipt action button on each row.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('filament.admin.resources.payments.index') }}"
                           class="text-sm font-medium text-purple-600 hover:text-purple-800 dark:text-purple-400">
                            Go to Payments &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
