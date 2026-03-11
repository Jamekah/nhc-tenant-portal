<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">My Profile</h2>
        <p class="mt-1 text-sm text-gray-500">Update your account information and password</p>
    </div>

    <div class="mx-auto max-w-2xl space-y-6">

        {{-- Success Messages --}}
        @if($profileSaved)
            <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    <p class="ml-2 text-sm font-medium text-green-800">Profile updated successfully!</p>
                </div>
            </div>
        @endif

        @if($passwordChanged)
            <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    <p class="ml-2 text-sm font-medium text-green-800">Password changed successfully!</p>
                </div>
            </div>
        @endif

        {{-- Profile Information --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
            </div>
            <div class="p-6">
                <form wire:submit="updateProfile" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" wire:model="name"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" wire:model="email"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" wire:model="phone"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="+675 xxx xxxx">
                        @error('phone') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                        <svg wire:loading wire:target="updateProfile" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span wire:loading.remove wire:target="updateProfile">Save Changes</span>
                        <span wire:loading wire:target="updateProfile">Saving...</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
            </div>
            <div class="p-6">
                <form wire:submit="updatePassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input type="password" wire:model="current_password"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('current_password') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" wire:model="new_password"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('new_password') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" wire:model="new_password_confirmation"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50">
                        <svg wire:loading wire:target="updatePassword" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span wire:loading.remove wire:target="updatePassword">Change Password</span>
                        <span wire:loading wire:target="updatePassword">Changing...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
