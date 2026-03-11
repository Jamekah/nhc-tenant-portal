<div wire:poll.15s>
    <!-- Page Header -->
    <div class="mb-6">
        <a href="{{ route('portal.tickets') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Tickets
        </a>
    </div>

    <!-- Ticket Header Card -->
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-sm font-medium text-gray-500">{{ $ticket->ticket_number }}</span>

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

                    <!-- Priority Badge -->
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ match($ticket->priority) {
                            'urgent' => 'bg-red-100 text-red-700',
                            'high' => 'bg-orange-100 text-orange-700',
                            'medium' => 'bg-blue-100 text-blue-700',
                            'low' => 'bg-gray-100 text-gray-600',
                            default => 'bg-gray-100 text-gray-600',
                        } }}">
                        {{ ucfirst($ticket->priority) }} Priority
                    </span>

                    <!-- Category Badge -->
                    <span class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                        {{ str($ticket->category)->replace('_', ' ')->title() }}
                    </span>
                </div>

                <h2 class="mt-2 text-xl font-bold text-gray-900">{{ $ticket->subject }}</h2>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                    <span>Submitted {{ $ticket->created_at->format('d M Y, H:i') }} ({{ $ticket->created_at->diffForHumans() }})</span>

                    @if($ticket->assignee)
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                            Assigned to: {{ $ticket->assignee->name }}
                        </span>
                    @endif

                    @if($ticket->resolved_at)
                        <span class="flex items-center gap-1 text-green-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Resolved {{ $ticket->resolved_at->format('d M Y, H:i') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Original Description -->
        <div class="mt-4 rounded-lg bg-gray-50 p-4">
            <h4 class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Description</h4>
            <p class="whitespace-pre-line text-sm text-gray-700">{{ $ticket->description }}</p>
        </div>
    </div>

    <!-- Conversation Thread -->
    <div class="mb-6">
        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
            </svg>
            Conversation
            <span class="text-sm font-normal text-gray-400">({{ count($comments) }} {{ Str::plural('reply', count($comments)) }})</span>
        </h3>

        @if(count($comments) > 0)
            <div class="space-y-4">
                @foreach($comments as $comment)
                    @php
                        $isOwnComment = $comment->user_id === auth()->id();
                    @endphp
                    <div class="flex {{ $isOwnComment ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-xl px-4 py-3 shadow-sm
                                    {{ $isOwnComment
                                        ? 'bg-blue-600 text-white'
                                        : 'border border-gray-200 bg-white text-gray-900' }}">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold {{ $isOwnComment ? 'text-blue-100' : 'text-gray-500' }}">
                                    {{ $isOwnComment ? 'You' : $comment->user->name }}
                                    @if(!$isOwnComment)
                                        <span class="ml-1 inline-flex items-center rounded bg-blue-100 px-1 py-0.5 text-[10px] font-medium text-blue-700">NHC Staff</span>
                                    @endif
                                </span>
                            </div>
                            <p class="mt-1 whitespace-pre-line text-sm {{ $isOwnComment ? 'text-white' : 'text-gray-700' }}">{{ $comment->body }}</p>
                            <p class="mt-1 text-[11px] {{ $isOwnComment ? 'text-blue-200' : 'text-gray-400' }}">
                                {{ $comment->created_at->format('d M Y, H:i') }} ({{ $comment->created_at->diffForHumans() }})
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center">
                <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No replies yet. NHC staff will respond to your ticket shortly.</p>
            </div>
        @endif
    </div>

    <!-- Add Comment Form -->
    @if(!in_array($ticket->status, ['closed']))
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            @if($commentSent)
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3">
                    <p class="text-sm text-green-700">Your reply has been sent successfully.</p>
                </div>
            @endif

            <form wire:submit="addComment">
                <label for="newComment" class="block text-sm font-medium text-gray-700">Add a Reply</label>
                <textarea wire:model="newComment" id="newComment" rows="3"
                          placeholder="Type your reply here..."
                          class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                @error('newComment') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mt-3 flex justify-end">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="addComment">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                            </svg>
                            Send Reply
                        </span>
                        <span wire:loading wire:target="addComment" class="inline-flex items-center">
                            <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 text-center">
            <p class="text-sm text-gray-500">This ticket has been closed. If you need further assistance, please create a new ticket.</p>
        </div>
    @endif

    <!-- Auto-refresh indicator -->
    <p class="mt-4 text-center text-xs text-gray-400">
        <svg class="mr-1 inline h-3 w-3 text-green-400" fill="currentColor" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3"/>
        </svg>
        This page refreshes automatically every 15 seconds
    </p>
</div>
