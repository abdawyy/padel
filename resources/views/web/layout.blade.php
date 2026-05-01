<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fdf4ff',
                            100: '#fae8ff',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7e22ce',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

{{-- Nav --}}
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ route('home') }}" class="text-xl font-bold text-brand-600 tracking-tight">
            {{ config('app.name') }}
        </a>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('home') }}#plans" class="text-gray-500 hover:text-brand-600 transition">Pricing</a>
            <a href="{{ url('/admin/login') }}" class="text-gray-500 hover:text-brand-600 transition">Login</a>
            <a href="{{ route('register.academy') }}" class="bg-brand-600 text-white px-4 py-2 rounded-lg hover:bg-brand-700 transition font-medium">
                Register Academy
            </a>
        </div>
    </div>
</nav>

{{-- Flash --}}
@if(session('success'))
    <div class="max-w-6xl mx-auto mt-4 px-4">
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    </div>
@endif

@yield('content')

{{-- Footer --}}
<footer class="mt-24 bg-gray-900 text-gray-400 py-10">
    <div class="max-w-6xl mx-auto px-4 text-center text-sm">
        <p class="font-semibold text-white text-base mb-1">{{ config('app.name') }}</p>
        <p>The all-in-one sports academy management platform.</p>
        <p class="mt-4">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
