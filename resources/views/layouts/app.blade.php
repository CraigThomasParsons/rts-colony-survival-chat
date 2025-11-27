<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <!-- Materialize + App Scripts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" integrity="sha512-1m0RsmYf7v1jFZp09wDJF60p0ISjGH8GMWz3KBGM7rCNRLtLwBEmp6kAMPx/+4vOB0fOkH1hpV2Q0Qp8+4d0Bw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body.theme-dark {
                background: linear-gradient(180deg, rgba(6,11,25,0.95), rgba(3,3,12,0.9)), url('{{ asset('images/login-bg.png') }}') center/cover fixed;
                color: #f4f6ff;
            }
            body.theme-light {
                background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(219,233,255,0.85)), url('{{ asset('images/login-bg.png') }}') center/cover fixed;
                color: #1f2937;
            }
        </style>
    </head>
    <body class="font-sans antialiased theme-dark">
        <div class="min-h-screen flex flex-col" style="backdrop-filter: blur(2px);">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1">
                <div class="section">
                    <div class="container">
                        @hasSection('content')
                            @yield('content')
                        @elseif(isset($slot))
                            {{ $slot }}
                        @else
                            {{-- No content provided --}}
                        @endif
                    </div>
                </div>
            </main>
        </div>
        <script>
            (function () {
                const body = document.body;
                const stored = localStorage.getItem('ff-theme');
                const mode = stored === 'light' ? 'light' : 'dark';
                body.classList.remove('theme-dark', 'theme-light');
                body.classList.add(mode === 'light' ? 'theme-light' : 'theme-dark');
                body.dataset.mode = mode;
            })();
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js" integrity="sha512-Cj2aqk8VnKXuX4nHCqB6f+GO6zkRgZNpmjDoE7YQDdyCjTiMQuuLHfoalGoVYLRNvKcJste19h9Up7ZK9C1w4g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </body>
</html>
