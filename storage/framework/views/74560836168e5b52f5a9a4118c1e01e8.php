<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'Status Page'); ?> - <?php echo e(config('app.name', 'Status Page')); ?></title>

    <?php
    $favicon = \App\Models\Setting::get('favicon');
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favicon && Storage::disk('public')->exists($favicon)): ?>
    <link rel="icon" href="<?php echo e(Storage::url($favicon)); ?>" sizes="any">
    <?php else: ?>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <?php
    $themeColor = \App\Models\Setting::get('theme_color', '#3B82F6');
    echo '<style>
        :root {
            --theme-color: ' . $themeColor . ';
            --theme-color-hover: color-mix(in srgb, ' . $themeColor . ' 90%, black);
            --theme-color-light: color-mix(in srgb, ' . $themeColor . ' 10%, white);
            --theme-color-dark: color-mix(in srgb, ' . $themeColor . ' 80%, black);
        }

        /* All buttons and submit buttons */
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
        button.bg-blue-500:hover,
        a.bg-blue-600:hover,
        a.bg-blue-500:hover {
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

        /* Active navigation border */
        .border-b-2.border-blue-600 {
            border-color: var(--theme-color) !important;
        }

        /* Links that look like buttons */
        a.inline-flex.items-center.px-4.py-2 {
            background-color: var(--theme-color) !important;
        }

        a.inline-flex.items-center.px-4.py-2:hover {
            background-color: var(--theme-color-hover) !important;
        }

        /* Mobile menu styles */
        @media (max-width: 768px) {
            .mobile-menu {
                display: none;
            }

            .mobile-menu.open {
                display: block;
            }
        }
    </style>';
    ?>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('open');
        }
    </script>
</head>

<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Public Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <?php
                        $publicLogo = \App\Models\Setting::get('public_logo');
                        $siteName = \App\Models\Setting::get('site_name', config('app.name'));
                        ?>
                        <a href="/" class="flex items-center gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($publicLogo && Storage::disk('public')->exists($publicLogo)): ?>
                            <img src="<?php echo e(Storage::url($publicLogo)); ?>" alt="<?php echo e($siteName); ?>" class="h-8 object-contain">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <nav class="hidden md:flex space-x-8">
                        <a href="/"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium <?php echo e(request()->is('/') ? 'text-gray-900 border-b-2 border-blue-600' : ''); ?>">
                            Status
                        </a>
                        <a href="/history"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium <?php echo e(request()->is('history') ? 'text-gray-900 border-b-2 border-blue-600' : ''); ?>">
                            History
                        </a>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="/dashboard"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium border-1 rounded-md">
                            Dashboard
                        </a>
                        <?php else: ?>
                        <a href="/login"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium border-1 rounded-md">
                            Admin Login
                        </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </nav>

                    <!-- Mobile menu button -->
                    <button onclick="toggleMobileMenu()"
                        class="md:hidden p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Navigation -->
                <div id="mobile-menu" class="mobile-menu md:hidden pb-4">
                    <div class="flex flex-col space-y-2 pt-2">
                        <a href="/"
                            class="text-gray-700 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 text-base font-medium rounded-md <?php echo e(request()->is('/') ? 'bg-gray-100 text-gray-900' : ''); ?>">
                            Status
                        </a>
                        <a href="/history"
                            class="text-gray-700 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 text-base font-medium rounded-md <?php echo e(request()->is('history') ? 'bg-gray-100 text-gray-900' : ''); ?>">
                            History
                        </a>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="/dashboard"
                            class="text-gray-700 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 text-base font-medium rounded-md">
                            Dashboard
                        </a>
                        <?php else: ?>
                        <a href="/login"
                            class="text-gray-700 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 text-base font-medium rounded-md">
                            Admin Login
                        </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <?php echo e($slot); ?>

            </div>
        </main>

        <!-- Public Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <?php
                        $siteName = \App\Models\Setting::get('site_name', config('app.name'));
                        ?>
                        © <?php echo e(date('Y')); ?> <?php echo e($siteName); ?>. All rights reserved.
                    </div>
                    <div class="flex space-x-6">
                        <a href="/" class="text-sm text-gray-500 hover:text-gray-900">Status</a>
                        <a href="/history" class="text-sm text-gray-500 hover:text-gray-900">History</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>

</html><?php /**PATH C:\laragon\www\carecloudStatus\resources\views/components/layouts/public.blade.php ENDPATH**/ ?>