<div>
    <!-- Welcome Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">
            Welcome back, {{ Auth::user()->name }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            @if($propertyName)
                Property: <span class="font-medium text-gray-700">{{ $propertyName }}</span>
            @else
                No active tenancy found.
            @endif
        </p>
    </div>

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

        <!-- Current Balance Card -->
        @php
            $balanceColor = match($tenantStatus) {
                'in_good_standing' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'value' => 'text-green-700', 'border' => 'border-green-200'],
                'overdue' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'value' => 'text-amber-700', 'border' => 'border-amber-200'],
                'in_arrears' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'value' => 'text-red-700', 'border' => 'border-red-200'],
                default => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'value' => 'text-gray-900', 'border' => 'border-gray-200'],
            };
        @endphp
        <div class="overflow-hidden rounded-xl border {{ $balanceColor['border'] }} bg-white shadow-sm">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $balanceColor['bg'] }}">
                        <svg class="h-6 w-6 {{ $balanceColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Current Balance</p>
                        <p class="mt-1 text-2xl font-bold {{ $balanceColor['value'] }}">PGK {{ number_format($totalBalance, 2) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                        {{ match($tenantStatus) {
                            'in_good_standing' => 'bg-green-100 text-green-800',
                            'overdue' => 'bg-amber-100 text-amber-800',
                            'in_arrears' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800',
                        } }}">
                        {{ str($tenantStatus)->replace('_', ' ')->title() }}
                    </span>
                    @if($totalBalance > 0)
                        <a href="{{ route('portal.make-payment') }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">Pay Now &rarr;</a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Next Due Date Card -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Next Due Date</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $nextDueDate ? $nextDueDate->format('M d, Y') : 'No pending' }}
                        </p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400">
                    @if($nextDueDate)
                        {{ $nextDueDate->isPast() ? 'Overdue by ' . $nextDueDate->diffForHumans() : 'Due ' . $nextDueDate->diffForHumans() }}
                    @else
                        All invoices are settled
                    @endif
                </p>
            </div>
        </div>

        <!-- Tenancy Info Card -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Monthly Rent</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            PGK {{ $tenancy ? number_format($tenancy->agreed_rent, 2) : '0.00' }}
                        </p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400">
                    {{ $tenancy ? ucfirst($tenancy->payment_frequency) . ' billing cycle' : 'No active tenancy' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Monthly Statement Download -->
    @if($tenancy)
    <div class="mt-8">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Monthly Statement</p>
                        <p class="text-xs text-gray-500">Download a summary of your invoices and payments</p>
                    </div>
                </div>
                <a href="{{ route('portal.pdf.statement', ['year' => now()->format('Y'), 'month' => now()->format('m')]) }}"
                   target="_blank"
                   class="inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    {{ now()->format('F Y') }} Statement
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="mt-8">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Recent Activity</h3>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @if($recentActivity->count() > 0)
                <ul class="divide-y divide-gray-100">
                    @foreach($recentActivity as $activity)
                        <li class="flex items-center justify-between px-6 py-4">
                            <div class="flex items-center space-x-3">
                                @if($activity['type'] === 'payment')
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                        <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $activity['description'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity['method'] }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400">{{ $activity['date']->format('M d, Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-500">No recent activity</p>
                </div>
            @endif
        </div>
    </div>
</div>
