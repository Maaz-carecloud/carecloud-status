<div>
    <div class="bg-white border rounded-lg p-6 shadow-sm">
        <h2 class="text-xl font-bold mb-2">Subscribe to Updates</h2>
        <p class="text-gray-600 text-sm mb-4">
            Get notified when incidents occur or components change status.
        </p>

        {{-- Success Message --}}
        @if($showSuccess)
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
            <div class="flex items-start">
                <div class="flex-1">
                    <h3 class="font-semibold text-green-800 text-sm mb-1">Success!</h3>
                    <p class="text-xs text-green-700">
                        @if($activeTab === 'email')
                        We've sent a verification email to <strong>{{ $email }}</strong>.
                        @elseif($activeTab === 'sms')
                        We've sent a verification SMS to <strong>{{ $phone }}</strong>.
                        @else
                        Your Teams webhook has been configured.
                        @endif
                    </p>
                </div>
                <button wire:click="dismissSuccess" class="text-green-600 hover:text-green-800 ml-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
        @endif

        {{-- Error Message --}}
        @if($showError)
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
            <div class="flex items-start">
                <div class="flex-1">
                    <h3 class="font-semibold text-red-800 text-sm mb-1">Error</h3>
                    <p class="text-xs text-red-700">{{ $errorMessage }}</p>
                </div>
                <button wire:click="dismissError" class="text-red-600 hover:text-red-800 ml-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
        @endif

        {{-- Tabs --}}
        <div class="mb-4">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <button wire:click="setActiveTab('email')" type="button"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-xs font-medium transition-colors {{ $activeTab === 'email' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                        <svg class="inline-block w-4 h-4 mr-1 -mt-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Email
                    </button>
                    <button wire:click="setActiveTab('sms')" type="button"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-xs font-medium transition-colors {{ $activeTab === 'sms' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                        <svg class="inline-block w-4 h-4 mr-1 -mt-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        SMS
                    </button>
                    <button wire:click="setActiveTab('teams')" type="button"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-xs font-medium transition-colors {{ $activeTab === 'teams' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                        <svg class="inline-block w-4 h-4 mr-1 -mt-1" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.625 8.127v7.746a1.125 1.125 0 01-1.125 1.125h-7.746a1.125 1.125 0 01-1.125-1.125V8.127a1.125 1.125 0 011.125-1.125h7.746a1.125 1.125 0 011.125 1.125z" />
                            <path
                                d="M9.002 5.377v11.246A2.627 2.627 0 016.377 19H5.25A2.25 2.25 0 013 16.75v-9a2.25 2.25 0 012.25-2.25h1.127A2.627 2.627 0 019.002 5.377z"
                                opacity=".5" />
                        </svg>
                        Teams
                    </button>
                </nav>
            </div>
        </div>

        <form wire:submit="subscribe">
            {{-- Email Tab Content --}}
            @if($activeTab === 'email')
            <div class="mb-4">
                <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" wire:model="email"
                    class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                    placeholder="your@email.com" required>
                @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    You'll receive a verification email.
                </p>
            </div>
            @endif

            {{-- SMS Tab Content --}}
            @if($activeTab === 'sms')
            <div class="mb-4">
                <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1">
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <input type="tel" id="phone" wire:model="phone"
                    class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                    placeholder="+1 (555) 123-4567" required>
                @error('phone')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Include country code (e.g., +1 for US).
                </p>

                {{-- Also collect email for SMS subscribers --}}
                <label for="email-sms" class="block text-xs font-semibold text-gray-700 mb-1 mt-3">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email-sms" wire:model="email"
                    class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                    placeholder="your@email.com" required>
                <p class="text-xs text-gray-500 mt-1">
                    Required for verification.
                </p>
            </div>
            @endif

            {{-- Teams Tab Content --}}
            @if($activeTab === 'teams')
            <div class="mb-4">
                <label for="teamsWebhookUrl" class="block text-xs font-semibold text-gray-700 mb-1">
                    Webhook URL <span class="text-red-500">*</span>
                </label>
                <input type="url" id="teamsWebhookUrl" wire:model="teamsWebhookUrl"
                    class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('teamsWebhookUrl') border-red-500 @enderror"
                    placeholder="https://outlook.office.com/webhook/..." required>
                @error('teamsWebhookUrl')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    <a href="https://docs.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook"
                        target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                        How to create a webhook
                    </a>
                </p>

                {{-- Also collect email for Teams subscribers --}}
                <label for="email-teams" class="block text-xs font-semibold text-gray-700 mb-1 mt-3">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email-teams" wire:model="email"
                    class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                    placeholder="your@email.com" required>
                <p class="text-xs text-gray-500 mt-1">
                    Required for verification.
                </p>
            </div>
            @endif

            {{-- Info Box --}}
            {{-- <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <div class="flex">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="text-xs text-blue-800">
                        <p class="font-semibold mb-1">Subscribed to all components</p>
                        <p>You'll receive notifications for all incidents and status changes.</p>
                    </div>
                </div>
            </div> --}}

            {{-- Submit Button --}}
            <button type="submit" wire:loading.attr="disabled"
                class="w-full bg-blue-600 text-white font-semibold py-2.5 text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <span wire:loading.remove>Subscribe</span>
                <span wire:loading>Subscribing...</span>
            </button>

            <p class="text-xs text-gray-500 mt-3 text-center">
                You can unsubscribe at any time.
            </p>
        </form>
    </div>
</div>