<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')

    @php
    $themeColor = \App\Models\Setting::get('theme_color', '#3B82F6');
    echo '<style>
        :root {
            --theme-color: ' . $themeColor . ';
            --theme-color-hover: color-mix(in srgb, ' . $themeColor . ' 90%, black);
            --theme-color-light: color-mix(in srgb, ' . $themeColor . ' 10%, white);
            --theme-color-dark: color-mix(in srgb, ' . $themeColor . ' 80%, black);
        }

        /* Flux UI Primary Buttons */
        [data-flux-button][data-variant="primary"],
        [data-flux-button][data-variant="primary"]>* {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
        }

        [data-flux-button][data-variant="primary"]:hover {
            background-color: var(--theme-color-hover) !important;
            border-color: var(--theme-color-hover) !important;
        }

        /* All submit and primary buttons */
        button[type="submit"],
        .btn-primary,
        button.bg-blue-600,
        button.bg-blue-500,
        a.bg-blue-600,
        a.bg-blue-500 {
            background-color: var(--theme-color) !important;
        }

        button[type="submit"]:hover,
        .btn-primary:hover,
        button.bg-blue-600:hover,
        button.bg-blue-500:hover {
            background-color: var(--theme-color-hover) !important;
        }

        /* Background colors */
        .bg-blue-600,
        .bg-blue-500,
        .bg-blue-700 {
            background-color: var(--theme-color) !important;
        }

        .hover\\:bg-blue-700:hover,
        .hover\\:bg-blue-600:hover,
        .hover\\:bg-blue-500:hover {
            background-color: var(--theme-color-hover) !important;
        }

        /* Text colors */
        .text-blue-600,
        .text-blue-700,
        .text-blue-500 {
            color: var(--theme-color) !important;
        }

        .hover\\:text-blue-600:hover,
        .hover\\:text-blue-700:hover {
            color: var(--theme-color-hover) !important;
        }

        /* Border colors */
        .border-blue-600,
        .border-blue-500 {
            border-color: var(--theme-color) !important;
        }

        /* Ring/Focus colors */
        .ring-blue-500,
        .ring-blue-600 {
            --tw-ring-color: var(--theme-color) !important;
        }

        .focus\\:ring-blue-500:focus,
        .focus\\:ring-blue-600:focus {
            --tw-ring-color: var(--theme-color) !important;
        }

        .focus\\:border-blue-500:focus,
        .focus\\:border-blue-600:focus {
            border-color: var(--theme-color) !important;
        }

        /* File input upload button */
        input[type="file"]::file-selector-button,
        input[type="file"]::-webkit-file-upload-button {
            background-color: var(--theme-color-light) !important;
            color: var(--theme-color-dark) !important;
        }

        input[type="file"]::file-selector-button:hover,
        input[type="file"]::-webkit-file-upload-button:hover {
            background-color: var(--theme-color) !important;
            color: white !important;
        }

        /* Active navigation items */
        [data-flux-navlist-item][aria-current="page"],
        .border-b-2.border-blue-600 {
            border-color: var(--theme-color) !important;
        }
    </style>';
    @endphp
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            @php
            $adminLogo = \App\Models\Setting::get('admin_logo');
            $siteName = \App\Models\Setting::get('site_name', config('app.name'));
            @endphp
            @if($adminLogo && Storage::disk('public')->exists($adminLogo))
            <img src="{{ Storage::url($adminLogo) }}" alt="{{ $siteName }}" class="h-8 object-contain">
            @else
            <x-app-logo />
            @endif
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Overview')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                <flux:navlist.item icon="chart-bar" :href="route('analytics.index')"
                    :current="request()->routeIs('analytics.*')">{{ __('Analytics') }}</flux:navlist.item>
                <flux:navlist.item icon="presentation-chart-line" :href="route('metrics.dashboard')"
                    :current="request()->routeIs('metrics.*')">{{ __('Metrics') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Management')" class="grid">
                @if(auth()->user()->role->canManageComponents())
                <flux:navlist.item icon="cube" :href="route('components.index')"
                    :current="request()->routeIs('components.*')" wire:navigate>{{ __('Components') }}
                </flux:navlist.item>
                @endif

                @if(auth()->user()->role->canManageIncidents())
                <flux:navlist.item icon="exclamation-triangle" :href="route('incidents.index')"
                    :current="request()->routeIs('incidents.*')" wire:navigate>{{ __('Incidents') }}</flux:navlist.item>
                @endif

                @if(auth()->user()->role->canManageComponents())
                <flux:navlist.item icon="users" :href="route('subscribers.index')"
                    :current="request()->routeIs('subscribers.*')" wire:navigate>{{ __('Subscribers') }}
                </flux:navlist.item>
                @endif

                @if(auth()->user()->role->canManageUsers())
                <flux:navlist.item icon="user-group" :href="route('users.index')"
                    :current="request()->routeIs('users.*')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                @endif
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Public Pages')" class="grid">
                <flux:navlist.item icon="globe-alt" :href="route('home')" target="_blank">{{ __('Status Page') }}
                </flux:navlist.item>
                <flux:navlist.item icon="clock" :href="route('history')" target="_blank">{{ __('Incident History') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        {{-- <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist> --}}

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
    @stack('scripts')
</body>

</html>