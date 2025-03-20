<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Billy') }} - @yield('title', 'Financial Management')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Bootstrap CSS (Added) -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        
        <!-- Custom CSS -->
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Section for additional styles -->
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-light d-flex flex-column">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="container py-4 flex-grow-1">
                <!-- Page title if available -->
                @hasSection('page-title')
                    <div class="mb-4">
                        <h2 class="text-2xl font-semibold">@yield('page-title')</h2>
                        
                        @hasSection('breadcrumbs')
                            @yield('breadcrumbs')
                        @endif
                    </div>
                @endif
                
                <!-- Main Content -->
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
            
            <!-- Footer -->
            <footer class="bg-white shadow mt-auto py-4">
                <div class="container">
                    <div class="text-center text-muted">
                        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Billy') }}. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Section for additional scripts -->
        @stack('scripts')
    </body>
</html>
