<div>
    <!-- Page Header -->
    <div class="mb-6">
        <a href="{{ route('portal.tickets') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Tickets
        </a>
        <h2 class="mt-2 text-2xl font-bold text-gray-900">Report an Issue</h2>
        <p class="mt-1 text-sm text-gray-500">Submit a support request to NHC maintenance or administration team</p>
    </div>

    @if(!$tenancy)
        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-6 text-center">
            <p class="text-sm font-medium text-yellow-800">No active tenancy found. Please contact NHC administration.</p>
        </div>
    @elseif($submitted)
        <!-- Success State -->
        <div class="mx-auto max-w-lg rounded-xl border border-green-200 bg-green-50 p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-green-900">Ticket Submitted Successfully</h3>
            <p class="mt-2 text-sm text-green-700">
                Your ticket <span class="font-mono font-bold">{{ $ticketNumber }}</span> has been submitted.
                Our team will review it and respond as soon as possible.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="{{ route('portal.tickets') }}"
                   class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                    View My Tickets
                </a>
                <a href="{{ route('portal.tickets.create') }}"
                   wire:navigate
                   class="inline-flex items-center rounded-lg border border-green-300 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">
                    Submit Another
                </a>
            </div>
        </div>
    @else
        <!-- Ticket Form -->
        <div class="mx-auto max-w-2xl">
            <!-- Property Info Banner -->
            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-900">
                            Property: {{ $tenancy->property->title ?? $tenancy->property->property_code }}
                        </p>
                        <p class="text-xs text-blue-600">{{ $tenancy->property->address ?? '' }}</p>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <form wire:submit="submit" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                    <select wire:model="category" id="category"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">Select a category...</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="electrical">Electrical</option>
                        <option value="structural">Structural</option>
                        <option value="general_query">General Query</option>
                        <option value="billing">Billing</option>
                    </select>
                    @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Priority <span class="text-red-500">*</span></label>
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                            <label class="relative flex cursor-pointer items-center justify-center rounded-lg border p-3 transition
                                          {{ $priority === $value
                                              ? match($value) {
                                                  'low' => 'border-gray-400 bg-gray-50 ring-2 ring-gray-400',
                                                  'medium' => 'border-blue-400 bg-blue-50 ring-2 ring-blue-400',
                                                  'high' => 'border-orange-400 bg-orange-50 ring-2 ring-orange-400',
                                                  'urgent' => 'border-red-400 bg-red-50 ring-2 ring-red-400',
                                              }
                                              : 'border-gray-200 bg-white hover:bg-gray-50' }}">
                                <input type="radio" wire:model="priority" value="{{ $value }}" class="sr-only">
                                <span class="text-sm font-medium {{ $priority === $value
                                    ? match($value) {
                                        'low' => 'text-gray-700',
                                        'medium' => 'text-blue-700',
                                        'high' => 'text-orange-700',
                                        'urgent' => 'text-red-700',
                                    }
                                    : 'text-gray-600' }}">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('priority') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="subject" id="subject"
                           placeholder="Brief description of the issue"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                    <textarea wire:model="description" id="description" rows="5"
                              placeholder="Please provide as much detail as possible about the issue, including location within the property, when it started, and any relevant information..."
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <a href="{{ route('portal.tickets') }}"
                       class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="submit">Submit Ticket</span>
                        <span wire:loading wire:target="submit" class="inline-flex items-center">
                            <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
