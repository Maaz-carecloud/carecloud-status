<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    @if($success)
                    ✅ Email Verified!
                    @else
                    ❌ Verification Failed
                    @endif
                </h2>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                @if($success)
                <div class="text-center">
                    <p class="text-gray-700 mb-4">
                        Thank you for verifying your email address!
                    </p>
                    <p class="text-gray-600 mb-6">
                        You will now receive status updates for:
                    </p>
                    <div class="bg-gray-50 rounded p-4 mb-6">
                        <ul class="text-left space-y-2">
                            @foreach($subscriber->components as $component)
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                {{ $component->name }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('home') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        View Status Page
                    </a>
                </div>
                @else
                <div class="text-center">
                    <p class="text-gray-700 mb-4">
                        {{ $message ?? 'The verification link is invalid or has expired.' }}
                    </p>
                    <p class="text-gray-600 mb-6">
                        This could happen if:
                    </p>
                    <ul class="text-left text-gray-600 mb-6 space-y-2">
                        <li>• The link has already been used</li>
                        <li>• The link has expired (24 hours)</li>
                        <li>• The email address has already been verified</li>
                    </ul>
                    <div class="space-x-4">
                        <a href="{{ route('subscribe') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Subscribe Again
                        </a>
                        <a href="{{ route('home') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            View Status Page
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <div class="text-center text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-gray-700">← Back to Status Page</a>
            </div>
        </div>
    </div>
</body>

</html>