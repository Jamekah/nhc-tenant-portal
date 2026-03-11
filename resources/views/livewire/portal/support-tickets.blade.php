<div>
    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Support Tickets</h2>
            <p class="mt-1 text-sm text-gray-500">View and manage your support requests</p>
        </div>
        <a href="{{ route('portal.tickets.create') }}"
           class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Report an Issue
        </a>
    </div>

    @if(!$tenancy)
        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <p class="mt-2 text-sm font-medium text-yellow-800">No active tenancy found. Please contact NHC administration.</p>
        </div>
    @else
        <!-- Status Filter Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                @php
                    $tabs = [
                        '' => ['label' => 'All', 'count' => $ticketCounts['all']],
                        'open' => ['label' => 'Open', 'count' => $ticketCounts['open']],
                        'in_progress' => ['label' => 'In Progress', 'count' => $ticketCounts['in_progress']],
                        'resolved' => ['label' => 'Resolved', 'count' => $ticketCounts['resolved']],
                        'closed' => ['label' => 'Closed', 'count' => $ticketCounts['closed']],
                    ];
                @endphp

                @foreach($tabs as $value => $tab)
                    <button wire:click="$set('statusFilter', '{{ $value }}')"
                            class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition
                                   {{ $statusFilter === $value
                                       ? 'border-blue-500 text-blue-600'
                                       : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                        {{ $tab['label'] }}
                        @if($tab['count'] > 0)
                            <span class="ml-1.5 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                         {{ $statusFilter === $value ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                {{ $tab['count'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Tickets List -->
        @if($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator && $tickets->count() > 0)
            <div class="space-y-3">
                @foreach($tickets as $ticket)
                    <a href="{{ route('portal.tickets.show', $ticket->id) }}"
                       class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow-md sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-mono font-medium text-gray-500">{{ $ticket->ticket_number }}</span>

                                    <!-- Priority Badge -->
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ match($ticket->priority) {
                                            'urgent' => 'bg-red-100 text-red-700',
                                            'high' => 'bg-orange-100 text-orange-700',
                                            'medium' => 'bg-blue-100 text-blue-700',
                                            'low' => 'bg-gray-100 text-gray-600',
                                            default => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>

                                    <!-- Category Badge -->
                                    <span class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                                        {{ str($ticket->category)->replace('_', ' ')->title() }}
                                    </span>
                                </div>

                                <h3 class="mt-1.5 text-sm font-semibold text-gray-900">{{ $ticket->subject }}</h3>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ Str::limit($ticket->description, 120) }}</p>
                            </div>

                            <div class="flex flex-shrink-0 items-center gap-3 sm:flex-col sm:items-end sm:gap-2">
                                <!-- Status Badge -->
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ match($ticket->status) {
                                        'open' => 'bg-yellow-100 text-yellow-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'resolved' => 'bg-green-100 text-green-800',
                                        'closed' => 'bg-gray-100 text-gray-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ str($ticket->status)->replace('_', ' ')->title() }}
                                </span>

                                <span class="text-xs text-gray-400">
                                    {{ $ticket->created_at->diffForHumans() }}
                                </span>

                                @if($ticket->comments_count ?? $ticket->comments()->count() > 0)
                                    <span class="inline-flex items-center text-xs text-gray-400">
                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                                        </svg>
                                        {{ $ticket->comments()->where('is_internal', false)->count() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No tickets found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($statusFilter)
                        No tickets with status "{{ str($statusFilter)->replace('_', ' ')->title() }}".
                    @else
                        You haven't submitted any support tickets yet.
                    @endif
                </p>
                <div class="mt-4">
                    <a href="{{ route('portal.tickets.create') }}"
                       class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <svg class="mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Report an Issue
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>
