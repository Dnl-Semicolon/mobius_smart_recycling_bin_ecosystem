<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Mobius' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-nunito min-h-screen bg-gradient-to-br from-gray-50 to-gray-100" hx-boost="true">
    <div class="min-h-screen flex flex-col">
        {{-- Header --}}
        <header class="sticky top-0 z-10 bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="max-w-2xl mx-auto px-6 h-14 flex items-center justify-between">
                {{-- Left: Back button --}}
                <div class="w-20 flex justify-start">
                    @if (isset($back))
                        {{ $back }}
                    @else
                        <a href="/" class="text-lg font-bold text-gray-900 hover:text-gray-600 focus-visible:text-gray-600 -ml-2 px-2 py-1 rounded-lg focus-visible:ring-2 focus-visible:ring-black/10">
                            Mobius
                        </a>
                    @endif
                </div>

                {{-- Center: Title --}}
                <div class="flex-1 flex justify-center">
                    @if (isset($header))
                        <h1 class="text-sm font-semibold text-gray-900">{{ $header }}</h1>
                    @endif
                </div>

                {{-- Right: Actions --}}
                <div class="w-20 flex justify-end">
                    @if (isset($actions))
                        {{ $actions }}
                    @endif
                </div>
            </div>
            {{-- Subtle separator --}}
            <div class="max-w-2xl mx-auto px-6">
                <div class="h-px bg-gray-200/60"></div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1">
            <div class="max-w-2xl mx-auto px-6 py-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
