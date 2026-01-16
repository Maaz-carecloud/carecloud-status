<div class="flex flex-col lg:flex-row gap-8">
    {{-- Main Content --}}
    <div class="flex-1 space-y-8">
        <h1 class="text-2xl mb-2 font-bold">About us</h1>
        <p class="mb-1 max-w-[90ch]">Welcome to CareCloud's home for real-time information on system performance. Here
            you'll find
            live and
            historical data on system performance. If there are any interruptions in service, a note will be posted
            here.</p>
        <p class="max-w-[90ch]">Please contact CareCloud's support team at <span class="text-blue-500">(866)
                931-3832</span> or email us at <a href="mailto:support@carecloud.com"
                class="text-blue-500">support@carecloud.com</a> for any
            additional questions or concerns.</p>
        {{-- Overall Status Banner --}}
        <div class="rounded-lg border-2 p-6 text-center" style="border-color: {{ $overallStatus['color'] }};">
            <h1 class="text-3xl font-bold mb-2">{{ $overallStatus['label'] }}</h1>
            <p class="text-gray-600">{{ now()->timezone('America/New_York')->format('F j, Y - g:i A') }}</p>
        </div>

        {{-- Active Incidents --}}
        @if($activeIncidents->count() > 0)
        <section>
            <h2 class="text-2xl font-bold mb-4">Active Incidents</h2>
            <div class="space-y-4">
                @foreach($activeIncidents as $incident)
                <div class="border rounded-lg p-6 bg-white shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-xl font-semibold">{{ $incident->name }}</h3>
                            <div class="flex gap-2 mt-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded"
                                    style="border: 1px solid {{ $incident->status->color() }}; color: {{ $incident->status->color() }};">
                                    {{ $incident->status->label() }}
                                </span>
                                <span class="px-2 py-1 text-xs font-semibold rounded"
                                    style="border: 1px solid {{ $incident->impact->color() }}; color: {{ $incident->impact->color() }};">
                                    {{ $incident->impact->label() }} Impact
                                </span>
                            </div>
                        </div>
                        <time class="text-sm text-gray-500">
                            {{ $incident->created_at->timezone('America/New_York')->diffForHumans() }}
                        </time>
                    </div>

                    {{-- Affected Components --}}
                    @if($incident->components->count() > 0)
                    <div class="mb-3">
                        <p class="text-sm text-gray-600">
                            <strong>Affected:</strong>
                            {{ $incident->components->pluck('name')->join(', ') }}
                        </p>
                    </div>
                    @endif

                    {{-- Latest Updates --}}
                    @if($incident->updates->count() > 0)
                    <div class="space-y-2 mt-4 border-t pt-4 max-h-96 overflow-y-auto">
                        @foreach($incident->updates as $update)
                        <div class="text-sm">
                            <p class="text-gray-600">
                                <strong>{{ $update->created_at->timezone('America/New_York')->format('M d, H:i')
                                    }}</strong>
                                -
                                <span class="font-semibold">{{ $update->status->label() }}</span>
                            </p>
                            <p class="mt-1">{!! nl2br(e($update->message)) !!}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Scheduled Maintenance --}}
        @if($scheduledMaintenance->count() > 0)
        <section>
            <h2 class="text-2xl font-bold mb-4">Scheduled Maintenance</h2>
            <div class="space-y-4">
                @foreach($scheduledMaintenance as $maintenance)
                <div class="border rounded-lg p-6 bg-blue-50 border-blue-200">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-xl font-semibold">{{ $maintenance->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Scheduled for: <strong>{{
                                    $maintenance->scheduled_at->timezone('America/New_York')->format('F j, Y - g:i A')
                                    }}</strong>
                            </p>
                        </div>
                    </div>

                    @if($maintenance->components->count() > 0)
                    <p class="text-sm text-gray-600">
                        <strong>Affected:</strong>
                        {{ $maintenance->components->pluck('name')->join(', ') }}
                    </p>
                    @endif

                    @if($maintenance->message)
                    <p class="mt-3">{{ $maintenance->message }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Components Status --}}
        <section>
            <h2 class="text-2xl font-bold mb-4">Components</h2>
            <div class="space-y-3">
                @foreach($components as $component)
                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold">{{ $component->name }}</h3>
                            @if($component->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $component->description }}</p>
                            @endif
                        </div>
                        <span class="px-3 py-1 text-sm font-semibold rounded whitespace-nowrap"
                            style="border: 1px solid {{ $component->status->color() }}; color: {{ $component->status->color() }};">
                            {{ $component->status->label() }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        {{-- Auto-refresh indicator --}}
        <div class="text-center text-sm text-gray-500">
            <p>This page refreshes automatically every 60 seconds</p>
            <button wire:click="refresh" class="text-blue-600 hover:text-blue-800 underline mt-2">
                Refresh Now
            </button>
        </div>
    </div>

    {{-- Sticky Subscribe Form (Right Side) --}}
    <div class="w-full lg:w-80 flex-shrink-0">
        <div class="sticky top-8">
            <livewire:public.subscribe-form />
        </div>
    </div>
</div>

{{-- Auto-refresh script --}}
@script
setInterval(() => {
$wire.$refresh();
}, 60000); // 60 seconds
@endscript